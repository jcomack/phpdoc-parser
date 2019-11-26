<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\ClassReflector\MethodReflector;
use WP_Parser\DocCallable;

/**
 * Class DocMethod
 * @package WP_Parser\DocPart
 */
class DocMethod extends BaseDocPart implements DocPart {
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
		parent::__construct( $callable->getName(), $callable->getNamespace(), $callable->getDoc() );

		$this->callable = $callable;
		$this->final = $final;
		$this->abstract = $abstract;
		$this->visibility = $visibility;
		$this->static = $static;
	}

	/**
	 * Sets the method's name.
	 *
	 * @param string $name The name to set.
	 */
	public function setName( string $name ) {
		$this->callable->setName( $name );
	}

	/**
	 * Gets the method's name.
	 *
	 * @return string The name.
	 */
	public function getName(): string {
		return $this->getCallable()->getName();
	}

	/**
	 * Gets the callable instance.
	 *
	 * @return DocCallable The callable instance.
	 */
	public function getCallable() {
		return $this->callable;
	}

	public function getArguments() {
		return $this->getCallable()->getArguments();
	}

	public function getAliases() {
		return $this->getCallable()->getAliases();
	}

	public function getLine() {
		return $this->getCallable()->getLine();
	}

	public function getEndLine() {
		return $this->getCallable()->getEndLine();
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
}
