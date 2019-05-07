<?php namespace WP_Parser\PluginParser;

/**
 * Class Textdomain
 * @package WP_Parser\PluginParser
 */
class Textdomain {
	/**
	 * @var string
	 */
	private $domain;
	/**
	 * @var string
	 */
	private $path;

	/**
	 * Textdomain constructor.
	 *
	 * @param string $domain The textdomain.
	 * @param string $path The path to the textdomain.
	 */
	public function __construct( string $domain, string $path ) {
		$this->domain = $domain;
		$this->path = $path;
	}

	/**
	 * @return string
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}


}
