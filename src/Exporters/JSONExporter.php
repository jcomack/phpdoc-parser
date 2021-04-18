<?php namespace WP_Parser\Exporters;

use Tightenco\Collect\Support\Collection;

/**
 * Class JSONExporter
 * @package WP_Parser\Exporters
 */
class JSONExporter {

	/**
	 * @param            $file
	 * @param Collection $content
	 * @param string     $directory
	 *
	 * @return false|int
	 */
	public static function export( $file, Collection $content, string $directory = PHPDOC_PARSER_DIR ) {
		$hooks = $content
			->reject( function ( $item ) {
				return empty( $item->getHooks() );
			} )
			->mapWithKeys( function ( $item ) {
				$cHooks = new Collection( $item->getHooks() );

				return [
					$item->getPath() => $cHooks->mapWithKeys( function ( $hook ) {
						return [
							$hook->getName() => [
								'start' => $hook->getLine(),
								'doc'   => $hook->getDoc(),
							],
						];
					} ),
				];
			} );

		return file_put_contents( $directory . DIRECTORY_SEPARATOR . $file, json_encode( $hooks, JSON_PRETTY_PRINT ) );
	}
}
