<?php namespace WP_Parser;

use WP_Parser\DocPart\DocHook;

/**
 * Class DocHookFactory
 * @package WP_Parser
 */
class DocHookFactory {

	/**
	 * Converts hooks into DocHooks.
	 *
	 * @param array $hooks The hooks to be converted into DocHooks.
	 *
	 * @return array The DocHook objects.
	 */
	public static function fromHooks( $hooks ) {
		return array_map( function( $hook ) {
			return DocHook::fromReflector( $hook );
		}, $hooks );
	}
}
