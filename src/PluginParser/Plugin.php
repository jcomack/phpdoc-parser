<?php namespace WP_Parser\PluginParser;

/**
 * Class Plugin
 * @package WP_Parser\PluginParser
 */
class Plugin implements PluginInterface {
	/**
	 * @var string|string
	 */
	private $name;
	/**
	 * @var string|string
	 */
	private $uri;
	/**
	 * @var string|string
	 */
	private $version;
	/**
	 * @var string|string
	 */
	private $description;
	/**
	 * @var Author
	 */
	private $author;
	/**
	 * @var Textdomain
	 */
	private $textdomain;
	/**
	 * @var bool
	 */
	private $network;

	/**
	 * Plugin constructor.
	 *
	 * @param string $name
	 * @param string $uri
	 * @param string $version
	 * @param string $description
	 * @param Author $author
	 * @param Textdomain $textdomain
	 * @param bool   $network
	 */
	public function __construct( string $name, string $uri, string $version, string $description, Author $author, Textdomain $textdomain, $network = false ) {
		$this->name = $name;
		$this->uri = $uri;
		$this->version = $version;
		$this->description = $description;
		$this->author = $author;
		$this->textdomain = $textdomain;
		$this->network = $network;
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

	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return Author
	 */
	public function getAuthor() {
		return $this->author->getName();
	}

	/**
	 * @return Author
	 */
	public function getAuthorURI() {
		return $this->author->getUri();
	}

	/**
	 * @return Textdomain
	 */
	public function getTextdomain() {
		return $this->textdomain->getDomain();
	}

	/**
	 * @return Textdomain
	 */
	public function getTextdomainPath() {
		return $this->textdomain->getPath();
	}

	/**
	 * @return bool
	 */
	public function isNetwork() {
		return $this->network;
	}
}
