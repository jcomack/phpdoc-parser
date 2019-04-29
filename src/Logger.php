<?php namespace WP_Parser;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class Logger
 * @package WP_Parser
 */
class Logger implements LoggerAwareInterface {

	use LoggerAwareTrait;

	protected $errors = [];

	public function __construct( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Indents the passed text with the amount of tabs specified.
	 *
	 * @param string $text    The text to indent.
	 * @param int    $indents The amount of indentations to add.
	 *
	 * @return string The indented text.
	 */
	private function indent_text( $text, $indents ) {
		$indents_to_add = str_repeat( "\t", $indents );

		return $indents_to_add . $text;
	}

	/**
	 * Sends the passed message to the logger as a generic info message.
	 *
	 * @param string $message The message to log.
	 * @param int 	 $indents The amount of indentations to add.
	 *
	 * @return void
	 */
	public function info( $message, $indents = 0 ) {
		$this->logger->info( $this->indent_text( $message, $indents ) );
	}

	/**
	 * Sends the passed message to the logger as a warning.
	 *
	 * @param string $message The message to log as a warning.
	 * @param int 	 $indents The amount of indentations to add.
	 *
	 * @return void
	 */
	public function warning( $message, $indents = 0 ) {
		$this->logger->warning( $this->indent_text( $message, $indents ) );
	}

	/**
	 * Sends the passed message to the logger as an error.
	 *
	 * @param string $message The message to log as a warning.
	 *
	 * @return void
	 */
	public function error( $message ) {
		$this->logger->error( $message );
	}

	/**
	 * Adds the passed message to the error log.
	 *
	 * @param string $message The message to add to the error log.
	 *
	 * @return void
	 */
	public function log_error( $message ) {
		$this->errors[] = $message;
	}

	public function notice( $message ) {
		$this->logger->notice( $message );
	}

	public function skipped( $type, $namespace, $indent = 1 ) {
		$this->info( sprintf( 'Skipped importing @ignore-d %1$s "%2$s"', $type, $namespace ), $indent );
	}

	public function insertion_error( $type, $namespace ) {
		$this->error( sprintf( 'Problem inserting post for %1$s "%2$s"', $type, $namespace ) );
	}

	public function update_error( $type, $namespace ) {
		$this->error( sprintf( 'Problem updating post for %1$s "%2$s"', $type, $namespace ) );
	}

	public function insertion_success( $type, $namespace ) {
		$this->info( sprintf( 'Inserted %1$s "%2$s"', $type, $namespace ) );
	}

	public function update_success( $type, $namespace ) {
		$this->info( sprintf( 'Updated %1$s "%2$s"', $type, $namespace ) );
	}

	public function output_errors() {
		foreach ( $this->errors as $error ) {
			$this->error( $error );
		}
	}
}
