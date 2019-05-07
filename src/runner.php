<?php namespace WP_Parser;

use phpDocumentor\Reflection\ClassReflector;
use phpDocumentor\Reflection\Exception\UnparsableFile;
use phpDocumentor\Reflection\Exception\UnreadableFile;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use WP_CLI;
use WP_Parser\PluginParser\PluginInterface;

/**
 * Class Runner
 *
 * @package WP_Parser
 */
class Runner {
	private $exporter;
	private $plugin;
	private $directory;

	/**
	 * Runner constructor.
	 *
	 * @param                 $directory
	 * @param PluginInterface $plugin
	 */
	public function __construct( $directory, PluginInterface $plugin ) {
		$this->exporter = new Exporter();

		$this->directory = $directory;
		$this->plugin = $plugin;
	}

	/**
	 * Parses the passed files and attempts to extract the proper docblocks from the files.
	 *
	 * @param Finder $files The files to extract the docblocks from.
	 *
	 * @return array The extracted docblocks.
	 *
	 * @throws UnparsableFile Thrown if the file can't be parsed.
	 * @throws UnreadableFile Thrown if the file can't be read.
	 */
	public function parse_files( Finder $files ) {

		$output = array();
		$root = $this->directory;

		$factory = DocFileFactory::fromFiles( $files, $this->directory, $this->plugin );

		$array_format = array_map( function( $file ) { return $file->toArray(); }, $factory );

		/** @var SplFileInfo $fileInfo */
		foreach ( $files as $fileInfo ) {

			$filename = $fileInfo->getPathname();
			$file 	  = new File_Reflector( $filename );
			$path 	  = ltrim( substr( $filename, strlen( $root ) ), DIRECTORY_SEPARATOR );


//						var_dump(
//							$fileInfo,
//							$path,
//							str_replace( DIRECTORY_SEPARATOR, '/', $file->getFilename() )
//						);die;


			$file->setFilename( $path );
			$file->process();

			$out = array(
				'file' => $this->exporter->export_docblock( $file ),
				'path' => str_replace( DIRECTORY_SEPARATOR, '/', $file->getFilename() ),
				'root' => $root,
			);

			if ( ! empty( $file->uses ) ) {
				$out['uses'] = $this->exporter->export_uses( $file->uses );
			}

			if ( ! empty( $this->plugin_data ) ) {
				$out['plugin'] = $this->plugin_data['Name'];
			}

			foreach ( $file->getIncludes() as $include ) {
				$include_data = array(
					'name' => $include->getName(),
					'line' => $include->getLineNumber(),
					'type' => $include->getType(),
				);

				$out['includes'][] = $include_data;
			}

			foreach ( $file->getConstants() as $constant ) {
				$constant_data = array(
					'name'  => $constant->getShortName(),
					'line'  => $constant->getLineNumber(),
					'value' => $constant->getValue(),
				);

				$out['constants'][] = $constant_data;
			}

			if ( ! empty( $file->uses['hooks'] ) ) {
				$out['hooks'] = $this->exporter->export_hooks( $file->uses['hooks'] );
			}

			foreach ( $file->getFunctions() as $function ) {
				$func = array(
					'name'      => $function->getShortName(),
					'namespace' => $function->getNamespace(),
					'aliases'   => $function->getNamespaceAliases(),
					'line'      => $function->getLineNumber(),
					'end_line'  => $function->getNode()->getAttribute( 'endLine' ),
					'arguments' => $this->exporter->export_arguments( $function->getArguments() ),
					'doc'       => $this->exporter->export_docblock( $function ),
					'hooks'     => array(),
				);

				if ( ! empty( $function->uses ) ) {
					$func['uses'] = $this->exporter->export_uses( $function->uses );

					if ( ! empty( $function->uses['hooks'] ) ) {
						$func['hooks'] = $this->exporter->export_hooks( $function->uses['hooks'] );
					}
				}

				$out['functions'][] = $func;
			}

			/** @var ClassReflector $class */
			foreach ( $file->getClasses() as $class ) {
				$class_data = array(
					'name'       => $class->getShortName(),
					'namespace'  => $class->getNamespace(),
					'line'       => $class->getLineNumber(),
					'end_line'   => $class->getNode()->getAttribute( 'endLine' ),
					'final'      => $class->isFinal(),
					'abstract'   => $class->isAbstract(),
					'extends'    => $class->getParentClass(),
					'implements' => $class->getInterfaces(),
					'properties' => $this->exporter->export_properties( $class->getProperties() ),
					'methods'    => $this->exporter->export_methods( $class->getMethods() ),
					'doc'        => $this->exporter->export_docblock( $class ),
				);

				$out['classes'][] = $class_data;
			}

			$output[] = $out;
		}


//		var_dump(array_values($output));die;

		$fp = fopen('old.json', 'w' );
		fwrite($fp, json_encode( array_slice( $output, 0, 10 ) ) );
		fclose($fp);

		$fp = fopen('new.json', 'w' );
		fwrite($fp, json_encode( array_slice( $array_format, 0, 10 ) ) );
		fclose($fp);

		die;


		return $output;
	}
}
