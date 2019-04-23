<?php namespace WP_Parser;

/**
 * Class PluginFinder
 *
 * @package WP_Parser
 */
class PluginFinder {

	/**
	 * @var string
	 */
	private $directory;

	/**
	 * @var array
	 */
	private $plugin = [];

	/**
	 * @var array
	 */
	private $exclude_files;

	/**
	 * PluginFinder constructor.
	 *
	 * @param string $directory	   The directory to search in.
	 * @param array  $exclude_files The files to exclude.
	 */
	public function __construct( $directory, $exclude_files = array() ) {
		$this->directory 	 = $directory;
		$this->exclude_files = $exclude_files;
	}

	/**
	 * Finds files and collects plugin data.
	 *
	 * @return void
	 */
	public function find() {
		$files = Utils::get_files( $this->directory, $this->exclude_files );

		foreach ( $files as $file ) {
			$plugin_data = $this->get_plugin_data( $file );

			if ( $plugin_data === array() ) {
				continue;
			}

			$this->plugin = $plugin_data;
			break;
		}
	}

	/**
	 * Determines whether or not there's a valid plugin present.
	 *
	 * @return bool Whether or not a plugin was found.
	 */
	public function has_plugin() {
		return $this->plugin !== array();
	}

	/**
	 * Gets the plugin information.
	 *
	 * @return array The plugin information.
	 */
	public function get_plugin() {
		return $this->plugin;
	}

	/**
	 * Gets the plugin data for the passed file.
	 *
	 * @param string $file The file to get the plugin data for.
	 *
	 * @return array The plugin data or an empty array if none is present.
	 */
	private function get_plugin_data( $file ) {
		$plugin_data = get_plugin_data( $file, false, false );

		if ( ! empty( $plugin_data['Name'] ) ) {
			return $plugin_data;
		}

		return array();
	}

}
