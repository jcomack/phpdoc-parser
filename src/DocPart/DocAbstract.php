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

	public function __construct( SplFileInfo $file, $root, PluginInterface $plugin ) {
		$this->filename     = $file->getFilename();
		$this->fullPath 	= $file->getPathname();
		$this->relativePath = $file->getRelativePathname();
		$this->root         = $root;
		$this->plugin		= $plugin;
	}

	abstract protected function parse();
}
