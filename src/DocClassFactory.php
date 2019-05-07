<?php namespace WP_Parser;

use WP_Parser\DocPart\DocClass;

/**
 * Class DocClassFactory
 * @package WP_Parser
 */
class DocClassFactory {

	public static function fromClasses( $classes ) {
		return array_map( function( $class ) {
			return DocClass::fromReflector( $class );
		}, $classes );
	}
}
