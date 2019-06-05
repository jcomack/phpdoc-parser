<?php namespace WP_Parser\PluginParser;

/**
 * Class Author
 * @package WP_Parser\PluginParser
 */
class Author {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $uri;

	/**
	 * Author constructor.
	 *
	 * @param string $name The name of the author.
	 * @param string $uri  The URI of the author.
	 */
	public function __construct( string $name, string $uri ) {
		$this->name = $name;
		$this->uri = $uri;
	}

	/**
	 * Gets the name of the author.
	 *
	 * @return string The name of the author.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Gets the URI of the author.
	 *
	 * @return string The URI of the author.
	 */
	public function getUri() {
		return $this->uri;
	}




}
