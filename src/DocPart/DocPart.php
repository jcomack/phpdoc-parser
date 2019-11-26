<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\BaseReflector;

/**
 * Interface DocPart
 * @package WP_Parser\DocPart
 */
interface DocPart {

	/**
	 * Creates a DocPart-compatible instance from the reflector.
	 *
	 * @param BaseReflector $reflector The reflector to convert.
	 *
	 * @return DocPart The instance.
	 */
	public static function fromReflector( $reflector );
}
