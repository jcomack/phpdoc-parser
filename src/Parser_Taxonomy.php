<?php namespace WP_Parser;

use ErrorException;

/**
 * Class Parser_Taxonomy
 * @package WP_Parser
 */
class Parser_Taxonomy {
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
	 * @var bool
	 */
	private $hierarchical;
	/**
	 * @var array
	 */
	private $object_types;

	/**
	 * Parser_Post_Type constructor.
	 *
	 * @param string $name		   The name of the taxonomy.
	 * @param string $label		   The label for the taxonomy.
	 * @param string $slug		   The slug for the taxonomy.
	 * @param array  $object_types The object types that the taxonomy supports.
	 * @param bool   $hierarchical Whether or not the taxonomy is hierarchical.
	 */
	public function __construct( string $name, string $label, string $slug, array $object_types = [], bool $hierarchical = true ) {
		$this->name 		= $name;
		$this->label 		= $label;
		$this->slug 		= $slug;
		$this->object_types = $object_types;
		$this->hierarchical = $hierarchical;
	}

	/**
	 * Registers the taxonomy.
	 *
	 * @return void
	 *
	 * @throws ErrorException
	 */
	public function register() {
		if ( ! taxonomy_exists( $this->name ) ) {
			register_taxonomy(
				$this->name,
				$this->object_types,
				array(
					'hierarchical'          => $this->hierarchical,
					'label'                 => __( $this->label, 'wp-parser' ),
					'public'                => true,
					'rewrite'               => array( 'slug' => $this->slug ),
					'sort'                  => false,
					'update_count_callback' => '_update_post_term_count',
				)
			);
		}

		throw new ErrorException( sprintf( 'Taxonomy with name %s already exists', $this->name ) );
	}
}