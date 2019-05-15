<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\FunctionReflector;
use WP_Parser\DocCallable;

/**
 * Class DocFunction
 * @package WP_Parser\DocPart
 */
class DocFunction implements DocPart {

	/**
	 * @var DocCallable
	 */
	private $callable;

	/**
	 * DocFunction constructor.
	 *
	 * @param DocCallable $callable The DocCallable instance to use for the basic data.
	 */
	public function __construct( DocCallable $callable ) {
		$this->callable = $callable;
	}

	/**
	 * Gets the callable instance.
	 *
	 * @return DocCallable The callable instance.
	 */
	public function getCallable() {
		return $this->callable;
	}

	/**
	 * Creates a function from the reflector.
	 *
	 * @param FunctionReflector $function The function reflector to convert.
	 *
	 * @return DocFunction The function instance.
	 */
	public static function fromReflector( $function ) {
		return new self( DocCallable::fromReflector( $function ) );
	}

	/**
	 * Converts the object to an array notation.
	 *
	 * @return array The array notation of the object.
	 */
	public function toArray() {
		return $this->callable->toArray();
	}
}
