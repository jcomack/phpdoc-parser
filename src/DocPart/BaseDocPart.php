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
	 * @var string
	 */
	private $namespace;
	/**
	 * @var array
	 */
	private $docblock;
	/**
	 * @var array
	 */
	private $aliases;
	/**
	 * @var int
	 */
	private $line;
	/**
	 * @var int
	 */
	private $end_line;

	/**
	 * BaseDocPart constructor.
	 *
	 * @param string $name
	 * @param string $namespace
	 * @param int    $line
	 * @param int    $end_line
	 * @param array  $docblock
	 * @param array  $aliases
	 */
	public function __construct( string $name, string $namespace, int $line, int $end_line, array $docblock, array $aliases = [] ) {
		$this->name 	 = $name;
		$this->namespace = $namespace;
		$this->docblock  = $docblock;
		$this->line 	 = $line;
		$this->end_line  = $end_line;
		$this->aliases   = $aliases;
	}

	/**
	 * Gets the name.
	 *
	 * @return string The name of the part.
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Gets the namespace.
	 *
	 * @return string The namespace.
	 */
	public function getNamespace(): string {
		return $this->namespace;
	}

	/**
	 * Gets the tags.
	 *
	 * @return array The tags.
	 */
	public function getTags(): array {
		return $this->docblock['tags'];
	}

	/**
	 * Determines whether or not the item is flagged to be ignored.
	 *
	 * @return array Whether or not the item is flagged to be ignored.
	 */
	public function getIgnored(): array {
		return $this->getTagsData( 'ignore' );
	}

	/**
	 * Gets the associated since tags.
	 *
	 * @return array The since tags.
	 */
	public function getSince(): array {
		return $this->getTagsData( 'since' );
	}

	/**
	 * Gets the version in which the item first appeared.
	 *
	 * @return string The version in which the item first appeared.
	 */
	public function getFirstAppearance(): string {
		$since = '';

		foreach ( $this->getSince() as $sinceItem ) {
			if ( empty( $sinceItem['content'] ) ) {
				continue;
			}

			$since = $sinceItem['content'];
			break;
		}

		return $since;
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
	 * Gets the line that the part starts on.
	 *
	 * @return int The starting line of the part.
	 */
	public function getLine(): int {
		return $this->line;
	}

	/**
	 * Gets the line that the part ends on.
	 *
	 * @return int The ending line of the part.
	 */
	public function getEndLine(): int {
		return $this->end_line;
	}

	/**
	 * Gets the aliases.
	 *
	 * @return array The aliases of the part.
	 */
	public function getAliases(): array {
		return $this->aliases;
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
