<?php namespace WP_Parser;

use phpDocumentor\Reflection\ClassReflector\MethodReflector;
use WP_Parser\DocPart\DocPart;

/**
 * Class DocCallable
 * @package WP_Parser
 */
class DocCallable implements DocPart {
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
	private $doc;

	/**
	 * @var array
	 */
	private $uses;

	/**
	 * @var array
	 */
	private $hooks;

	/**
	 * DocCallable constructor.
	 *
	 * @param string $name		The name of the callable.
	 * @param string $namespace	The namespace of the callable.
	 * @param array  $aliases	The aliases for the callable.
	 * @param int    $line		The line on which the callable's code starts.
	 * @param int    $end_line	The line on which the calleble's code ends.
	 * @param array  $arguments	The arguments passed to the callable.
	 * @param array  $doc		The documentation of the callable.
	 * @param array  $uses		Other callables that are used by this callable.
	 * @param array  $hooks		The hooks available in the callable.
	 */
	public function __construct( string $name, string $namespace, array $aliases, int $line, int $end_line, array $arguments, array $doc, array $uses = [], array $hooks = [] ) {
		$this->name      = $name;
		$this->namespace = $namespace;
		$this->aliases   = $aliases;
		$this->line      = $line;
		$this->end_line  = $end_line;
		$this->arguments = $arguments;
		$this->doc       = $doc;
		$this->uses      = $uses;
		$this->hooks     = $hooks;
	}

	/**
	 * Gets the name.
	 *
	 * @return string The name of the callable.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Gets the namespace.
	 *
	 * @return string The namespace of the callable.
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * Gets the aliases.
	 *
	 * @return array The aliases of the callable.
	 */
	public function getAliases() {
		return $this->aliases;
	}

	/**
	 * Gets the line that the callable starts on.
	 *
	 * @return int The starting line of the callable.
	 */
	public function getLine() {
		return $this->line;
	}

	/**
	 * Gets the line that the callable ends on.
	 *
	 * @return int The ending line of the callable.
	 */
	public function getEndLine() {
		return $this->end_line;
	}

	/**
	 * Gets the arguments.
	 *
	 * @return array The arguments of the callable.
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * Gets the documentation.
	 *
	 * @return array The documentation of the callable.
	 */
	public function getDoc() {
		return $this->doc;
	}

	/**
	 * Gets the uses.
	 *
	 * @return array The uses of the callable.
	 */
	public function getUses() {
		return $this->uses;
	}

	/**
	 * Gets the hooks.
	 *
	 * @return array The hooks of the callable.
	 */
	public function getHooks() {
		return $this->hooks;
	}

	/**
	 * Creates a callable from the reflector.
	 *
	 * @param FunctionReflector|MethodReflector $callable The callable reflector to convert.
	 *
	 * @return DocCallable The callable instance.
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

	/**
	 * Converts the object to an array notation.
	 *
	 * @return array The array notation of the object.
	 */
	public function toArray() {
		return [
			'name' => $this->name,
			'namespace' => $this->namespace,
			'aliases' => $this->aliases,
			'line' => $this->line,
			'end_line' => $this->end_line,
			'arguments' => $this->arguments,
			'doc' => $this->doc,
			'uses' => $this->uses,
			'hooks' => array_map( function( $hook ) { return $hook->toArray(); }, $this->hooks ),
		];
	}
}
