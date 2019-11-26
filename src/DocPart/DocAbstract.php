<?php namespace WP_Parser\DocPart;

use Symfony\Component\Finder\SplFileInfo;
use WP_Parser\PluginParser\PluginInterface;

/**
 * Class DocAbstract
 * @package WP_Parser\DocPart
 */
abstract class DocAbstract {

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var string
	 */
	protected $fullPath;

	/**
	 * @var string
	 */
	protected $relativePath;

	/**
	 * @var string
	 */
	protected $root;

	/**
	 * @var PluginInterface
	 */
	protected $plugin;

	/**
	 * DocAbstract constructor.
	 *
	 * @param SplFileInfo     $file   The file to parse.
	 * @param string      	  $root   The root of the file.
	 * @param PluginInterface $plugin The plugin data collector.
	 */
	public function __construct( SplFileInfo $file, $root, PluginInterface $plugin ) {
		$this->filename		= $file->getFilename();
		$this->fullPath		= $file->getPathname();
		$this->relativePath	= $file->getRelativePathname();
		$this->root 		= $root;
		$this->plugin		= $plugin;
	}

	public function getPluginName() {
		return $this->plugin->getName();
	}

	public function getPluginVersion() {
		return $this->plugin->getVersion();
	}

	/**
	 * Parses the file data.
	 *
	 * @return mixed The parsed data.
	 */
	abstract protected function parse();

	public function __get( $property ) {
		if ( property_exists( $this, $property ) ) {
			return $this->{$property};
		}

		return null;
	}
}
