<?php namespace WP_Parser\Generators;

use Tightenco\Collect\Support\Collection;
use WP_Parser\PluginFinder;
use WP_Parser\Runner;
use WP_Parser\Utils;
use WP_CLI;
use WP_CLI_Command;

/**
 * Class DocGenerator
 * @package WP_Parser\Generators
 */
class DocGenerator {

	/**
	 * A list of files to ignore.
	 *
	 * @var string[] The files to ignore.
	 */
	protected $default_ignore_files = [
		'vendor',
		'vendor_prefixed',
		'node_modules',
		'integration-tests',
		'tests',
		'build',
		'config',
		'grunt',
		'deploy_keys',
		'js',
		'languages',
		'webpack',
		'images',
		'css',
	];

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var array
	 */
	protected $ignore_files;

	/**
	 * DocGenerator constructor.
	 *
	 * @param string $path         Directory or file to scan for PHPDoc
	 * @param array  $ignore_files What files to ignore.
	 *
	 */
	public function __construct( string $path, $ignore_files = [] ) {
		$this->path = $path;
		$this->ignore_files = $ignore_files;
	}

	/**
	 * Generate the data from the PHPDoc markup.
	 *
	 * @return string|array
	 */
	public function get_data(): Collection {
		$ignore_files = ! empty( $this->ignore_files ) ? $this->ignore_files : $this->default_ignore_files;

		WP_CLI::line( sprintf( 'Extracting PHPDoc from %1$s. This may take a few minutes...', $this->path ) );

		// Collect the files.
		$files   = $this->collect_files( $this->path, $ignore_files );
		$folder  = is_file( $this->path ) ? dirname( $this->path ) : $this->path;

		if ( $files instanceof WP_Error ) {
			WP_CLI::error( sprintf( 'Problem with %1$s: %2$s', $folder, $files->get_error_message() ) );
			exit;
		}

		// Loop through files and get plugin data.
		$plugin_data = PluginFinder::find( $files );

		return ( new Runner( $folder, $plugin_data ) )
			->parse_files( $files );
	}

	protected function collect_files( $path, $ignore_files ) {
		return is_file( $path ) ? [ $path ] : Utils::get_files( $path, $ignore_files );
	}
}
