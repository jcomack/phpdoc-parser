<?php namespace WP_Parser;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use WP_Parser\DocPart\DocFile;
use WP_Parser\PluginParser\PluginInterface;

/**
 * Class DocFileFactory
 * @package WP_Parser
 */
class DocFileFactory {

	/**
	 * @param Finder          $files
	 * @param                 $root_directory
	 * @param PluginInterface $plugin
	 *
	 * @return array
	 */
	public static function fromFiles( Finder $files, $root_directory, PluginInterface $plugin ) {
		$doc_files = [];

		/** @var SplFileInfo $file */
		foreach ( $files as $file ) {
			$doc_files[] = new DocFile( $file, $root_directory, $plugin );
		}

		return $doc_files;
	}
}
