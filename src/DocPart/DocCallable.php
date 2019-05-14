<?php namespace WP_Parser;

/**
 * Class DocCallable
 * @package WP_Parser
 */
class DocCallable {
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $namespace;
	/**
	 * @var string
	 */
	private $aliases;
	/**
	 * @var string
	 */
	private $line;
	/**
	 * @var string
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
	 * @param string $name
	 * @param string $namespace
	 * @param array  $aliases
	 * @param int    $line
	 * @param int    $end_line
	 * @param array  $arguments
	 * @param array  $doc
	 * @param array  $uses
	 * @param array  $hooks
	 */
	public function __construct( string $name, string $namespace, array $aliases, int $line, int $end_line, array $arguments, array $doc, array $uses = array(), array $hooks = array() ) {
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
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * @return string
	 */
	public function getAliases() {
		return $this->aliases;
	}

	/**
	 * @return string
	 */
	public function getLine() {
		return $this->line;
	}

	/**
	 * @return string
	 */
	public function getEndLine() {
		return $this->end_line;
	}

	/**
	 * @return array
	 */
	public function getArguments() {
		return $this->arguments;
	}

	/**
	 * @return array
	 */
	public function getDoc() {
		return $this->doc;
	}

	/**
	 * @return array
	 */
	public function getUses() {
		return $this->uses;
	}

	/**
	 * @return array
	 */
	public function getHooks() {
		return $this->hooks;
	}

	/**
	 * @param $callable
	 *
	 * @return DocCallable
	 */
	public static function fromReflector( $callable ) {
		$exporter = new Exporter();

		$uses = array();
		if ( ! empty( $callable->uses ) ) {
			$uses = $exporter->export_uses( $callable->uses );
		}

		$hooks = array();
		if ( ! empty( $callable->uses ) && ! empty( $callable->uses['hooks'] ) ) {
			$hooks = DocHookFactory::fromHooks( $callable->uses['hooks'] );
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
