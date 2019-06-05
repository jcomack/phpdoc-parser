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
	 * @param string $path 	 The path to the textdomain.
	 */
	public function __construct( string $domain, string $path ) {
		$this->domain = $domain;
		$this->path = $path;
	}

	/**
	 * Gets the domain.
	 *
	 * @return string The domain.
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * Gets the path.
	 *
	 * @return string The path.
	 */
	public function getPath() {
		return $this->path;
	}


}
