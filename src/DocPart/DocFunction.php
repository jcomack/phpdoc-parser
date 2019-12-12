<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\ClassReflector\MethodReflector;
use WP_Parser\DocPartFactory;
use WP_Parser\Exporter;

/**
 * Class DocFunction
 * @package WP_Parser
 */
class DocFunction extends BaseDocPart implements DocPart {
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * @var array
	 */
	private $aliases;

	/**
	 * @var int
	 */
	private $line;

	/**
	 * @var int
	 */
	private $end_line;

	/**
	 * @var array
	 */
	private $arguments;

	/**
	 * @var array
	 */
	private $docblock;

	/**
	 * @var array
	 */
	private $uses;

	/**
	 * @var array
	 */
	private $hooks;

	/**
	 * DocFunction constructor.
	 *
	 * @param string $name      The name of the function.
	 * @param string $namespace The namespace of the function.
	 * @param array  $aliases   The aliases for the function.
	 * @param int    $line      The line on which the function's code starts.
	 * @param int    $end_line  The line on which the function's code ends.
	 * @param array  $arguments The arguments passed to the function.
	 * @param array  $docblock  The documentation of the function.
	 * @param array  $uses      Other functions that are used by this function.
	 * @param array  $hooks     The hooks available in the function.
	 */
	public function __construct( string $name, string $namespace, array $aliases, int $line, int $end_line, array $arguments, array $docblock, array $uses = [], array $hooks = [] ) {
		parent::__construct( $name, $namespace, $line, $end_line, $docblock, $aliases );

		$this->arguments = $arguments;
		$this->uses      = $uses;
		$this->hooks     = $hooks;
	}

	/**
	 * Sets the functions's name.
	 *
	 * @param string $name The name to set.
	 */
	public function setName( string $name ) {
		$this->name = $name;
	}

	/**
	 * Gets the arguments.
	 *
	 * @return array The arguments of the function.
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * Gets the uses.
	 *
	 * @return array The uses of the function.
	 */
	public function getUses() {
		return $this->uses;
	}

	/**
	 * Gets the hooks.
	 *
	 * @return array The hooks of the function.
	 */
	public function getHooks() {
		return $this->hooks;
	}

	/**
	 * Creates a function from the reflector.
	 *
	 * @param FunctionReflector|MethodReflector $callable The callable reflector to convert.
	 *
	 * @return DocFunction The function instance.
	 */
	public static function fromReflector( $callable ) {
		$exporter = new Exporter();

		$uses = [];
		if ( ! empty( $callable->uses ) ) {
			$uses = $exporter->export_uses( $callable->uses );
		}

		$hooks = [];
		if ( ! empty( $callable->uses ) && ! empty( $callable->uses['hooks'] ) ) {
			$hooks = DocPartFactory::fromHooks( $callable->uses['hooks'] );
		}

		return new self(
			$callable->getShortName(),
			$callable->getNamespace(),
			$callable->getNamespaceAliases(),
			$callable->getLineNumber(),
			$callable->getNode()->getAttribute( 'endLine' ),
			$exporter->export_arguments( $callable->getArguments() ),
			$exporter->export_docblock( $callable ),
			$uses,
			$hooks
		);
	}
}
