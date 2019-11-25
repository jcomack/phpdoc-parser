<?php namespace WP_Parser;

use InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Tightenco\Collect\Support\Collection;
use WP_Parser\DocPart\DocClass;
use WP_Parser\DocPart\DocConstant;
use WP_Parser\DocPart\DocFile;
use WP_Parser\DocPart\DocFunction;
use WP_Parser\DocPart\DocHook;
use WP_Parser\DocPart\DocMethod;
use WP_Parser\PluginParser\PluginInterface;

/**
 * Class DocPartFactory
 * @package WP_Parser
 */
class DocPartFactory {

	/**
	 * @var array
	 */
	private static $valid_part_types = [
		DocClass::class,
		DocConstant::class,
		DocHook::class,
		DocFunction::class,
		DocMethod::class,
	];

	/**
	 * Converts files to their DocFile counterparts.
	 *
	 * @param Finder          $files			The files to convert.
	 * @param string          $root_directory	The root directory of the files.
	 * @param PluginInterface $plugin			The plugin associated with the files.
	 *
	 * @return Collection The converted files.
	 */
	public static function fromFiles( Finder $files, string $root_directory, PluginInterface $plugin ) {
		return new Collection( array_map( function( $file ) use( $root_directory, $plugin ) {
			return new DocFile( $file, $root_directory, $plugin );
		}, iterator_to_array( $files ) ) );
	}

	/**
	 * Converts classes into DocClass.
	 *
	 * @param array $classes The classes to be converted into DocClasses.
	 *
	 * @return array The DocClass objects.
	 */
	public static function fromClasses( $classes ) {
		return self::convertToType( $classes, DocClass::class );
	}

	/**
	 * Converts constants into DocConstants.
	 *
	 * @param array $constants The constants to be converted into DocConstants.
	 *
	 * @return array The DocConstant objects.
	 */
	public static function fromConstants( $constants ) {
		return self::convertToType( $constants, DocHook::class );
	}

	/**
	 * Converts functions into DocFunctions.
	 *
	 * @param array $functions The functions to be converted into DocFunctions.
	 *
	 * @return array The DocFunctions objects.
	 */
	public static function fromFunctions( $functions ) {
		return self::convertToType( $functions, DocFunction::class, true );
	}

	/**
	 * Converts methods into DocFunctions.
	 *
	 * @param array $methods The methods to be converted into DocFunctions.
	 *
	 * @return array The DocFunctions objects.
	 */
	public static function fromMethods( $methods ) {
		return self::convertToType( $methods, DocMethod::class, true );
	}

	/**
	 * Converts hooks into DocHooks.
	 *
	 * @param array $hooks The hooks to be converted into DocHooks.
	 *
	 * @return array The DocHook objects.
	 */
	public static function fromHooks( $hooks ) {
		return self::convertToType( $hooks, DocHook::class );
	}

	/**
	 * Determines whether the passed type is of a valid DocPart type.
	 *
	 * @param string $type The type to validate.
	 *
	 * @return bool Whether or not the type is valid.
	 */
	private static function is_valid_part_type( string $type ) {
		return count( array_filter( self::$valid_part_types, function( $valid_part ) use ( $type ) {
			return $type === $valid_part;
		} ) ) > 0;
	}

	/**
	 * Converts an array of objects to their DocPart counterparts.
	 *
	 * @param array  $data		  The objects to convert.
	 * @param string $type		  The type to convert to.
	 * @param bool   $values_only Whether we only want to return the values and not the keys.
	 *
	 * @return array The array of converted types.
	 */
	protected static function convertToType( array $data, string $type, bool $values_only = false ) {
		if ( ! self::is_valid_part_type( $type ) ) {
			throw new InvalidArgumentException( sprintf( "Cannot convert to type %s as it isn't a valid DocPart type.", (string) $type ) );
		}

		$converted = array_map( function( $item ) use ( $type ) {
			return $type::fromReflector( $item );
		}, $data );

		if ( $values_only ) {
			return array_values( $converted );
		}

		return $converted;
	}
}
