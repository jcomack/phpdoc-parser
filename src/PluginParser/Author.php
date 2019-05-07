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
	 * @param string $name
	 * @param string $uri
	 */
	public function __construct( string $name, string $uri ) {
		$this->name = $name;
		$this->uri = $uri;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getUri() {
		return $this->uri;
	}




}
