<?php namespace WP_Parser;

use WP_Parser\DocPart\DocFunction;
use WP_Parser\DocPart\DocMethod;

/**
 * Class DocCallableFactory
 * @package WP_Parser
 */
class DocCallableFactory {

	public static function fromFunctions( $functions ) {
		return array_values( array_map( function( $function ) {
			return DocFunction::fromReflector( $function );
		}, $functions ) );
	}

	public static function fromMethods( $methods ) {
		return array_values( array_map( function( $method ) {
			return DocMethod::fromReflector( $method );
		}, $methods ) );
	}
}
