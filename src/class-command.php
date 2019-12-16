<?php

namespace WP_Parser;

use Tightenco\Collect\Support\Collection;
use WP_CLI;
use WP_CLI_Command;
use WP_Error;
use WP_Parser\Loggers\CommandLineLogger;

/**
 * Converts PHPDoc markup into a template ready for import to a WordPress blog.
 */
class Command extends WP_CLI_Command {

	/**
	 * @var array
	 */
	private $validTaxonmies = [ 'wp-parser-since' ];

	/**
	 * Generate a JSON file containing the PHPDoc markup, and save to filesystem.
	 *
	 * @synopsis <directory> [<output_file>] [--ignore_files]
	 *
	 * @param array $args       The arguments to pass to the command.
	 * @param array $assoc_args The associated arguments to pass to the command.
	 */
	public function export( $args, $assoc_args ) {
		$directory    = realpath( $args[0] );
		$output_file  = empty( $args[1] ) ? 'phpdoc.json' : $args[1];
		$ignore_files = $this->getIgnoreFiles( $assoc_args );

		$json        = $this->_get_phpdoc_data( $directory, 'json', $ignore_files );
		$result      = file_put_contents( $output_file, $json );
		WP_CLI::line();

		if ( $result === false ) {
			WP_CLI::error( sprintf( 'Problem writing %1$s bytes of data to %2$s', strlen( $json ), $output_file ) );
			exit;
		}

		WP_CLI::success( sprintf( 'Data exported to %1$s', $output_file ) );
		WP_CLI::line();
	}

	/**
	 * Read a JSON file containing the PHPDoc markup, convert it into WordPress posts, and insert into DB.
	 *
	 * @synopsis <file> [--quick] [--import-internal]
	 *
	 * @param array $args		The arguments to pass to the command.
	 * @param array $assoc_args The associated arguments to pass to the command.
	 */
	public function import( $args, $assoc_args ) {
		list( $file ) = $args;
		WP_CLI::line();

		// Get the data from the <file>, and check it's valid.
		$phpdoc = false;

		if ( is_readable( $file ) ) {
			$phpdoc = file_get_contents( $file );
		}

		if ( ! $phpdoc ) {
			WP_CLI::error( sprintf( "Can't read %1\$s. Does the file exist?", $file ) );
			exit;
		}

		$phpdoc = json_decode( $phpdoc, true );
		if ( is_null( $phpdoc ) ) {
			WP_CLI::error( sprintf( "JSON in %1\$s can't be decoded :(", $file ) );
			exit;
		}

		// Import data
		$this->_do_import( $phpdoc, isset( $assoc_args['import-internal'] ) );
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
	public function create( $args, $assoc_args ) {
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
	 * Wipes terms based on the passed type.
	 *
	 * @subcommand wipe
	 * @synopsis   <taxonomy> [--user]
	 *
	 * @param array $args       The arguments to pass to the command.
	 * @param array $assoc_args The associated arguments to pass to the command.
	 */
	public function wipe( $args, $assoc_args ) {
		$type = $args[0];

		if ( ! in_array( $type, $this->validTaxonmies ) ) {
			WP_CLI::error( sprintf( 'Cannot wipe taxonomy of type: %1$s', $type ) );
		}

		$this->clearTerm( $type );
	}

	/**
	 * Generate the data from the PHPDoc markup.
	 *
	 * @param string $path         Directory or file to scan for PHPDoc
	 * @param string $format       What format the data is returned in: [json|array].
	 * @param array  $ignore_files What files to ignore.
	 *
	 * @return string|array
	 */
	protected function _get_phpdoc_data( $path, $format = 'json', $ignore_files = [] ) {
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
		$importer->setLogger( new CommandLineLogger() );
		$importer->import( $data, $import_ignored );

		WP_CLI::line();
	}

	/**
	 * Clears the passed taxonomy.
	 *
	 * @param string $taxonomy The taxonomy to clear.
	 */
	private function clearTerm( string $taxonomy ) {
		if ( ! wp_get_current_user()->exists() ) {
			WP_CLI::error( 'Please specify a valid user: --user=<id|login>' );
			exit;
		}

		$terms = get_terms( [
			'fields' => 'ids',
			'hide_empty' => false,
			'taxonomy' => $taxonomy,
		] );

		if ( count( $terms ) === 0 ) {
			WP_CLI::line( 'No terms to delete' );
			exit;
		}

		if ( $terms instanceof WP_Error ) {
			WP_CLI::error( sprintf( 'Problem with collecting terms: %1$s', $terms->get_error_message() ) );
			exit;
		}

		foreach ( $terms as $term ) {
			wp_delete_term( $term, $taxonomy );
		}

		WP_CLI::line( sprintf( 'Wiped terms of taxonomy `%1$s`', $taxonomy ) );
	}

	/**
	 * Gets the ignored files from the associated arguments.
	 *
	 * @param array $assoc_args The associated arguments.
	 *
	 * @return array The ignored files.
	 */
	protected function getIgnoreFiles( $assoc_args ): array {
		$ignore_files = empty( $assoc_args['ignore_files'] ) ? [] : explode( ',', $assoc_args['ignore_files'] );

		return $ignore_files;
}
}
