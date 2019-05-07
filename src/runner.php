<?php namespace WP_Parser;

use Symfony\Component\Finder\Finder;
use WP_CLI;
use WP_Parser\PluginParser\PluginInterface;

/**
 * Class Runner
 *
 * @package WP_Parser
 */
class Runner {
	private $exporter;
	private $plugin;
	private $directory;

	/**
	 * Runner constructor.
	 *
	 * @param                 $directory
	 * @param PluginInterface $plugin
	 */
	public function __construct( $directory, PluginInterface $plugin ) {
		$this->exporter = new Exporter();

		$this->directory = $directory;
		$this->plugin = $plugin;
	}

	/**
	 * Parses the passed files and attempts to extract the proper docblocks from the files.
	 *
	 * @param Finder $files The files to extract the docblocks from.
	 *
	 * @return array The extracted docblocks.
	 */
	public function parse_files( Finder $files ) {
		$parsed_files = DocFileFactory::fromFiles( $files, $this->directory, $this->plugin );

		return array_map( function( $file ) { return $file->toArray(); }, $parsed_files );
	}
}
