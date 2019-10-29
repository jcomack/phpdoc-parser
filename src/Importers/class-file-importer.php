<?php namespace WP_Parser\Importers;

use Psr\Log\LoggerInterface;
use WP_Parser\helpers\TermHelper;
use WP_Parser\Importer;

/**
 * Class FileImporter
 * @package WP_Parser\Importers
 */
class FileImporter {
	/**
	 * @var array
	 */
	private $files;

	/**
	 * @var bool
	 */
	private $import_ignored;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var TermHelper
	 */
	private $term_helper;

	/**
	 * @var string
	 */
	private $plugin_name;

	public function __construct( array $files, bool $import_ignored, LoggerInterface $logger ) {
		$this->files = $files;
		$this->import_ignored = $import_ignored;
		$this->logger = $logger;

		$this->term_helper = new TermHelper();
	}

	public function import() {
		// Import files.

		$file_number = 1;
		$total_files = count( $this->files );

		foreach ( $this->files as $file ) {
			$this->logger->info(
				sprintf(
					'Processing file %1$s of %2$s "%3$s".',
					number_format_i18n( $file_number ),
					number_format_i18n( $total_files ),
					$file['path']
				)
			);

			$file_number++;

			if ( ! isset( $this->plugin_name ) || $this->plugin_name !== $file['plugin'] ) {
				$this->plugin_name = $file['plugin'];
			}

			$plugin_term = $this->register_plugin_term( $file );
			$file_term = $this->register_file_term( $file );

			if ( ! $plugin_term || ! $file_term ) {
				return;
			}




			if ( empty( $root ) && ( isset( $file['root'] ) && $file['root'] ) ) {
				$root = $file['root'];
			}
		}
	}

	protected function register_file_term( array $file ) {
		$slug = sanitize_title( str_replace( '/', '_', $file['path'] ) );

		$file_term = $this->term_helper->insert_term( $file['path'], Importer::$taxonomy_file, [ 'slug' => $slug ] );

		if ( is_wp_error( $file_term ) ) {
			$this->logger->stash_error(
				sprintf(
					'Problem creating file taxonomy item "%1$s" for %2$s: %3$s',
					$slug,
					$file['path'],
					$file_term->get_error_message()
				)
			);

			return false;
		}

		return $file_term;
	}

	protected function register_plugin_term( array $file ) {
		$plugin_term = $this->term_helper->insert_term( $file['plugin'], Importer::$taxonomy_plugin );

		if ( is_wp_error( $plugin_term ) ) {
			$this->logger->stash_error(
				sprintf(
					'Problem creating plugin taxonomy item "%1$s" for %2$s',
					$file['plugin'],
					$plugin_term->get_error_message()
				)
			);

			return false;
		}

		add_term_meta( $plugin_term['term_id'], '_wp-parser-plugin-directory', plugin_basename( $file['root'] ), true );

		return $plugin_term;
	}

}
