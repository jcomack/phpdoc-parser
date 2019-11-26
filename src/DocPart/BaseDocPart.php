<?php namespace WP_Parser\DocPart;

/**
 * Class BaseDocPart
 * @package WP_Parser\DocPart
 */
abstract class BaseDocPart {

	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var array
	 */
	private $docblock;
	/**
	 * @var string
	 */
	private $namespace;

	public function __construct( string $name, string $namespace, array $docblock ) {
		$this->name = $name;
		$this->docblock = $docblock;
		$this->namespace = $namespace;
	}

	/**
	 * Gets the name.
	 *
	 * @return string The name of the part.
	 */
	public function getName(): string {
		return $this->name;
	}

	public function getNamespace() {
		return $this->namespace;
	}

	public function getAliases() {
		return [];
	}

	/**
	 * @return array
	 */
	public function getTags(): array {
		return $this->getDocblock()['tags'];
	}

	public function getIgnored(): array {
		return $this->getTagsData( 'ignore' );
	}

	public function getSince(): array {
		return $this->getTagsData( 'since' );
	}

	/**
	 * Gets the docblock.
	 *
	 * @return array The docblock for the part.
	 */
	public function getDocblock(): array {
		return $this->docblock;
	}

	/**
	 * Gets the tag's data based on the passed name.
	 *
	 * @param string $name The name of the tag to retrieve.
	 *
	 * @return array The tags data. Returns an empty array if none could be found.
	 */
	protected function getTagsData( string $name ): array {
		return wp_list_filter( $this->getTags(), [ 'name' => $name ] );
	}
}
