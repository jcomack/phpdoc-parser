<?php

namespace WP_Parser;

use Psr\Log\LoggerInterface;
use WP_Error;
use WP_Parser\Importers\ImportLogger;
use wpdb;

/**
 * Handles creating and updating posts from (functions|classes|files) generated by phpDoc.
 */
class Importer {

	/**
	 * Taxonomy name for files
	 *
	 * @var string
	 */
	public $taxonomy_file;

	/**
	 * Taxonomy name for an item's namespace tags
	 *
	 * @var string
	 */
	public $taxonomy_namespace;

	/**
	 * Taxonomy name for an item's @since tag
	 *
	 * @var string
	 */
	public $taxonomy_since_version;

	/**
	 * Taxonomy name for an item's @package/@subpackage tags
	 *
	 * @var string
	 */
	public $taxonomy_package;

	/**
	 * Taxonomy name for an item's plugin tags
	 *
	 * @var string
	 */
	public $taxonomy_plugin;

	/**
	 * Post type name for functions
	 *
	 * @var string
	 */
	public $post_type_function;

	/**
	 * Post type name for classes
	 *
	 * @var string
	 */
	public $post_type_class;

	/**
	 * Post type name for methods
	 *
	 * @var string
	 */
	public $post_type_method;

	/**
	 * Post type name for hooks
	 *
	 * @var string
	 */
	public $post_type_hook;

	/**
	 * Handy store for meta about the current item being imported
	 *
	 * @var array
	 */
	public $file_meta = [];

	/**
	 * @var array Cached items of inserted terms
	 */
	protected $inserted_terms = [];

	/**
	 * @var string
	 */
	protected $plugin_name = '';

	/**
	 * @var string
	 */
	protected $plugin_dir = '';

	/**
	 * @var ImportLogger
	 */
	private $logger;
	/**
	 * @var array|WP_Error
	 */
	private $plugin_term;

	/**
	 * Constructor. Sets up post type/taxonomy names.
	 *
	 * @param array $args Optional. Associative array; class property => value.
	 */
	public function __construct( array $args = [] ) {

		$properties = wp_parse_args(
			$args,
			[
				'post_type_class'        => 'wp-parser-class',
				'post_type_method'       => 'wp-parser-method',
				'post_type_function'     => 'wp-parser-function',
				'post_type_hook'         => 'wp-parser-hook',
				'taxonomy_file'          => 'wp-parser-source-file',
				'taxonomy_namespace'     => 'wp-parser-namespace',
				'taxonomy_package'       => 'wp-parser-package',
				'taxonomy_since_version' => 'wp-parser-since',
				'taxonomy_plugin' 		 => 'wp-parser-plugin',
			]
		);

		foreach ( $properties as $property_name => $value ) {
			$this->{$property_name} = $value;
		}

		$this->logger = new ImportLogger();
	}

	/**
	 * Sets the logger.
	 *
	 * @param LoggerInterface $logger The logger to set.
	 *
	 * @return void.
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger->setLogger( $logger );
	}

	/**
	 * Import the PHPDoc $data into WordPress posts and taxonomies
	 *
	 * @param array $files					  The files to import.
	 * @param bool  $skip_sleep               Optional; defaults to false. If true, the sleep() calls are skipped.
	 * @param bool  $import_ignored_functions Optional; defaults to false. If true, functions marked `@ignore` will be imported.
	 *
	 * @return void
	 */
	public function import( array $files, $skip_sleep = false, $import_ignored_functions = false ) {
		global $wpdb;

		$time_start = microtime(true);
		$num_queries = $wpdb->num_queries;

		$this->logger->info( 'Starting import. This will take some time…' );

		$file_number  = 1;
		$num_of_files = count( $files );

		do_action( 'wp_parser_starting_import' );

		// Defer term counting for performance
		wp_suspend_cache_invalidation( true );
		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );

		// Remove actions for performance
		remove_action( 'transition_post_status', '_update_blog_date_on_post_publish', 10 );
		remove_action( 'transition_post_status', '__clear_multi_author_cache', 10 );

		delete_option( 'wp_parser_imported_wp_version' );
		delete_option( 'wp_parser_root_import_dir' );

