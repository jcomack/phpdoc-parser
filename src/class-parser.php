<?php namespace WP_Parser;

use WP_CLI;
use WP_Post;

/**
 * Main plugin's class. Registers things and adds WP CLI command.
 */
class Parser {

	/**
	 * Sets up the parser code.
	 *
	 * @return void
	 */
	public function on_load() {

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'parser', __NAMESPACE__ . '\\Command' );
		}

		add_filter( 'wp_parser_get_arguments', [ $this, 'make_args_safe' ] );
		add_filter( 'wp_parser_return_type', [ $this, 'humanize_separator' ] );
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
