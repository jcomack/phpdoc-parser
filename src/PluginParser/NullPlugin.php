<?php namespace WP_Parser\PluginParser;

/**
 * Class NullPlugin
 * @package WP_Parser\PluginParser
 */
class NullPlugin implements PluginInterface {
	public function __get( $name ) {
		return null;
	}
}
