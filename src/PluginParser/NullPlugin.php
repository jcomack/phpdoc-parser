<?php namespace WP_Parser\PluginParser;

/**
 * Class NullPlugin
 * @package WP_Parser\PluginParser
 */
class NullPlugin implements PluginInterface {

	/**
	 * Returns a value of null.
	 *
	 * @param string $name The property to retrieve.
	 *
	 * @return null Always returns null.
	 */
	public function __get( $name ) {
		return null;
	}
}
