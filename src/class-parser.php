<?php namespace WP_Parser;

use WP_CLI;
use WP_Post;

/**
 * Main plugin's class. Registers things and adds WP CLI command.
 */
class Parser {

	/**
	 * @var Relationships
	 */
	public $relationships;

	/**
	 * @var array
	 */
	private $taxonomy_object_types = [
		'wp-parser-class',
		'wp-parser-method',
		'wp-parser-function',
		'wp-parser-hook'
	];

	/**
	 * @var array
	 */
	private $post_type_support = [
		'comments',
		'custom-fields',
		'editor',
		'excerpt',
		'revisions',
		'title',
	];

	/**
	 * Parser constructor.
	 */
	public function __construct() {
		$this->relationships = new Relationships();
	}

	/**
	 * Sets up the parser code.
	 *
	 * @return void
	 */
	public function on_load() {

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'parser', __NAMESPACE__ . '\\Command' );
		}

		add_action( 'init', [ $this, 'register_post_types' ], 11 );
		add_action( 'init', [ $this, 'register_taxonomies' ], 11 );

		add_filter( 'wp_parser_get_arguments', [ $this, 'make_args_safe' ] );
		add_filter( 'wp_parser_return_type', [ $this, 'humanize_separator' ] );
	}

	/**
	 * Register the function and class post types.
	 *
	 * @return void
	 */
	public function register_post_types() {

		if ( ! post_type_exists( 'wp-parser-function' ) ) {
			register_post_type(
				'wp-parser-function',
				[
					'has_archive' => 'functions',
					'label'       => __( 'Functions', 'wp-parser' ),
					'public'      => true,
					'rewrite'     => [
						'feeds'      => false,
						'slug'       => 'function',
						'with_front' => false,
					],
					'supports'    => $this->post_type_support,
				]
			);
		}

		if ( ! post_type_exists( 'wp-parser-method' ) ) {
			register_post_type(
				'wp-parser-method',
				[
					'has_archive' => false,
					'label'       => __( 'Methods', 'wp-parser' ),
					'public'      => true,
					'rewrite'     => [
						'feeds'      => false,
						'slug'       => 'method',
						'with_front' => false,
					],
					'supports' => $this->post_type_support,
				]
			);
		}


		if ( ! post_type_exists( 'wp-parser-class' ) ) {
			register_post_type(
				'wp-parser-class',
				[
					'has_archive' => 'classes',
					'label'       => __( 'Classes', 'wp-parser' ),
					'public'      => true,
					'rewrite'     => [
						'feeds'      => false,
						'slug'       => 'class',
						'with_front' => false,
					],
					'supports'    => $this->post_type_support,
				]
			);
		}

		if ( ! post_type_exists( 'wp-parser-hook' ) ) {
			register_post_type(
				'wp-parser-hook',
				[
					'has_archive' => 'hooks',
					'label'       => __( 'Hooks', 'wp-parser' ),
					'public'      => true,
					'rewrite'     => [
						'feeds'      => false,
						'slug'       => 'hook',
						'with_front' => false,
					],
					'supports'    => $this->post_type_support,
				]
			);
		}
	}

	/**
	 * Register the file and @since taxonomies.
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		if ( ! taxonomy_exists( 'wp-parser-source-file' ) ) {
			register_taxonomy(
				'wp-parser-source-file',
				$this->taxonomy_object_types,
				[
					'label'                 => __( 'Files', 'wp-parser' ),
					'public'                => true,
					'rewrite'               => [ 'slug' => 'files' ],
					'sort'                  => false,
					'update_count_callback' => '_update_post_term_count',
				]
			);
		}

		if ( ! taxonomy_exists( 'wp-parser-package' ) ) {
			register_taxonomy(
				'wp-parser-package',
				$this->taxonomy_object_types,
				[
					'hierarchical'          => true,
					'label'                 => '@package',
					'public'                => true,
					'rewrite'               => [ 'slug' => 'package' ],
					'sort'                  => false,
					'update_count_callback' => '_update_post_term_count',
				]
			);
		}

		if ( taxonomy_exists( 'wp-parser-since' ) ) {
			foreach ( $this->taxonomy_object_types as $taxonomy_object_type ) {
				unregister_taxonomy_for_object_type( 'wp-parser-since', $taxonomy_object_type );
			}

			unregister_taxonomy( 'wp-parser-since' );
		}

		if ( ! taxonomy_exists( 'wp-parser-namespace' ) ) {
			register_taxonomy(
				'wp-parser-namespace',
				$this->taxonomy_object_types,
				[
					'hierarchical'          => true,
					'label'                 => __( 'Namespaces', 'wp-parser' ),
					'public'                => true,
					'rewrite'               => [ 'slug' => 'namespace' ],
					'sort'                  => false,
					'update_count_callback' => '_update_post_term_count',
				]
			);
		}

		if ( ! taxonomy_exists( 'wp-parser-plugin' ) ) {
			register_taxonomy(
				'wp-parser-plugin',
				$this->taxonomy_object_types,
				[
					'hierarchical'          => true,
					'label'                 => __( 'Plugins', 'wp-parser' ),
					'public'                => true,
					'rewrite'               => [ 'slug' => 'plugin' ],
					'sort'                  => false,
					'update_count_callback' => '_update_post_term_count',
				]
			);
		}
	}

	/**
	 * Sanitizes raw phpDoc that could potentially introduce unsafe markup into the HTML.
	 *
	 * @param array $args Parameter arguments to make safe.
	 *
	 * @return array The sanitized parameter arguments.
	 */
	public function make_args_safe( $args ) {
		array_walk_recursive( $args, [ $this, 'sanitize_argument' ] );

		return $args;
	}

	/**
	 * Sanitizes the passed argument.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return mixed The sanitized argument.
	 */
	public function sanitize_argument( &$value ) {

		static $filters = [
			'wp_filter_kses',
			'make_clickable',
			'force_balance_tags',
			'wptexturize',
			'convert_smilies',
			'convert_chars',
			'stripslashes_deep',
		];

		foreach ( $filters as $filter ) {
			$value = call_user_func( $filter, $value );
		}

		return $value;
	}

	/**
	 * Replaces separators with a more readable version.
	 *
	 * @param string $type The variable type.
	 *
	 * @return string The humanized separator.
	 */
	public function humanize_separator( $type ) {
		return str_replace( '|', '<span class="wp-parser-item-type-or">' . _x( ' or ', 'separator', 'wp-parser' ) . '</span>', $type );
	}
}
