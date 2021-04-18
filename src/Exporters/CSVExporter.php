<?php namespace WP_Parser\Exporters;

use Tightenco\Collect\Support\Collection;
use WP_Parser\DocPart\DocHook;

/**
 * Class CSVExporter
 * @package WP_Parser\Exporters
 */
class CSVExporter {
	/**
	 * Exports the passed contents to a CSV file.
	 *
	 * @param string     $file		The file to output to.
	 * @param Collection $content	The content of the file to write.
	 * @param string     $directory The directory to write the file to.
	 *
	 * @return bool Always returns true to signal that exporting is complete.
	 */
	public static function export( string $file, Collection $content, string $directory = PHPDOC_PARSER_DIR ) {
		$content_with_hooks = $content
			->reject( function ( $item ) {
				return empty( $item->getHooks() );
			} );

		$output = [];
		$output[] = [ 'file', 'hook', 'line', 'description' ];
		$success = false;

		foreach ( $content_with_hooks->toArray() as $key => $value ) {
			$output[] = [ $value->getPath(), '', '', '' ];

			/** @var DocHook $hook */
			foreach ( $value->getHooks() as $hook ) {
				$output[] = [ '', $hook->getName(), $hook->getLine(), $hook->getDoc()['description'] ];
			}
		}

		try {
			// Your code here...
			$fp = fopen( $directory . DIRECTORY_SEPARATOR . $file, 'w');

			foreach ($output as $fields) {
				fputcsv($fp, $fields);
			}

			fclose($fp);

			$success = true;
		} catch ( \Exception $exception ) {
			// TODO: log issues to file.
		}

		return $success;
	}
}
