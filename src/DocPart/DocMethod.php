<?php namespace WP_Parser\DocPart;

use WP_Parser\DocCallable;
use WP_Parser\Exporter;

/**
 * Class DocMethod
 * @package WP_Parser\DocPart
 */
class DocMethod {
	/**
	 * @var DocCallable
	 */
	private $callable;
	/**
	 * @var bool|bool
	 */
	private $final;
	/**
	 * @var bool|bool
	 */
	private $abstract;
	private $visibility;
	/**
	 * @var bool
	 */
	private $static;

	/**
	 * DocMethod constructor.
	 *
	 * @param DocCallable $callable
	 * @param bool        $final
	 * @param bool        $abstract
	 * @param bool        $static
	 * @param             $visibility
	 */
	public function __construct( DocCallable $callable, bool $final, bool $abstract, bool $static, $visibility ) {
		$this->callable = $callable;
		$this->final = $final;
		$this->abstract = $abstract;
		$this->visibility = $visibility;
		$this->static = $static;
	}

	/**
	 * @return DocCallable
	 */
	public function getCallable() {
		return $this->callable;
	}

	/**
	 * @return bool
	 */
	public function isFinal() {
		return $this->final;
	}

	/**
	 * @return bool
	 */
	public function isAbstract() {
		return $this->abstract;
	}

	/**
	 * @return mixed
	 */
	public function getVisibility() {
		return $this->visibility;
	}

	/**
	 * @return bool
	 */
	public function isStatic() {
		return $this->static;
	}

	public static function fromReflector( $method ) {
		return new self(
			DocCallable::fromReflector( $method ),
			$method->isFinal(),
			$method->isAbstract(),
			$method->isStatic(),
			$method->getVisibility()
		);
	}

	public function toArray() {
		return array_merge(
			$this->callable->toArray(),
			[
				'final' 	 => $this->final,
				'abstract' 	 => $this->abstract,
				'static' 	 => $this->static,
				'visibility' => $this->visibility,
			]
		);
	}
}
