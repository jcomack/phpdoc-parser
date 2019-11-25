<?php namespace WP_Parser\DocPart;

use WP_Parser\DocCallableFactory;
use WP_Parser\DocPartFactory;
use WP_Parser\Exporter;

/**
 * Class DocClass
 * @package WP_Parser\DocPart
 */
class DocClass extends BaseDocPart implements DocPart {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * @var int
	 */
	private $line;

	/**
	 * @var int
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
	 * DocClass constructor.
	 *
	 * @param string $name			The name of the class.
	 * @param string $namespace		The namespace used by the class.
	 * @param int    $line			The line on which the class' code starts.
	 * @param int    $end_line		The line on which the class' code ends.
	 * @param bool   $final			Whether or not the class is final.
	 * @param bool   $abstract		Whether or not the class is abstract.
	 * @param string $extends		The class this class extends.
	 * @param array  $implements	The interfaces that the class implements.
	 * @param array  $properties	The properties of the class.
	 * @param array  $methods		The methods of the class.
	 * @param array  $doc			The documentation of the class.
	 */
	public function __construct( string $name, string $namespace, int $line, int $end_line, bool $final, bool $abstract, string $extends, array $implements, array $properties, array $methods, array $doc ) {
		parent::__construct( $name, $namespace, $doc );

		$this->line       = $line;
		$this->end_line   = $end_line;
		$this->final      = $final;
		$this->abstract   = $abstract;
		$this->extends    = $extends;
		$this->implements = $implements;
		$this->properties = $properties;
		$this->methods    = $methods;
	}

	/**
	 * Gets the line that the class starts on.
	 *
	 * @return int The starting line of the class.
	 */
	public function getLine() {
		return $this->line;
	}

	/**
	 * Gets the line that the class ends on.
	 *
	 * @return int The ending line of the class.
	 */
	public function getEndLine() {
		return $this->end_line;
	}

	/**
	 * Determines if the class is final.
	 *
	 * @return bool Whether or not the class is final.
	 */
	public function isFinal() {
		return $this->final;
	}

	/**
	 * Determines if the class is abstract.
	 *
	 * @return bool Whether or not the class is abstract.
	 */
	public function isAbstract() {
		return $this->abstract;
	}

	/**
	 * Gets the extended class.
	 *
	 * @return string The extended class name.
	 */
	public function getExtends() {
		return $this->extends;
	}

	/**
	 * Gets the implemented interfaces.
	 *
	 * @return array The implemented interfaces used in the class.
	 */
	public function getImplements() {
		return $this->implements;
	}

	/**
	 * Gets the properties.
	 *
	 * @return array The properties for the class.
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * Gets the available methods.
	 *
	 * @return array The available methods for the class.
	 */
	public function getMethods() {
		return $this->methods;
	}

	/**
	 * Gets a list of available method names.
	 *
	 * @return array The method names.
	 */
	public function getMethodNames() {
		return array_map( function( $method ) {
			return $method->getCallable()->getName();
		}, $this->methods );
	}

	/**
	 * Searches for a method within the class by the passed name.
	 *
	 * @param string $name The name of the method to search for.
	 *
	 * @return DocMethod The method.
	 */
	public function getMethodByName( $name ) {
		return array_shift( array_filter( $this->methods,
			function( $method ) use ( $name ) {
				if ( $method->getCallable()->getName() === $name ) {
					return $method;
				}

				return false;
			} )
		);
	}

	/**
	 * Creates a class from the reflector.
	 *
	 * @param ClassReflector $class The class reflector to convert.
	 *
	 * @return DocClass The class instance.
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
			DocPartFactory::fromMethods( $class->getMethods() ),
			$exporter->export_docblock( $class )
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
			'line' => $this->line,
			'end_line' => $this->end_line,
			'final' => $this->final,
			'abstract' => $this->abstract,
			'extends' => $this->extends,
			'implements' => $this->implements,
			'properties' => $this->properties,
			'methods' => array_map( function( $method ) { return $method->toArray(); }, $this->methods ),
			'doc' => $this->getDocblock(),
		];
	}
}
