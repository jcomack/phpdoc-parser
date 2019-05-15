<?php namespace WP_Parser;

use Exception;
use Symfony\Component\Finder\Finder;
use WP_Error;

/**
 * Class Utils
 * @package WP_Parser
 */
class Utils {

	/**
	 * Gets files from the passed directory.
	 *
	 * @param string $directory		The directory to get the files from.
	 * @param array $exclude_files	The files and directories to exclude.
	 *
	 * @return Finder|WP_Error The found files. Throws an error if the directory can't be accessed.
	 */
	public static function get_files( $directory, $exclude_files = array() ) {
		$finder = new Finder();

		try {
			return $finder
				->ignoreDotFiles( true )
				->ignoreVCS( true )
				->exclude( $exclude_files )
				->files()
				->name( '*.php' )
				->in( $directory );
		} catch( Exception $e ) {
			return new WP_Error(
				'unexpected_value_exception',
				sprintf( 'Directory [%s] contained a directory we can not recurse into', $directory )
			);
		}
	}
}
