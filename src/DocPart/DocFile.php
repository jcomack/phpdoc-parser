<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\Exception\UnparsableFile;
use phpDocumentor\Reflection\Exception\UnreadableFile;
use Symfony\Component\Finder\SplFileInfo;
use WP_Parser\DocCallableFactory;
use WP_Parser\DocClassFactory;
use WP_Parser\DocHookFactory;
use WP_Parser\Exporter;
use WP_Parser\File_Reflector;
use WP_Parser\PluginParser\PluginInterface;

/**
 * Class DocFile
 * @package WP_Parser\DocPart
 */
class DocFile extends DocAbstract {
	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var string
	 */
	private $longDescription;

	/**
	 * @var array
	 */
	private $tags = array();

	/**
	 * @var array
	 */
	private $uses = array();

	/**
	 * @var array
	 */
	private $includes = array();

	/**
	 * @var array
	 */
	private $constants = array();

	/**
	 * @var array
	 */
	private $hooks = array();

	/**
	 * @var Exporter
	 */
	private $exporter;

	/**
	 * @var array
	 */
	private $functions;

	/**
	 * @var array
	 */
	private $classes;
	/**
	 * @var array
	 */
	private $docblock;

	/**
	 * DocFile constructor.
	 *
	 * @param SplFileInfo     $file	  The file to parse.
	 * @param string      	  $root	  The root of the file.
	 * @param PluginInterface $plugin The plugin data collector.
	 *
	 * @throws UnparsableFile
	 * @throws UnreadableFile
	 */
	public function __construct( SplFileInfo $file, $root, PluginInterface $plugin ) {
		parent::__construct( $file, $root, $plugin );

		$this->exporter = new Exporter();

		$this->parse();
	}

	/**
	 * Parses the file.
	 *
	 * @return void
	 *
	 * @throws UnparsableFile
	 * @throws UnreadableFile
	 */
	protected function parse() {
		$file = new File_Reflector( $this->fullPath );
		$file->setFilename( $this->relativePath );
		$file->process();

		$docblock = $this->exporter->export_docblock( $file );

		$this->description 	   = $docblock['description'];
		$this->longDescription = $docblock['long_description'];
		$this->tags 		   = $docblock['tags'];
		$this->docblock 	   = $docblock;

		if ( ! empty( $file->uses ) ) {
			$this->uses = $this->exporter->export_uses( $file->uses );
		}

		$this->includes  = DocInclude::fromReflector( $file->getIncludes() );
		$this->constants = DocConstant::fromReflector( $file->getConstants() );

		if ( ! empty( $file->uses ) && ! empty( $file->uses['hooks'] ) ) {
			$this->hooks = DocHookFactory::fromHooks( $file->uses['hooks'] );
		}

		$this->functions = DocCallableFactory::fromFunctions( $file->getFunctions() );
		$this->classes 	 = DocClassFactory::fromClasses( $file->getClasses() );
	}

	/**
	 * Converts DocFile to an array.
	 *
	 * @return array The array version of the object.
	 */
	public function toArray() {
		$out = array(
			'file'   => $this->docblock,
			'path'   => $this->relativePath,
			'root'   => $this->root,
			'plugin' => $this->plugin->getName(),
		);

		if ( ! empty( $this->uses ) ) {
			$out['uses']  = $this->uses;
		}

		$out['includes']  = array_map( function( $include ) { return $include->toArray(); }, $this->includes );
		$out['constants'] = array_map( function( $constant ) { return $constant->toArray(); }, $this->constants );

		if ( ! empty( $this->hooks ) ) {
			$out['hooks'] = array_map( function( $hook ) { return $hook->toArray(); }, $this->hooks );
		}

		$out['functions'] = array_map( function( $function ) { return $function->toArray(); }, $this->functions );
		$out['classes']   = array_map( function( $class ) { return $class->toArray(); }, $this->classes );

		return $out;
	}
}
