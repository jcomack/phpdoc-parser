<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\Exception\UnparsableFile;
use phpDocumentor\Reflection\Exception\UnreadableFile;
use Symfony\Component\Finder\SplFileInfo;
use WP_Parser\DocCallableFactory;
use WP_Parser\DocClassFactory;
use WP_Parser\DocPartFactory;
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
	private $tags = [];

	/**
	 * @var array
	 */
	private $uses = [];

	/**
	 * @var array
	 */
	private $includes = [];

	/**
	 * @var array
	 */
	private $constants = [];

	/**
	 * @var array
	 */
	private $hooks = [];

	/**
	 * @var Exporter
	 */
	private $exporter;

	/**
	 * @var array
	 */
	private $functions = [];

	/**
	 * @var array
	 */
	private $classes = [];

	/**
	 * @var array
	 */
	private $docblock = [];

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
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getLongDescription(): string {
		return $this->longDescription;
	}

	/**
	 * @return array
	 */
	public function getTags(): array {
		return $this->tags;
	}

	/**
	 * @return array
	 */
	public function getUses(): array {
		return $this->uses;
	}

	/**
	 * @return array
	 */
	public function getIncludes(): array {
		return $this->includes;
	}

	/**
	 * @return array
	 */
	public function getConstants(): array {
		return $this->constants;
	}

	/**
	 * @return array
	 */
	public function getHooks(): array {
		return $this->hooks;
	}

	/**
	 * @return Exporter
	 */
	public function getExporter(): Exporter {
		return $this->exporter;
	}

	/**
	 * @return array
	 */
	public function getFunctions(): array {
		return $this->functions;
	}

	/**
	 * @return array
	 */
	public function getClasses(): array {
		return $this->classes;
	}

	/**
	 * @return array
	 */
	public function getDocblock(): array {
		return $this->docblock;
	}

	public function getRoot() {
		return $this->root;
	}

	public function getPath() {
		return $this->relativePath;
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
			$this->hooks = DocPartFactory::fromHooks( $file->uses['hooks'] );
		}

		$this->functions = DocPartFactory::fromFunctions( $file->getFunctions() );
		$this->classes 	 = DocPartFactory::fromClasses( $file->getClasses() );
	}
}
