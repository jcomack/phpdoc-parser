<?php namespace WP_Parser\DocPart;

use WP_Parser\DocCallable;

/**
 * Class DocFunction
 * @package WP_Parser\DocPart
 */
class DocFunction {
	/**
	 * @var DocCallable
	 */
	private $callable;

	/**
	 * DocFunction constructor.
	 *
	 * @param DocCallable $callable
	 */
	public function __construct( DocCallable $callable ) {
		$this->callable = $callable;
	}

	/**
	 * @return DocCallable
	 */
	public function getCallable() {
		return $this->callable;
	}

	public static function fromReflector( $function ) {
		return new self( DocCallable::fromReflector( $function ) );
	}

	public function toArray() {
		return $this->callable->toArray();
	}
}
