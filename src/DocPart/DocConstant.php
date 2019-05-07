<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\ConstantReflector;

/**
 * Class DocConstant
 * @package WP_Parser\DocPart
 */
class DocConstant {
	private $name;
	private $line;
	private $value;

	public function __construct( $name, $line, $value ) {
		$this->name = $name;
		$this->line = $line;
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getLine() {
		return $this->line;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}


	/**
	 * @param ConstantReflector[] $items
	 *
	 * @return array
	 */
	public static function fromReflector( $items ) {
		return array_map( function( $item ) {
			/** @var ConstantReflector $item */
			return new self( $item->getName(), $item->getLineNumber(), $item->getValue() );
		}, $items );
	}

	public function toArray() {
		return [ 'name' => $this->name, 'line' => $this->line, 'value' => $this->value ];
	}
}
