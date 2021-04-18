<?php namespace WP_Parser\Commands;

use WP_Parser\Generators\DocGenerator;

/**
 * Class ExportCommand
 * @package WP_Parser\Commands
 */
class ExportCommand extends BaseCommand {

	/**
	 * @var DocGenerator
	 */
	protected $generator;

	/**
	 * @var array
	 */
	protected $args;

	/**
	 * @var array
	 */
	protected $associated_args;

	/**
	 * ExportCommand constructor.
	 *
	 * @param array $args
	 * @param array $associated_args
	 */
	public function __construct( array $args, array $associated_args ) {
		$this->args            = $args;
		$this->associated_args = $this->parseAssociatedArguments( $associated_args );
		$this->generator       = new DocGenerator( realpath( $args[0] ), $this->associated_args['ignore_files'] );
	}

	/**
	 * @return mixed
	 */
	public function run() {
		$format      = $this->associated_args['format'];
		$output_file = empty( $this->args[1] ) ? 'phpdoc.' . $format : $this->args[1];
		$data        = $this->generator->get_data();

		return [
			'file' => $output_file,
			'data' => $this->exporters[ $format ]::export( $output_file, $data )
		];
	}
}
