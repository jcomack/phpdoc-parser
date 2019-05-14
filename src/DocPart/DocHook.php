<?php namespace WP_Parser\DocPart;

use WP_Parser\Exporter;
use WP_Parser\Hook_Reflector;

/**
 * Class DocHook
 * @package WP_Parser\DocPart
 */
class DocHook {
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
	 * @param string $name
	 * @param int    $line
	 * @param int    $end_line
	 * @param string $type
	 * @param array  $arguments
	 * @param array  $doc
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
	 * @param Hook_Reflector $hook
	 *
	 * @return DocHook
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

	public function toArray() {
		return [
			'name' => $this->name,
			'line' => $this->line,
			'end_line' => $this->end_line,
			'type' => $this->type,
			'arguments' => $this->arguments,
			'doc' => $this->doc,
		];
	}
}
