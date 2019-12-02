<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\ClassReflector\MethodReflector;

/**
 * Class DocMethod
 * @package WP_Parser\DocPart
 */
class DocMethod implements DocPart {
	/**
	 * @var DocFunction
	 */
	private $function;

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
	 * @param DocFunction $function   The DocFunction instance to use for the basic data.
	 * @param bool        $final      Whether or not the method is considered final.
	 * @param bool        $abstract   Whether or not the method is abstract.
	 * @param bool        $static     Whether or not the method is static.
	 * @param string      $visibility The visibility of the method.
	 */
	public function __construct( DocFunction $function, bool $final, bool $abstract, bool $static, string $visibility ) {
		$this->function   = $function;
		$this->final      = $final;
		$this->abstract   = $abstract;
		$this->visibility = $visibility;
		$this->static     = $static;
	}

	/**
	 * Sets the method's name.
	 *
	 * @param string $name The name to set.
	 */
	public function setName( string $name ) {
		$this->function->setName( $name );
	}

	/**
	 * Gets the method's name.
	 *
	 * @return string The name.
	 */
	public function getName(): string {
		return $this->function->getName();
	}

	/**
	 * Gets the associated arguments.
	 *
	 * @return array The arguments.
	 */
	public function getArguments() {
		return $this->function->getArguments();
	}

	/**
	 * Gets the associated aliases.
	 *
	 * @return array The aliases.
	 */
	public function getAliases() {
		return $this->function->getAliases();
	}

	/**
	 * Gets starting line.
	 *
	 * @return int The starting line.
	 */
	public function getLine() {
		return $this->function->getLine();
	}

	/**
	 * Gets the ending line.
	 *
	 * @return int The ending line.
	 */
	public function getEndLine() {
		return $this->function->getEndLine();
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
		return $this->function->getHooks();
	}

	/**
	 * Gets the documentation.
	 *
	 * @return array The documentation of the method.
	 */
	public function getDocblock() {
		return $this->function->getDoc();
	}

	/**
	 * Gets the @ignored items.
	 *
	 * @return array The ignored items.
	 */
	public function getIgnored() {
		return $this->function->getIgnored();
	}

	/**
	 * Gets the namespace.
	 *
	 * @return string The namespace.
	 */
	public function getNamespace() {
		return $this->function->getNamespace();
	}

	/**
	 * Gets the tags.
	 *
	 * @return array The tags.
	 */
	public function getTags() {
		return $this->function->getTags();
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
			DocFunction::fromReflector( $method ),
			$method->isFinal(),
			$method->isAbstract(),
			$method->isStatic(),
			$method->getVisibility()
		);
	}
}
