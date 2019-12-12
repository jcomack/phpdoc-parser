<?php namespace WP_Parser\DocPart;

use WP_Parser\Exporter;
use WP_Parser\Hook_Reflector;

/**
 * Class DocHook
 * @package WP_Parser\DocPart
 */
class DocHook extends BaseDocPart implements DocPart {

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var array
	 */
	private $arguments;

	/**
	 * DocHook constructor.
	 *
	 * @param string $name      The name of the hook.
	 * @param int    $line      The line on which the hook's code starts.
	 * @param int    $end_line  The line on which the hook's code ends.
	 * @param string $type      The type of hook.
	 * @param array  $arguments The arguments passed to the hook.
	 * @param array  $docblock  The documentation of the hook.
	 */
	public function __construct( string $name, int $line, int $end_line, string $type, array $arguments, array $docblock ) {
		parent::__construct( $name, '', $line, $end_line, $docblock );

		$this->type      = $type;
		$this->arguments = $arguments;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @return array
	 */
	public function getArguments(): array {
		return $this->arguments;
	}


	/**
	 * Creates a hook from the reflector.
	 *
	 * @param Hook_Reflector $hook The hook reflector to convert.
	 *
	 * @return DocHook The hook instance.
	 */
	public static function fromReflector( $hook ) {
		$exporter = new Exporter();

		return new self(
			$hook->getName(),
			$hook->getLinenumber(),
			$hook->getNode()->getAttribute( 'endLine' ),
			$hook->getType(),
			$hook->getArgs(),
			$exporter->export_docblock( $hook )
		);
	}
}
