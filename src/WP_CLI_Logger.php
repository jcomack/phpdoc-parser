<?php
namespace WP_Parser;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * PSR-3 logger for WP CLI.
 */
class WP_CLI_Logger extends AbstractLogger {

	/**
	 * Logs messages to be sent to the CLI.
	 *
	 * @param string $level		The message level.
	 * @param string $message	The message.
	 * @param array  $context	The context of the message.
	 *
	 * @return void
	 */
	public function log( $level, $message, array $context = array() ) {

		switch ( $level ) {

			case LogLevel::WARNING:
				\WP_CLI::warning( $message );
				break;

			case LogLevel::ERROR:
			case LogLevel::ALERT:
			case LogLevel::EMERGENCY:
			case LogLevel::CRITICAL:
				\WP_CLI::error( $message );
				break;

			default:
				\WP_CLI::log( $message );
		}
	}
}
