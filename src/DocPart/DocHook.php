<?php namespace WP_Parser\DocPart;

use WP_Parser\Exporter;
use WP_Parser\Hook_Reflector;

/**
 * Class DocHook
 * @package WP_Parser\DocPart
 */
class DocHook implements DocPart {
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $line;

	/**
	 * @var int
	 */
	private $end_line;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var array
	 */
	private $arguments;

	/**
	 * @var array
	 */
	private $doc;

	/**
	 * DocHook constructor.
	 *
	 * @param string $name		The name of the hook.
	 * @param int    $line		The line on which the hook's code starts.
	 * @param int    $end_line	The line on which the hook's code ends.
	 * @param string $type		The type of hook.
	 * @param array  $arguments	The arguments passed to the hook.
	 * @param array  $doc		The documentation of the hook.
	 */
	public function __construct( string $name, int $line, int $end_line, string $type, array $arguments, array $doc ) {
		$this->name      = $name;
		$this->line      = $line;
		$this->end_line  = $end_line;
		$this->type      = $type;
		$this->arguments = $arguments;
		$this->doc       = $doc;
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

	/**
	 * Converts the object to an array notation.
	 *
	 * @return array The array notation of the object.
	 */
	public function toArray() {
		return array(
			'name' => $this->name,
			'line' => $this->line,
			'end_line' => $this->end_line,
			'type' => $this->type,
			'arguments' => $this->arguments,
			'doc' => $this->doc,
		);
	}
}
