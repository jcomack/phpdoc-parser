<?php namespace WP_Parser;

use ErrorException;

/**
 * Class Parser_Post_Type
 * @package WP_Parser
 */
class Parser_Post_Type {
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $label;
	/**
	 * @var string
	 */
	private $slug;
	/**
	 * @var string
	 */
	private $archive_name;
	/**
	 * @var array
	 */
	private $supports;

	/**
	 * Parser_Post_Type constructor.
	 *
	 * @param string $name		   The name of the post type.
	 * @param string $label		   The label for the post type.
	 * @param string $slug		   The slug for the post type.
	 * @param string $archive_name The archive name.
	 * @param array  $supports	   What type of support is offered by the post type.
	 */
	public function __construct( string $name, string $label, string $slug, string $archive_name, array $supports = [] ) {
		$this->name 		= $name;
		$this->label 		= $label;
		$this->slug 		= $slug;
		$this->archive_name = $archive_name;
		$this->supports 	= $supports;
	}

	/**
	 * Registers the post type.
	 *
	 * @return void
	 *
	 * @throws ErrorException
	 */
	public function register() {
		if ( ! post_type_exists( $this->name ) ) {

			register_post_type(
				$this->name,
				[
					'has_archive' => $this->archive_name,
					'label'       => __( $this->label, 'wp-parser' ),
					'public'      => true,
					'rewrite'     => [
						'feeds'      => false,
						'slug'       => $this->slug,
						'with_front' => false,
					],
					'supports'    => $this->supports,
				]
			);
		}

		throw new ErrorException( sprintf( 'Post type with name %s already exists', $this->name ) );
	}
}