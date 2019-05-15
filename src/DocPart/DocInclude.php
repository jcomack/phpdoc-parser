<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\IncludeReflector;

/**
 * Class DocInclude
 * @package WP_Parser\DocPart
 */
class DocInclude implements DocPart {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $line_number;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * DocInclude constructor.
	 *
	 * @param string $name		  The name of the include.
	 * @param int    $line_number The line on which the include is defined.
	 * @param string $type		  The type of inclusion.
	 */
	public function __construct( string $name, int $line_number, string $type ) {
		$this->name = $name;
		$this->line_number = $line_number;
		$this->type = $type;
	}

	/**
	 * Gets the name.
	 *
	 * @return string The name of the include.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Gets the line number.
	 *
	 * @return int The line number on which the include is defined.
	 */
	public function getLineNumber() {
		return $this->line_number;
	}

	/**
	 * Gets the type.
	 *
	 * @return string The type of inclusion.
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param IncludeReflector[] $items
	 *
	 * @return array
	 */

	/**
	 * Creates an include from the reflector.
	 *
	 * @param IncludeReflector[] $items The array of reflectors to convert.
	 *
	 * @return DocInclude The inclusion instance.
	 */
	public static function fromReflector( $items ) {
		return array_map( function( $item ) {
			/** @var IncludeReflector $item */
			return new self( $item->getName(), $item->getLineNumber(), $item->getType() );
		}, $items );
	}

	/**
	 * Converts the object to an array notation.
	 *
	 * @return array The array notation of the object.
	 */
	public function toArray() {
		return array(
			'name' => $this->name,
			'line' => $this->line_number,
			'type' => $this->type
		);
	}
}
