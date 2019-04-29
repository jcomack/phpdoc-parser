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
	 * @param string $directory		The directory to get the files from.
	 * @param array  $exclude_files Files to exclude.
	 */
	public function __construct( $directory, $exclude_files = [] ) {
		$this->directory 	 = $directory;
		$this->exclude_files = $exclude_files;
	}

	/**
	 * Finds the plugin data within the directory.
	 *
	 * @return void
	 */
	public function find() {
		$files = Utils::get_files( $this->directory, $this->exclude_files );

		foreach ( $files as $file ) {
			$plugin_data = $this->collect_plugin_data( $file );

			if ( $plugin_data === [] ) {
				continue;
			}

			$this->plugin = $plugin_data;
			break;
		}
	}

	/**
	 * Determines whether plugin data was found.
	 *
	 * @return bool True if plugin data was found.
	 */
	public function has_plugin() {
		return $this->plugin !== [];
	}

	/**
	 * Gets the plugin information that was collected.
	 *
	 * @return array The plugin data.
	 */
	public function get_plugin() {
		return $this->plugin;
	}

	/**
	 * Collects the plugin data.
	 *
	 * @param string $file The file to collect the plugin data from.
	 *
	 * @return array The plugin data. Empty if none could be found.
	 */
	private function collect_plugin_data( $file ) {
		$plugin_data = get_plugin_data( $file, false, false );

		if ( ! empty( $plugin_data['Name'] ) ) {
			return $plugin_data;
		}

		return [];
	}

}
