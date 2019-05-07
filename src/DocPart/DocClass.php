<?php namespace WP_Parser\DocPart;

use WP_Parser\DocCallableFactory;
use WP_Parser\Exporter;

/**
 * Class DocClass
 * @package WP_Parser\DocPart
 */
class DocClass {

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
	private $line;
	/**
	 * @var string
	 */
	private $end_line;
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
	private $extends;
	/**
	 * @var array
	 */
	private $implements;
	/**
	 * @var array
	 */
	private $properties;
	/**
	 * @var array
	 */
	private $methods;
	/**
	 * @var array
	 */
	private $doc;

	/**
	 * DocClass constructor.
	 *
	 * @param string $name
	 * @param string $namespace
	 * @param int $line
	 * @param int $end_line
	 * @param bool   $final
	 * @param bool   $abstract
	 * @param string $extends
	 * @param array  $implements
	 * @param array  $properties
	 * @param array  $methods
	 * @param array  $doc
	 */
	public function __construct( string $name, string $namespace, int $line, int $end_line, bool $final, bool $abstract, string $extends, array $implements, array $properties, array $methods, array $doc ) {

		$this->name = $name;
		$this->namespace = $namespace;
		$this->line = $line;
		$this->end_line = $end_line;
		$this->final = $final;
		$this->abstract = $abstract;
		$this->extends = $extends;
		$this->implements = $implements;
		$this->properties = $properties;
		$this->methods = $methods;
		$this->doc = $doc;
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
	 * @return bool
	 */
	public function isFinal() {
		return $this->final;
	}

	/**
	 * @return bool
	 */
	public function isAbstract() {
		return $this->abstract;
	}

	/**
	 * @return string
	 */
	public function getExtends() {
		return $this->extends;
	}

	/**
	 * @return array
	 */
	public function getImplements() {
		return $this->implements;
	}

	/**
	 * @return array
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * @return array
	 */
	public function getMethods() {
		return $this->methods;
	}

	/**
	 * @return array
	 */
	public function getDoc() {
		return $this->doc;
	}

	/**
	 * @param ClassReflector $class
	 *
	 * @return DocClass
	 */
	public static function fromReflector( $class ) {

		$exporter = new Exporter();

		return new self(
			$class->getShortName(),
			$class->getNamespace(),
			$class->getLineNumber(),
			$class->getNode()->getAttribute( 'endLine' ),
			$class->isFinal(),
			$class->isAbstract(),
			$class->getParentClass(),
			$class->getInterfaces(),
			$exporter->export_properties( $class->getProperties() ),
			DocCallableFactory::fromMethods( $class->getMethods() ),
			$exporter->export_docblock( $class )
		);
	}

	public function toArray() {
		return [
			'name' => $this->name,
			'namespace' => $this->namespace,
			'line' => $this->line,
			'end_line' => $this->end_line,
			'final' => $this->final,
			'abstract' => $this->abstract,
			'extends' => $this->extends,
			'implements' => $this->implements,
			'properties' => $this->properties,
			'methods' => array_map( function( $method ) { return $method->toArray(); }, $this->methods ),
			'doc' => $this->doc,
		];
	}
}
