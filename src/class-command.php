<?php

namespace WP_Parser;

use Tightenco\Collect\Support\Collection;
use WP_CLI;
use WP_CLI_Command;
use WP_Error;
use WP_Parser\Commands\ExportCommand;

/**
 * Converts PHPDoc markup into a template ready for import to a WordPress blog.
 */
class Command extends WP_CLI_Command {

	/**
	 * Generate a JSON file containing the PHPDoc markup, and save to filesystem.
	 *
	 * @synopsis <directory> [<output_file>] [--ignore_files] [--format]
	 *
	 * @param array $args       The arguments to pass to the command.
	 * @param array $assoc_args The associated arguments to pass to the command.
	 */
	public function export( array $args, array $assoc_args ) {
		$command = new ExportCommand( $args, $assoc_args );
		$results = $command->run();

		WP_CLI::line();

		if ( $results['data'] === false ) {
			WP_CLI::error( sprintf( 'Problem writing data to %1$s', $results['file'] ) );
			exit;
		}

		WP_CLI::success( sprintf( 'Data exported to %1$s', $results['file'] ) );
		WP_CLI::line();
	}

	/**
	 * Generate JSON containing the PHPDoc markup, convert it into WordPress posts, and insert into DB.
	 *
	 * @subcommand create
	 * @synopsis   <directory> [--quick] [--import-internal] [--user] [--ignore_files]
	 *
	 * @param array $args       The arguments to pass to the command.
	 * @param array $assoc_args The associated arguments to pass to the command.
	 */
	public function create( array $args, array $assoc_args ) {
		list( $directory ) = $args;
		$directory = realpath( $directory );
		$ignore_files = $this->getIgnoreFiles( $assoc_args );

		if ( empty( $directory ) ) {
			WP_CLI::error( sprintf( "Can't read %1\$s. Does the file exist?", $directory ) );
			exit;
		}

		WP_CLI::line();

		$data = $this->_get_phpdoc_data( $directory, 'array', $ignore_files );

		// Import data
		$this->_do_import( $data, isset( $assoc_args['import-internal'] ) );
	}

	/**
	 * Generate the data from the PHPDoc markup.
	 *
	 * @param string $path         Directory or file to scan for PHPDoc
	 * @param string $format       What format the data is returned in: [json|array].
	 * @param array  $ignore_files What files to ignore.
	 *
	 * @return Collection The collected data.
	 */
	protected function _get_phpdoc_data( string $path, $format = 'json', $ignore_files = [] ) {
		$ignore_files = ! empty( $ignore_files ) ? $ignore_files : [
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

		WP_CLI::line( sprintf( 'Extracting PHPDoc from %1$s. This may take a few minutes...', $path ) );

		// Collect the files.
		$is_file = is_file( $path );
		$files   = $is_file ? [ $path ] : Utils::get_files( $path, $ignore_files );
		$path    = $is_file ? dirname( $path ) : $path;

		if ( $files instanceof WP_Error ) {
			WP_CLI::error( sprintf( 'Problem with %1$s: %2$s', $path, $files->get_error_message() ) );
			exit;
		}

		// Loop through files and get plugin data.
		$plugin_data = PluginFinder::find( $files );

		$runner = new Runner( $path, $plugin_data );
		$output = $runner->parse_files( $files );

		if ( $format === 'json' ) {
			return json_encode( $output, JSON_PRETTY_PRINT );
		}

		return $output;
	}

	/**
	 * Import the PHPDoc $data into WordPress posts and taxonomies
	 *
	 * @param Collection $data
	 * @param bool  $import_ignored If true, functions marked `@ignore` will be imported.
	 */
	protected function _do_import( Collection $data, $import_ignored = false ) {

		if ( ! wp_get_current_user()->exists() ) {
			WP_CLI::error( 'Please specify a valid user: --user=<id|login>' );
			exit;
		}

		// Run the importer
		$importer = new Importer();
		$importer->setLogger( new WP_CLI_Logger() );
		$importer->import( $data, $import_ignored );

		WP_CLI::line();
	}

	/**
	 * @param $assoc_args
	 *
	 * @return array
	 */
	protected function getIgnoreFiles( $assoc_args ): array {
		$ignore_files = empty( $assoc_args['ignore_files'] ) ? [] : explode( ',', $assoc_args['ignore_files'] );

		return $ignore_files;
}
}
