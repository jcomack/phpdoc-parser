<?php namespace WP_Parser\Importers;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Class ImportLogger
 * @package WP_Parser\Importers
 */
class ImportLogger implements LoggerAwareInterface {

	use LoggerAwareTrait;

	protected $stashed_errors = [];

	/**
	 * ImportLogger constructor.
	 */
	public function __construct() {
		$this->logger = new NullLogger();
	}

	/**
	 * Creates a generic info message from the passed message.
	 *
	 * @param string $message 	  The message to log.
	 * @param int 	 $indentation The amount of indentations to add.
	 *
	 * @return void
	 */
	public function info( $message, $indentation = 0 ) {
		$this->logger->info( $this->indent_text( $message, $indentation ) );
	}

	/**
	 * Creates a warning message from the passed message.
	 *
	 * @param string $message 	  The message to log.
	 * @param int 	 $indentation The amount of indentations to add.
	 *
	 * @return void
	 */
	public function warning( $message, $indentation = 0 ) {
		$this->logger->warning( $this->indent_text( $message, $indentation ) );
	}

	/**
	 * Creates an error message from the passed message.
	 *
	 * @param string $message 	  The message to log.
	 * @param int 	 $indentation The amount of indentations to add.
	 *
	 * @return void
	 */
	public function error( $message, $indentation = 0 ) {
		$this->logger->error( $this->indent_text( $message, $indentation ) );
	}

	/**
	 * Stashes the passed message to the error message stash for later retrieval.
	 *
	 * @param     $message	  	The message to log.
	 * @param int $indentation 	The amount of indentations to add.
	 *
	 * @return void
	 */
	public function stash_error( $message, $indentation = 0 ) {
		$this->stashed_errors[] =  $this->indent_text( $message, $indentation );
	}

	/**
	 * Outputs the stashed error messages.
	 *
	 * @return void
	 */
	public function output_stashed_errors() {
		foreach ( $this->stashed_errors as $stashed_error ) {
			$this->error( $stashed_error );
		}
	}

	/**
	 * Determines whether there are stashed errors or not.
	 *
	 * @return bool Whether or not there are stashed errors.
	 */
	public function has_stashed_errors() {
		return count( $this->stashed_errors ) > 0;
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
}
