<?php namespace WP_Parser;

use WP_Parser\PluginParser\Author;
use WP_Parser\PluginParser\NullPlugin;
use WP_Parser\PluginParser\Plugin;
use WP_Parser\PluginParser\Textdomain;

/**
 * Class PluginFinder
 * @package WP_Parser
 */
class PluginFinder {

	/**
	 * Finds the plugin data and returns a Plugin object, if data is found.
	 *
	 * @param iterable $files The files to search through.
	 *
	 * @return NullPlugin|Plugin The plugin data.
	 */
	public static function find( iterable $files ) {
		$plugin = new NullPlugin();

		foreach ( $files as $file ) {
			$plugin_data = get_plugin_data( $file, false, false );

			if ( ! empty( $plugin_data['Name'] ) ) {
				$plugin = new Plugin(
					$plugin_data['Name'],
					$plugin_data['PluginURI'],
					$plugin_data['Version'],
					$plugin_data['Description'],
					new Author( $plugin_data['Author'], $plugin_data['AuthorURI'] ),
					new Textdomain( $plugin_data['TextDomain'], $plugin_data['DomainPath'] ),
					$plugin_data['Network']
				);
				break;
			}
		}

		return $plugin;
	}

}
