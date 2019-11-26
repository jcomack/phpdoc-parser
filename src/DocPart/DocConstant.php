<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\ConstantReflector;

/**
 * Class DocConstant
 * @package WP_Parser\DocPart
 */
class DocConstant implements DocPart {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $line;

	/**
	 * @var string
	 */
	private $value;

	/**
	 * DocConstant constructor.
	 *
	 * @param string $name	The name of the constant.
	 * @param int $line		The line on which the constant is defined.
	 * @param string $value	The value of the constant.
	 */
	public function __construct( string $name, int $line, string $value ) {
		$this->name = $name;
		$this->line = $line;
		$this->value = $value;
	}

	/**
	 * Gets the name.
	 *
	 * @return string The name of the constant.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Gets the line number.
	 *
	 * @return int The line number on which the constant exists.
	 */
	public function getLine() {
		return $this->line;
	}

	/**
	 * Gets the value.
	 *
	 * @return string The value of the constant.
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Creates a constant from the reflector.
	 *
	 * @param ConstantReflector[] $items The constant items to convert.
	 *
	 * @return array The converted constant items.
	 */
	public static function fromReflector( $items ) {
		return array_map( function( $item ) {
			/** @var ConstantReflector $item */
			return new self( $item->getName(), $item->getLineNumber(), $item->getValue() );
		}, $items );
	}
}
