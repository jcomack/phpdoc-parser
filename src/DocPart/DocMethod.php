<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\ClassReflector\MethodReflector;
use WP_Parser\DocCallable;

/**
 * Class DocMethod
 * @package WP_Parser\DocPart
 */
class DocMethod implements DocPart {
	/**
	 * @var DocCallable
	 */
	private $callable;

	/**
	 * @var bool
	 */
	private $final;

	/**
	 * @var bool
	 */
	private $abstract;

	/**
	 * @var string
	 */
	private $visibility;

	/**
	 * @var bool
	 */
	private $static;

	/**
	 * DocMethod constructor.
	 *
	 * @param DocCallable $callable   The DocCallable instance to use for the basic data.
	 * @param bool        $final	  Whether or not the method is considered final.
	 * @param bool        $abstract	  Whether or not the method is abstract.
	 * @param bool        $static	  Whether or not the method is static.
	 * @param string      $visibility The visibility of the method.
	 */
	public function __construct( DocCallable $callable, bool $final, bool $abstract, bool $static, string $visibility ) {
		$this->callable = $callable;
		$this->final = $final;
		$this->abstract = $abstract;
		$this->visibility = $visibility;
		$this->static = $static;
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
	 * Gets whether or not the method is final.
	 *
	 * @return bool Whether or not the method is final.
	 */
	public function isFinal() {
		return $this->final;
	}

	/**
	 * Gets whether or not the method is abstract.
	 *
	 * @return bool Whether or not the method is abstract.
	 */
	public function isAbstract() {
		return $this->abstract;
	}

	/**
	 * Gets the visibility.
	 *
	 * @return string The visibility of the method.
	 */
	public function getVisibility() {
		return $this->visibility;
	}

	/**
	 * Gets whether or not the method is static.
	 *
	 * @return bool Whether or not the method is static.
	 */
	public function isStatic() {
		return $this->static;
	}

	/**
	 * Gets the hooks associated with the method.
	 *
	 * @return array The hooks associated with the method.
	 */
	public function getHooks() {
		return $this->callable->getHooks();
	}

	/**
	 * Creates a method from the reflector.
	 *
	 * @param MethodReflector $method The method reflector to convert.
	 *
	 * @return DocMethod The method instance.
	 */
	public static function fromReflector( $method ) {
		return new self(
			DocCallable::fromReflector( $method ),
			$method->isFinal(),
			$method->isAbstract(),
			$method->isStatic(),
			$method->getVisibility()
		);
	}

	/**
	 * Converts the object to an array notation.
	 *
	 * @return array The array notation of the object.
	 */
	public function toArray() {
		return array_merge(
			$this->callable->toArray(),
			array(
				'final' 	 => $this->final,
				'abstract' 	 => $this->abstract,
				'static' 	 => $this->static,
				'visibility' => $this->visibility,
			)
		);
	}
}
