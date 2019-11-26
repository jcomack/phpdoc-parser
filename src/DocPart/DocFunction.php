<?php namespace WP_Parser\DocPart;

use phpDocumentor\Reflection\FunctionReflector;
use WP_Parser\DocCallable;

/**
 * Class DocFunction
 * @package WP_Parser\DocPart
 */
class DocFunction extends BaseDocPart implements DocPart {

	/**
	 * @var DocCallable
	 */
	private $callable;

	/**
	 * DocFunction constructor.
	 *
	 * @param DocCallable $callable The DocCallable instance to use for the basic data.
	 */
	public function __construct( DocCallable $callable ) {
		$this->callable = $callable;
	}

	/**
	 * Gets the callable instance.
	 *
	 * @return DocCallable The callable instance.
	 */
	public function getCallable() {
		return $this->callable;
	}

	/**
	 * Gets the associated hooks.
	 *
	 * @return array The hooks.
	 */
	public function getHooks() {
		return $this->getCallable()->getHooks();
	}

	/**
	 * Gets the associated arguments.
	 *
	 * @return array The arguments.
	 */
	public function getArguments() {
		return $this->getCallable()->getArguments();
	}

	/**
	 * Gets the associated aliases.
	 *
	 * @return array The aliases.
	 */
	public function getAliases() {
		return $this->getCallable()->getAliases();
	}

	/**
	 * Gets starting line.
	 *
	 * @return int The starting line.
	 */
	public function getLine() {
		return $this->getCallable()->getLine();
	}

	/**
	 * Gets the ending line.
	 *
	 * @return int The ending line.
	 */
	public function getEndLine() {
		return $this->getCallable()->getEndLine();
	}

	/**
	 * Creates a function from the reflector.
	 *
	 * @param FunctionReflector $function The function reflector to convert.
	 *
	 * @return DocFunction The function instance.
	 */
	public static function fromReflector( $function ) {
		return new self( DocCallable::fromReflector( $function ) );
	}
}
