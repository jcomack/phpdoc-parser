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
	 * @var string
	 */
	private $parent = '';

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
	 * Sets the method's parent.
	 *
	 * @param string $parent The parent to set.
	 */
	public function setParent( string $parent ) {
		$this->parent = $parent;
	}

	/**
	 * Gets the method's name.
	 *
	 * @return string The name.
	 */
	public function getName(): string {
		if ( $this->parent === '' ) {
			return $this->function->getName();
		}

		return $this->parent . '::' . $this->function->getName();
	}

	/**
	 * Gets the associated arguments.
	 *
	 * @return array The arguments.
	 */
	public function getArguments(): array  {
		return $this->function->getArguments();
	}

	/**
	 * Gets the associated aliases.
	 *
	 * @return array The aliases.
	 */
	public function getAliases(): array {
		return $this->function->getAliases();
	}

	/**
	 * Gets starting line.
	 *
	 * @return int The starting line.
	 */
	public function getLine(): int {
		return $this->function->getLine();
	}

	/**
	 * Gets the ending line.
	 *
	 * @return int The ending line.
	 */
	public function getEndLine(): int {
		return $this->function->getEndLine();
	}

	/**
	 * Gets whether or not the method is final.
	 *
	 * @return bool Whether or not the method is final.
	 */
	public function isFinal(): bool {
		return $this->final;
	}

	/**
	 * Gets whether or not the method is abstract.
	 *
	 * @return bool Whether or not the method is abstract.
	 */
	public function isAbstract(): bool {
		return $this->abstract;
	}

	/**
	 * Gets the visibility.
	 *
	 * @return string The visibility of the method.
	 */
	public function getVisibility(): string {
		return $this->visibility;
	}

	/**
	 * Gets whether or not the method is static.
	 *
	 * @return bool Whether or not the method is static.
	 */
	public function isStatic(): bool {
		return $this->static;
	}

	/**
	 * Gets the hooks associated with the method.
	 *
	 * @return array The hooks associated with the method.
	 */
	public function getHooks(): array {
		return $this->function->getHooks();
	}

	/**
	 * @inheritDoc
	 */
	public function getDocblock(): array {
		return $this->function->getDocblock();
	}

	/**
	 * Gets the @ignored items.
	 *
	 * @return array The ignored items.
	 */
	public function getIgnored(): array {
		return $this->function->getIgnored();
	}

	/**
	 * Gets the namespace.
	 *
	 * @return string The namespace.
	 */
	public function getNamespace(): string {
		return $this->function->getNamespace();
	}

	/**
	 * @inheritDoc
	 */
	public function getTags(): array {
		return $this->function->getTags();
	}

	/**
	 * @inheritDoc
	 */
	public function getSince(): array {
		return $this->function->getSince();
	}

	/**
	 * @inheritDoc
	 */
	public function getFirstAppearance(): string {
		return $this->function->getFirstAppearance();
	}

	/**
	 * Creates a method from the reflector.
	 *
	 * @param MethodReflector $method The method reflector to convert.
	 *
	 * @return DocMethod The method instance.
	 */
	public static function fromReflector( $method ): DocMethod {
		return new self(
			DocFunction::fromReflector( $method ),
			$method->isFinal(),
			$method->isAbstract(),
			$method->isStatic(),
			$method->getVisibility()
		);
	}
}