		// Sanity check -- do the required post types exist?
		if ( ! post_type_exists( $this->post_type_class ) || ! post_type_exists( $this->post_type_function ) || ! post_type_exists( $this->post_type_hook ) ) {
			$this->logger->error( sprintf( 'Missing post type; check that "%1$s", "%2$s", and "%3$s" are registered.', $this->post_type_class, $this->post_type_function, $this->post_type_hook ) );
			exit;
		}

		// Sanity check -- do the required taxonomies exist?
		if ( ! taxonomy_exists( $this->taxonomy_file ) || ! taxonomy_exists( $this->taxonomy_since_version ) || ! taxonomy_exists( $this->taxonomy_package ) ) {
			$this->logger->error( sprintf( 'Missing taxonomy; check that "%1$s" is registered.', $this->taxonomy_file ) );
			exit;
		}

		$root = '';

		foreach ( $files as $file ) {
			$this->logger->info(
				sprintf(
					'Processing file %1$s of %2$s "%3$s".',
					number_format_i18n( $file_number ),
					number_format_i18n( $num_of_files ),
					$file['path']
				)
			);

			$file_number++;

			$this->import_file( $file, $skip_sleep, $import_ignored_functions );

			if ( empty( $root ) && ( isset( $file['root'] ) && $file['root'] ) ) {
				$root = $file['root'];
			}
		}

		if ( ! empty( $root ) ) {
			update_option( 'wp_parser_root_import_dir', $root );
			$this->logger->info( 'Updated option wp_parser_root_import_dir: ' . $root );
		}

		$this->log_last_import();

		$wp_version = get_option( 'wp_parser_imported_wp_version' );
		if ( $wp_version ) {
			$this->logger->info( 'Updated option wp_parser_imported_wp_version: ' . $wp_version );
		}

		/**
		 * Workaround for a WP core bug where hierarchical taxonomy caches are not being cleared
		 *
		 * https://core.trac.wordpress.org/ticket/14485
		 * http://wordpress.stackexchange.com/questions/8357/inserting-terms-in-an-hierarchical-taxonomy
		 */
		delete_option( "{$this->taxonomy_package}_children" );
		delete_option( "{$this->taxonomy_since_version}_children" );

		/**
		 * Action at the end of a complete import
		 */
		do_action( 'wp_parser_ending_import' );

		// Start counting again
		wp_defer_term_counting( false );
		wp_suspend_cache_invalidation( false );
		wp_cache_flush();
		wp_defer_comment_counting( false );

		$time_end = microtime( true );
		$time = $time_end - $time_start;

		$this->logger->info( 'Time: ' . $time );
		$this->logger->info( 'Queries: ' . ( $wpdb->num_queries - $num_queries ) );

