<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\IncludeReflector;

/**
 * Class DocInclude
 * @package WP_Parser\DocPart
 */
class DocInclude {
	private $name;
	private $line_number;
	private $type;

	public function __construct( $name, $line_number, $type ) {
		$this->name = $name;
		$this->line_number = $line_number;
		$this->type = $type;
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
	public function getLineNumber() {
		return $this->line_number;
	}

	/**
	 * @return mixed
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param IncludeReflector[] $items
	 *
	 * @return array
	 */
	public static function fromReflector( $items ) {
		return array_map( function( $item ) {
			/** @var IncludeReflector $item */
			return new self( $item->getName(), $item->getLineNumber(), $item->getType() );
		}, $items );
	}

	public function toArray() {
		return [ 'name' => $this->name, 'line' => $this->line_number, 'type' => $this->type ];
	}
}
