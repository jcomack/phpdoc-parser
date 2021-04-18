<?php namespace WP_Parser\Commands;

use WP_Parser\Exporters\CSVExporter;
use WP_Parser\Exporters\JSONExporter;

/**
 * Class BaseCommand
 * @package WP_Parser\Commands
 */
abstract class BaseCommand {

	/**
	 * Contains a list of default arguments.
	 *
	 * @var array The default arguments.
	 */
	protected $default_associated_args = [
		'format'       => 'csv',
		'ignore_files' => [],
	];

	/**
	 * Contains a list of valid export formats.
	 *
	 * @var string[] The valid formats.
	 */
	protected $exporters = [
		'csv'  => CSVExporter::class,
		'json' => JSONExporter::class,
	];

	/**
	 * Parses the passed associated arguments.
	 *
	 * @param array $assoc_args The associated arguments to parse.
	 *
	 * @return array The parsed associated arguments.
	 */
	public function parseAssociatedArguments( array $assoc_args ): array {

		$parsed = [];

		foreach ( $assoc_args as $key => $value ) {
			if ( $key === 'ignore_files' ) {
				$parsed[$key] = explode( ',', $value );
			}

			if ( $key === 'format' && in_array( $value, array_keys( $this->exporters ), true ) ) {
				$parsed[$key] = $this->exporters[ $value ];
			} else {
				$parsed[$key] = $this->default_associated_args['format'];
			}

			$parsed[$key] = $value;
		}

		return array_merge(
			$this->default_associated_args,
			$parsed
		);
	}
}