		if ( ! $this->logger->has_stashed_errors() ) {
			$this->logger->info( 'Import complete!' );
		} else {
			$this->logger->info( 'Import complete, but some errors were found:' );
			$this->logger->output_stashed_errors();
		}
	}

	/**
	 * Logs the last import.
	 *
	 * @return void
	 */
	protected function log_last_import() {
		$last_import = time();
		$import_date = date_i18n( get_option('date_format'), $last_import );
		$import_time = date_i18n( get_option('time_format'), $last_import );

		update_option( 'wp_parser_last_import', $last_import );

		$this->logger->info(
			sprintf( 'Updated option wp_parser_last_import: %1$s at %2$s.', $import_date, $import_time )
		);
	}

	/**
	 * Inserts the term for the passed taxonomy.
	 *
	 * @param int|string $term		The term to insert.
	 * @param string     $taxonomy	The taxonomy to insert the term for.
	 * @param array      $args		Additional arguments.
	 *
	 * @return array|WP_Error Returns the inserted term or error if the insertion fails.
	 */
	protected function insert_term( $term, $taxonomy, $args = [] ) {
		$parent = isset( $args['parent'] ) ? $args['parent'] : 0;

		if ( isset( $this->inserted_terms[ $taxonomy ][ $term . $parent ] ) ) {
			return $this->inserted_terms[ $taxonomy ][ $term . $parent ];
		}

		if ( ! $inserted_term = term_exists( $term, $taxonomy, $parent ) ) {
			$inserted_term = wp_insert_term( $term, $taxonomy, $args );
		}

		if ( ! is_wp_error( $inserted_term ) ) {
			$this->inserted_terms[ $taxonomy ][ $term . $parent ] = $inserted_term;
		}

		return $inserted_term;
	}

	/**
	 * For a specific file, go through and import the file, functions, and classes.
	 *
	 * @param array $file
	 * @param bool  $skip_sleep     Optional; defaults to false. If true, the sleep() calls are skipped.
	 * @param bool  $import_ignored Optional; defaults to false. If true, functions and classes marked `@ignore` will be imported.
	 *
	 * @return void
	 */
	public function import_file( array $file, $skip_sleep = false, $import_ignored = false ) {
		$this->plugin_term = $this->insert_term( $file['plugin'], $this->taxonomy_plugin );
		add_term_meta( $this->plugin_term['term_id'], '_wp-parser-plugin-directory', plugin_basename( $file['root'] ), true );

		if ( ! isset( $this->plugin_name ) || $this->plugin_name !== $file['plugin'] ) {
			$this->plugin_name = $file['plugin'];
		}

		// Maybe add this file to the file taxonomy
		$slug = sanitize_title( str_replace( '/', '_', $file['path'] ) );

		$term = $this->insert_term( $file['path'], $this->taxonomy_file, [ 'slug' => $slug ] );

		if ( is_wp_error( $term ) ) {
			$this->logger->stash_error(
				sprintf(
					'Problem creating file tax item "%1$s" for %2$s: %3$s',
					$slug,
					$file['path'],
					$term->get_error_message()
				)
			);

			return;
		}

		// Detect deprecated file
		$deprecated_file = false;
		if ( isset( $file['uses']['functions'] ) ) {
			$first_function = $file['uses']['functions'][0];

			// If the first function in this file is _deprecated_function
			if ( $first_function['name'] === '_deprecated_file' ) {

				// Set the deprecated flag to the version number
				$deprecated_file = $first_function['deprecation_version'];
			}
		}

		// Store file meta for later use
		$this->file_meta = [
			'docblock'   => $file['file'], // File docblock
			'term_id'    => $file['path'], // Term name in the file taxonomy is the file name
			'deprecated' => $deprecated_file, // Deprecation status
		];

		// TODO ensures values are set, but better handled upstream later
		$file = array_merge( [
			'functions' => [],
			'classes'   => [],
			'hooks'     => [],
		], $file );

		$count = 0;

		foreach ( $file['functions'] as $function ) {
			$this->import_function( $function, 0, $import_ignored );
			$count++;

			// TODO figure our why are we still doing this
			if ( ! $skip_sleep && $count % 10 === 0 ) {
				sleep( 3 );
			}
		}

		foreach ( $file['classes'] as $class ) {
			$this->import_class( $class, $import_ignored );
			$count++;

			if ( ! $skip_sleep && $count % 10 === 0 ) {
				sleep( 3 );
			}
		}

		foreach ( $file['hooks'] as $hook ) {
			$this->import_hook( $hook, 0, $import_ignored );
			$count++;

			if ( ! $skip_sleep && $count % 10 === 0 ) {
				sleep( 3 );
			}
		}

		if ( $file['path'] === 'wp-includes/version.php' ) {
			$this->import_version( $file );
		}
	}

	/**
	 * Create a post for a function.
	 *
	 * @param array $data           The function data.
	 * @param int   $parent_post_id Optional; post ID of the parent (class or function) this item belongs to. Defaults to zero (no parent).
	 * @param bool  $import_ignored Optional; defaults to false. If true, functions marked `@ignore` will be imported.
	 *
	 * @return void Post ID of this function, false if any failure.
	 */
	public function import_function( array $data, $parent_post_id = 0, $import_ignored = false ) {
		$function_id = $this->import_item( $data, $parent_post_id, $import_ignored );

		foreach ( $data['hooks'] as $hook ) {
			$this->import_hook( $hook, $function_id, $import_ignored );
		}
	}

	/**
	 * Create a post for a hook
	 *
	 * @param array $data           The hook data.
	 * @param int   $parent_post_id Optional; post ID of the parent (function) this item belongs to. Defaults to zero (no parent).
	 * @param bool  $import_ignored Optional; defaults to false. If true, hooks marked `@ignore` will be imported.
	 *
	 * @return bool|int Post ID of this hook, false if any failure.
	 */
	public function import_hook( array $data, $parent_post_id = 0, $import_ignored = false ) {
		$hook_id = $this->import_item( $data, $parent_post_id, $import_ignored, [ 'post_type' => $this->post_type_hook ] );

		if ( ! $hook_id ) {
			return false;
		}

		update_post_meta( $hook_id, '_wp-parser_hook_type', $data['type'] );

		return $hook_id;
	}

	/**
	 * Create a post for a class
	 *
	 * @param array $data           The class data.
	 * @param bool  $import_ignored Optional; defaults to false. If true, functions marked `@ignore` will be imported.
	 *
	 * @return bool|int Post ID of this function, false if any failure.
	 */
	protected function import_class( array $data, $import_ignored = false ) {

		// Insert this class
		$class_id = $this->import_item( $data, 0, $import_ignored, [ 'post_type' => $this->post_type_class ] );

		if ( ! $class_id ) {
			return false;
		}

		// Set class-specific meta
		update_post_meta( $class_id, '_wp-parser_final', (string) $data['final'] );
		update_post_meta( $class_id, '_wp-parser_abstract', (string) $data['abstract'] );
		update_post_meta( $class_id, '_wp-parser_extends', $data['extends'] );
		update_post_meta( $class_id, '_wp-parser_implements', $data['implements'] );
		update_post_meta( $class_id, '_wp-parser_properties', $data['properties'] );

		// Now add the methods
		foreach ( $data['methods'] as $method ) {
			// Namespace method names with the class name
			$method['name'] = $data['name'] . '::' . $method['name'];
			$this->import_method( $method, $class_id, $import_ignored );
		}

		return $class_id;
	}

	/**
	 * Create a post for a class method.
	 *
	 * @param array $data           The method data.
	 * @param int   $parent_post_id Post ID of the parent (class) this method belongs to.
	 * @param bool  $import_ignored Defaults to false. If true, functions marked `@ignore` will be imported.
	 *
	 * @return bool|int Post ID of this function, false if any failure.
	 */
	protected function import_method( array $data, $parent_post_id = 0, $import_ignored = false ) {

		// Insert this method.
		$method_id = $this->import_item( $data, $parent_post_id, $import_ignored, [ 'post_type' => $this->post_type_method ] );

		if ( ! $method_id ) {
			return false;
		}

		// Set method-specific meta.
		update_post_meta( $method_id, '_wp-parser_final', (string) $data['final'] );
		update_post_meta( $method_id, '_wp-parser_abstract', (string) $data['abstract'] );
		update_post_meta( $method_id, '_wp-parser_static', (string) $data['static'] );
		update_post_meta( $method_id, '_wp-parser_visibility', $data['visibility'] );

		// Now add the hooks.
		if ( ! empty( $data['hooks'] ) ) {
			foreach ( $data['hooks'] as $hook ) {
				$this->import_hook( $hook, $method_id, $import_ignored );
			}
		}

		return $method_id;
	}

	/**
	 * Updates the 'wp_parser_imported_wp_version' option with the version from wp-includes/version.php.
	 *
	 * @param array   $data The data to extract the version from.
	 *
	 * @return void
	 */
	protected function import_version( $data ) {

		$version_path = $data['root'] . '/' . $data['path'];

		if ( ! is_readable( $version_path ) ) {
			return;
		}

		include $version_path;

		if ( isset( $wp_version ) && $wp_version ) {
			update_option( 'wp_parser_imported_wp_version', $wp_version );
			$this->logger->info( "\t" . sprintf( 'Updated option wp_parser_imported_wp_version to "%1$s"', $wp_version ) );
		}
	}

	/**
	 * Gets the namespace.
	 *
	 * @param array $data The dataset to get the namespace from.
	 *
	 * @return string The namespace.
	 */
	private function get_namespace( $data ) {
		if ( empty( $data['namespace'] ) || $data['namespace'] === 'global' ) {
			return $data['name'];
		}

		return $data['namespace'] . '\\' . $data['name'];
	}

	/**
	 * Gets the slug from the namespace.
	 *
	 * @param string $namespace The namespace to get the slug from.
	 *
	 * @return string The slug.
	 */
	private function get_slug_from_namespace( $namespace ) {
		return sanitize_title( str_replace( '\\', '-', str_replace( '::', '-', $namespace ) ) );
	}

	/**
	 * Gets the ID of the post that exists with the passed slug and post type.
	 * Additionally can be limited by the parent ID.
	 *
	 * @param string $slug		The slug of the post to search for.
	 * @param string $post_type The post type to search for.
	 * @param int 	 $parent_id The parent id.
	 *
	 * @return int The post ID.
	 */
	protected function get_existing_item( $slug, $post_type, $parent_id = 0 ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		if ( $post_type === 'wp-parser-hook' ) {
			return $wpdb->get_var(
				$q = $wpdb->prepare(
					"SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s LIMIT 1",
					$slug,
					$post_type
				)
			);
		}

		return $wpdb->get_var(
			$q = $wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND post_parent = %d LIMIT 1",
				$slug,
				$post_type,
				(int) $parent_id
			)
		);
	}

	/**
	 * Create a post for an item (a class or a function).
	 *
	 * Anything that needs to be dealt identically for functions or methods should go in this function.
	 * Anything more specific should go in either import_function() or import_class() as appropriate.
	 *
	 * @param array $data           Data.
	 * @param int   $parent_post_id Optional; post ID of the parent (class or function) this item belongs to. Defaults to zero (no parent).
	 * @param bool  $import_ignored Optional; defaults to false. If true, functions or classes marked `@ignore` will be imported.
	 * @param array $arg_overrides  Optional; array of parameters that override the defaults passed to wp_update_post().
	 *
	 * @return bool|int Post ID of this item, false if any failure.
	 */
	public function import_item( array $data, $parent_post_id = 0, $import_ignored = false, array $arg_overrides = [] ) {
		$is_new_post 		= true;
		$post_needed_update = false;
		$namespace     		= $this->get_namespace( $data );
		$slug        		= $this->get_slug_from_namespace( $namespace );

		$post_data = wp_parse_args(
			$arg_overrides,
			[
				'post_content' => $data['doc']['long_description'],
				'post_excerpt' => $data['doc']['description'],
				'post_name'    => $slug,
				'post_parent'  => (int) $parent_post_id,
				'post_status'  => 'publish',
				'post_title'   => $data['name'],
				'post_type'    => $this->post_type_function,
			]
		);

		// The tags associated with the item.
		$tags = $data['doc']['tags'];

		// Don't import items marked `@ignore` unless explicitly requested. See https://github.com/WordPress/phpdoc-parser/issues/16
		if ( ! $import_ignored && wp_list_filter( $tags, [ 'name' => 'ignore' ] ) ) {

			switch ( $post_data['post_type'] ) {
				case $this->post_type_class:
					$this->logger->info(
						sprintf( 'Skipped importing @ignore-d class "%1$s"', $namespace ),
						1
					);
					break;

				case $this->post_type_method:
					$this->logger->info(
						sprintf( 'Skipped importing @ignore-d method "%1$s"', $namespace ),
						2
					);
					break;

				case $this->post_type_hook:
					$this->logger->info(
						sprintf( 'Skipped importing @ignore-d hook "%1$s"', $namespace ),
						( $parent_post_id ) ? 2 : 1
					);
					break;

				default:
					$this->logger->info(
						sprintf( 'Skipped importing @ignore-d function "%1$s"', $namespace ),
						1
					);
			}

			return false;
		}

		if ( wp_list_filter( $tags, [ 'name' => 'ignore' ] ) ) {
			return false;
		}

		// Look for an existing post for this item
		$existing_post_id = $this->get_existing_item( $slug, $post_data['post_type'], $parent_post_id );

		// Insert/update the item post
		if ( ! empty( $existing_post_id ) ) {
			$is_new_post = false;
			$post_id 	 = $post_data['ID'] = (int) $existing_post_id;

			// Determine whether we're dealing with a conflict between Free and Premium
			$potential_duplicates = $this->check_for_potential_duplicates( $post_data );

			// If code is duplicate (i.e. already existing), but the plugin name is different, create the plugin taxonomy and assign it. Then skip out.
			if ( $potential_duplicates ) {
				$this->logger->info( 'Skipping ' . $data['name'] . ' as it\'s duplicate' );
				$this->assign_additional_plugin( $post_id, $this->plugin_name );
				$this->_set_namespaces( $post_id, $data );

				return false;
			}

			$post_needed_update = $this->post_needs_update( $post_data, $existing_post_id );

			if ( $post_needed_update ) {
				$post_id = $this->update_post( $post_data );
			}
		} else {
			$post_id = $this->insert_post( $post_data );

			// Record the plugin if there is one.
			$this->assign_additional_plugin( $post_id, $this->plugin_name );
			$this->_set_namespaces( $post_id, $data );
		}

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			switch ( $post_data['post_type'] ) {
				case $this->post_type_class:
					$this->log_error_message( 'class', $namespace, $post_id );
					break;

				case $this->post_type_method:
					$this->log_error_message( 'method', $namespace, $post_id, 2 );
					break;

				case $this->post_type_hook:
					$this->log_error_message( 'hook', $namespace, $post_id, ( $parent_post_id ) ? 2 : 1 );
					break;

				default:
					$this->log_error_message( 'function', $namespace, $post_id );
			}

			return false;
		}

		$anything_updated = [];

		// If the item has @since markup, assign the taxonomy
		$anything_updated = array_merge(
			$anything_updated,
			$this->set_since_versions( $post_id, $tags )
		);

		$anything_updated = array_merge(
			$anything_updated,
			$this->set_packages( $post_id, $tags )
		);

		// Set other taxonomy and post meta to use in the theme templates
		$added_item = did_action( 'added_term_relationship' );
		wp_set_object_terms( $post_id, $this->file_meta['term_id'], $this->taxonomy_file );

		if ( did_action( 'added_term_relationship' ) > $added_item ) {
			$anything_updated[] = true;
		}

		// If the file is deprecated do something
		if ( ! empty( $this->file_meta['deprecated'] ) ) {
			$tags['deprecated'] = $this->file_meta['deprecated'];
		}

		if ( $post_data['post_type'] !== $this->post_type_class ) {
			$anything_updated[] = update_post_meta( $post_id, '_wp-parser_args', $data['arguments'] );
		}

		// If the post type is using namespace aliases, record them.
		if ( ! empty( $data['aliases'] ) ) {
			$anything_updated[] = update_post_meta( $post_id, '_wp_parser_aliases', (array) $data['aliases'] );
		}

		// Record the namespace if there is one.
		if ( ! empty( $data['namespace'] ) ) {
			$anything_updated[] = update_post_meta( $post_id, '_wp_parser_namespace', (string) addslashes( $data['namespace'] ) );
		}

		$anything_updated[] = update_post_meta( $post_id, '_wp-parser_line_num', (string) $data['line'] );
		$anything_updated[] = update_post_meta( $post_id, '_wp-parser_end_line_num', (string) $data['end_line'] );
		$anything_updated[] = update_post_meta( $post_id, '_wp-parser_tags', $tags );

		// If the post didn't need to be updated, but meta or tax changed, update it to bump last modified.
		if ( ! $is_new_post && ! $post_needed_update && array_filter( $anything_updated ) ) {
			wp_update_post( wp_slash( $post_data ), true );
		}

		$action = $is_new_post ? 'Imported' : 'Updated';

		switch ( $post_data['post_type'] ) {
			case $this->post_type_class:
				$this->logger->info( sprintf( '%1$s class "%2$s"', $action, $namespace ), 1 );
				break;

			case $this->post_type_hook:
				$this->logger->info( sprintf( '%1$s hook "%2$s"', $action, $namespace ), ( $parent_post_id ) ? 2 : 1 );
				break;

			case $this->post_type_method:
				$this->logger->info( sprintf( '%1$s method "%2$s"', $action, $namespace ), 2 );
				break;

			default:
				$this->logger->info( sprintf( '%1$s function "%2$s"', $action, $namespace ), 1 );
		}

		/**
		 * Action at the end of importing an item.
		 *
		 * @param int   $post_id   Optional; post ID of the inserted or updated item.
		 * @param array $data PHPDoc data for the item we just imported
		 * @param array $post_data WordPress data of the post we just inserted or updated
		 */
		do_action( 'wp_parser_import_item', $post_id, $data, $post_data );

		return $post_id;
	}

	/**
	 * Inserts a post with the passed post data.
	 *
	 * @param array $post_data The post data to add.
	 *
	 * @return int The newly inserted post ID.
	 */
	private function insert_post( $post_data ) {
		return wp_insert_post( wp_slash( $post_data ), true );
	}

	/**
	 * Updates a post based on the passed post data.
	 *
	 * @param array $post_data The post data to update.
	 *
	 * @return int The post ID of the updated post.
	 */
	private function update_post( $post_data ) {
		return wp_update_post( wp_slash( $post_data ), true );
	}

	/**
	 * Process the Namespace of items and add them to the correct taxonomy terms.
	 *
	 * This creates terms for each of the namespace terms in a hierachical tree
	 * and then adds the item being processed to each of the terms in that tree.
	 *
	 * @param int   $post_id The ID of the post item being processed.
	 * @param array $data	 The data.
	 *
	 * @return void
	 */
	protected function _set_namespaces( $post_id, $data ) {
		$ns_term  = false;
		$ns_terms = [];

		$namespaces = ( ! empty( $data['namespace'] ) ) ? explode( '\\', $data['namespace'] ) : [];

		if ( count( $namespaces ) === 0 ) {
			return;
		}

		foreach ( $namespaces as $namespace ) {
			$ns_term = $this->insert_term( $namespace, $this->taxonomy_namespace, [
					'slug'   => strtolower( str_replace( '_', '-', $namespace ) ),
					'parent' => ( $ns_term ) ? $ns_term['term_id'] : 0,
				]
			);

			if ( is_wp_error( $ns_term ) ) {
				$this->logger->warning( "Cannot set namespace term: " . $ns_term->get_error_message(), 1 );
				$ns_term = false;

				continue;
			}

			$ns_terms[] = (int) $ns_term['term_id'];
		}

		if ( ! empty( $ns_terms ) ) {
			$added_term_relationship = did_action( 'added_term_relationship' );
			wp_set_object_terms( $post_id, $ns_terms, $this->taxonomy_namespace );

			if ( did_action( 'added_term_relationship' ) > $added_term_relationship ) {
				$this->anything_updated[] = true;
			}
		}
	}

	/**
	 * Gets the plugin terms associated with the post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array The plugin terms.
	 */
	protected function get_plugin_terms_for_post( $post_id ) {
		return array_map(
			function( $term ) {
				return $term->name;
			},
			wp_get_post_terms( $post_id, $this->taxonomy_plugin )
		);
	}

	/**
	 * Checks if there's a potential duplicate entry, based on the post ID.
	 *
	 * @param array $post_data The post data to check.
	 *
	 * @return bool Whether or not there are potential duplicates.
	 */
	protected function check_for_potential_duplicates( $post_data ) {
		$associated_terms = $this->get_plugin_terms_for_post( $post_data['ID'] );

		if ( empty( $this->plugin_name ) || in_array( $this->plugin_name, $associated_terms, true ) ) {
			return false;
		}

		if ( ( $this->plugin_name === 'Yoast SEO' && in_array( 'Yoast SEO Premium', $associated_terms, true ) || ( $this->plugin_name === 'Yoast SEO Premium' && in_array( 'Yoast SEO', $associated_terms, true ) ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determines whether the post needs updating based on the changes found.
	 *
	 * @param array $post_data			The post data from the current item.
	 * @param int   $existing_post_id	The post ID known in the database.
	 *
	 * @return array The difference in data between the current post and the one in the database.
	 */
	protected function post_needs_update( $post_data, $existing_post_id ) {
		return array_diff_assoc( sanitize_post( $post_data, 'db' ), get_post( $existing_post_id, ARRAY_A, 'db' ) );
	}

	/**
	 * Logs error messages related to the insertion or updating of a particular post.
	 *
	 * @param string $type		  The type.
	 * @param string $namespace	  The namespace.
	 * @param int 	 $post_id 	  The post ID.
	 * @param int 	 $indentation The amount of indentation to use.
	 *
	 * @return void
	 */
	protected function log_error_message( $type, $namespace, $post_id, $indentation = 1 ) {
		$this->logger->stash_error(
			sprintf(
				'Problem inserting/updating post for %1$s "%2$s"',
				$type,
				$namespace,
				$post_id->get_error_message() ),
			$indentation
		);
	}

	/**
	 * Sets the @since versions for the post based on the passed tag data.
	 *
	 * @param int 	$post_id The post ID.
	 * @param array $tags	 The tag data to extract the since data from.
	 *
	 * @return array The set data.
	 */
	protected function set_since_versions( $post_id, $tags ) {
		$since_versions = wp_list_filter( $tags, [ 'name' => 'since' ] );
		$anything_updated = [];

		if ( empty( $since_versions ) ) {
			return $anything_updated;
		}

		// Loop through all @since versions.
		foreach ( $since_versions as $since_version ) {
			if ( empty( $since_version['content'] ) ) {
				continue;
			}

			$since_term = $this->insert_term( $since_version['content'], $this->taxonomy_since_version );

			// Assign the tax item to the post
			if ( is_wp_error( $since_term ) ) {
				$this->logger->warning( "Cannot set @since term: " . $since_term->get_error_message(), 1 );

				continue;
			}

			$added_term_relationship = did_action( 'added_term_relationship' );

			wp_set_object_terms( $post_id, (int) $since_term['term_id'], $this->taxonomy_since_version, true );

			if ( did_action( 'added_term_relationship' ) > $added_term_relationship ) {
				$anything_updated[] = true;
			}
		}

		return $anything_updated;
	}

	/**
	 * Sets the packages for the post based on the passed tag data.
	 *
	 * @param int 	$post_id The post ID.
	 * @param array $tags	 The tag data to extract the package data from.
	 *
	 * @return array The set data.
	 */
	protected function set_packages( $post_id, $tags ) {
		$anything_updated = [];

		$packages = [
			'main' => wp_list_filter( $tags, [ 'name' => 'package' ] ),
			'sub'  => wp_list_filter( $tags, [ 'name' => 'subpackage' ] ),
		];

		// If the @package/@subpackage is not set by the individual function or class, get it from the file scope
		if ( empty( $packages['main'] ) ) {
			$packages['main'] = wp_list_filter( $this->file_meta['docblock']['tags'], [ 'name' => 'package' ] );
		}

		if ( empty( $packages['sub'] ) ) {
			$packages['sub'] = wp_list_filter( $this->file_meta['docblock']['tags'], [ 'name' => 'subpackage' ] );
		}

		$main_package_id  = false;
		$package_term_ids = [];

		// If the item has any @package/@subpackage markup (or has inherited it from file scope), assign the taxonomy.
		foreach ( $packages as $pack_name => $pack_value ) {
			if ( empty( $pack_value ) ) {
				continue;
			}

			$pack_value = array_shift( $pack_value );
			$pack_value = $pack_value['content'];

			$package_term_args = [ 'parent' => 0 ];

			// Set the parent term_id to look for, as the package taxonomy is hierarchical.
			if ( $pack_name === 'sub' && is_int( $main_package_id ) ) {
				$package_term_args = [ 'parent' => $main_package_id ];
			}

			// If the package doesn't already exist in the taxonomy, add it
			$package_term = $this->insert_term( $pack_value, $this->taxonomy_package, $package_term_args );
			$package_term_ids[] = (int) $package_term['term_id'];

			if ( $pack_name === 'main' && $main_package_id === false && ! is_wp_error( $package_term ) ) {
				$main_package_id = (int) $package_term['term_id'];
			}

			if ( ! is_wp_error( $package_term ) ) {
				continue;
			}

			if ( is_int( $main_package_id ) ) {
				$this->logger->warning( "Cannot create @subpackage term: " . $package_term->get_error_message(), 1 );
			} else {
				$this->logger->warning( "Cannot create @package term: " . $package_term->get_error_message(), 1 );
			}
		}

		$added_term_relationship = did_action( 'added_term_relationship' );
		wp_set_object_terms( $post_id, $package_term_ids, $this->taxonomy_package );

		if ( did_action( 'added_term_relationship' ) > $added_term_relationship ) {
			$anything_updated[] = true;
		}

		return $anything_updated;
	}

	/**
	 * Assign additional plugin taxonomies to the passed post.
	 *
	 * @param int 	 $post_id The post ID.
	 * @param string $plugin  The plugin to assign to the post.
	 *
	 * @return array The term ID's that were set.
	 */
	protected function assign_additional_plugin( $post_id, $plugin ) {
		return wp_set_object_terms( $post_id, $plugin, $this->taxonomy_plugin, true );
	}
}
