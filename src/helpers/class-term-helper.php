<?php namespace WP_Parser\helpers;

/**
 * Class TermHelper
 * @package WP_Parser\helpers
 */
final class TermHelper {

	/**
	 * @var array
	 */
	private $inserted_terms = [];

	/**
	 * Inserts the term for the passed taxonomy.
	 *
	 * @param int|string $term		The term to insert.
	 * @param string     $taxonomy	The taxonomy to insert the term for.
	 * @param array      $args		Additional arguments.
	 *
	 * @return array|WP_Error Returns the inserted term or error if the insertion fails.
	 */
	public function insert_term( $term, $taxonomy, $args = [] ) {
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
}
