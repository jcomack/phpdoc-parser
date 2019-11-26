<?php namespace WP_Parser;

use Symfony\Component\Finder\Finder;
use Tightenco\Collect\Support\Collection;
use WP_CLI;
use WP_Parser\PluginParser\PluginInterface;

/**
 * Class Runner
 *
 * @package WP_Parser
 */
class Runner {

	/**
	 * @var Exporter
	 */
	private $exporter;

	/**
	 * @var PluginInterface
	 */
	private $plugin;

	/**
	 * @var string
	 */
	private $directory;

	/**
	 * Runner constructor.
	 *
	 * @param string 		  $directory The directory in which the runner should run.
	 * @param PluginInterface $plugin	 The plugin associated with the code.
	 */
	public function __construct( string $directory, PluginInterface $plugin ) {
		$this->exporter = new Exporter();

		$this->directory = $directory;
		$this->plugin = $plugin;
	}

	/**
	 * Parses the passed files and attempts to extract the proper docblocks from the files.
	 *
	 * @param Finder $files The files to extract the docblocks from.
	 *
	 * @return Collection The extracted docblocks.
	 */
	public function parse_files( Finder $files ) {
		return DocPartFactory::fromFiles( $files, $this->directory, $this->plugin );
	}
}
