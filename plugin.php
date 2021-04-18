<?php
/**
 * Plugin Name: WP Parser
 * Description: Create a function reference site powered by WordPress
 * Author: Ryan McCue, Paul Gibbs, Andrey "Rarst" Savchenko and Contributors
 * Author URI: https://github.com/WordPress/phpdoc-parser/graphs/contributors
 * Plugin URI: https://github.com/WordPress/phpdoc-parser
 * Version:
 * Text Domain: wp-parser
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

global $wp_parser;

if ( ! defined( 'PHPDOC_PARSER_DIR' ) ) {
	define( 'PHPDOC_PARSER_DIR', dirname( __FILE__ ) );
}

$wp_parser = new WP_Parser\Parser();
$wp_parser->on_load();
