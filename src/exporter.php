<?php

namespace WP_Parser;

use phpDocumentor\Reflection\BaseReflector;
use phpDocumentor\Reflection\ClassReflector\MethodReflector;
use phpDocumentor\Reflection\ClassReflector\PropertyReflector;
use phpDocumentor\Reflection\FunctionReflector;
use phpDocumentor\Reflection\FunctionReflector\ArgumentReflector;
use phpDocumentor\Reflection\ReflectionAbstract;

/**
 * Class Exporter
 *
 * @package WP_Parser
 */
class Exporter {

	/**
	 * Exports the docblock for the passed element.
	 *
	 * @param BaseReflector|ReflectionAbstract $element The element to export the docblock for.
	 *
	 * @return array The exported docblock.
	 */
	public function export_docblock( $element ) {
		$docblock = $element->getDocBlock();

		if ( ! $docblock ) {
			return [
				'description'      => '',
				'long_description' => '',
				'tags'             => [],
			];
		}

		$output = [
			'description'      => preg_replace( '/[\n\r]+/', ' ', $docblock->getShortDescription() ),
			'long_description' => Formatter::fix_newlines( $docblock->getLongDescription()->getFormattedContents() ),
			'tags'             => [],
		];

		foreach ( $docblock->getTags() as $tag ) {
			$tag_data = [
				'name'    => $tag->getName(),
				'content' => preg_replace( '/[\n\r]+/', ' ', Formatter::format_description( $tag->getDescription() ) ),
			];

			if ( method_exists( $tag, 'getTypes' ) ) {
				$tag_data['types'] = $tag->getTypes();
			}

			if ( method_exists( $tag, 'getLink' ) ) {
				$tag_data['link'] = $tag->getLink();
			}

			if ( method_exists( $tag, 'getVariableName' ) ) {
				$tag_data['variable'] = $tag->getVariableName();
			}

			if ( method_exists( $tag, 'getReference' ) ) {
				$tag_data['refers'] = $tag->getReference();
			}

			if ( method_exists( $tag, 'getVersion' ) ) {
				// Version string.
				$version = $tag->getVersion();
				if ( ! empty( $version ) ) {
					$tag_data['content'] = $version;
				}

				// Description string.
				if ( method_exists( $tag, 'getDescription' ) ) {
					$description = preg_replace( '/[\n\r]+/', ' ', Formatter::format_description( $tag->getDescription() ) );

					if ( ! empty( $description ) ) {
						$tag_data['description'] = $description;
					}
				}
			}

			$output['tags'][] = $tag_data;
		}

		return $output;
	}

	/**
	 * Exports a formatted array of arguments based on te passed arguments.
	 *
	 * @param ArgumentReflector[] $arguments The arguments to format.
	 *
	 * @return array The formatted arguments.
	 */
	public function export_arguments( array $arguments ) {
		$output = [];

		foreach ( $arguments as $argument ) {
			$output[] = [
				'name'    => $argument->getName(),
				'default' => $argument->getDefault(),
				'type'    => $argument->getType(),
			];
		}

		return $output;
	}

	/**
	 * Exports a formatted array of properties based on the passed properties.
	 *
	 * @param PropertyReflector[] $properties The properties to format.
	 *
	 * @return array The formatted properties.
	 */
	public function export_properties( array $properties ) {
		$out = [];

		foreach ( $properties as $property ) {
			$out[] = [
				'name'       => $property->getName(),
				'line'       => $property->getLineNumber(),
				'end_line'   => $property->getNode()->getAttribute( 'endLine' ),
				'default'    => $property->getDefault(),
				'static'     => $property->isStatic(),
				'visibility' => $property->getVisibility(),
				'doc'        => $this->export_docblock( $property ),
			];
		}

		return $out;
	}

	/**
	 * Export the list of elements used by a file or structure.
	 *
	 * @param array $uses {
	 *        @type Function_Call_Reflector[] $functions The functions called.
	 * }
	 *
	 * @return array
	 */
	public function export_uses( array $uses ) {
		$out = [];

		// Ignore hooks here, they are exported separately.
		unset( $uses['hooks'] );

		foreach ( $uses as $type => $used_elements ) {

			/** @var MethodReflector|FunctionReflector $element */
			foreach ( $used_elements as $element ) {
				if ( $type !== 'methods' && $type !== 'functions' ) {
					continue;
				}

				if ( $type === 'methods' ) {
					$out[ $type ][] = $this->get_method_information( $element );

					continue;
				}

				// Fallback to functions.
				$out[ $type ][] = $this->get_function_information( $element );

				if ( $this->is_deprecated( $element ) ) {
					$out[ $type ][0]['deprecation_version'] = $this->export_deprecation_version( $element );
				}
			}
		}

		return $out;
	}

	/**
	 * Exports the version in which the element was deprecated.
	 *
	 * @param BaseReflector|ReflectionAbstract $element The element to export the deprecation version for.
	 *
	 * @return string The version in which the element was deprecated.
	 */
	protected function export_deprecation_version( $element ) {
		$arguments = $element->getNode()->args;

		$argument_value = $arguments[1];

		if ( ! property_exists( $argument_value, 'value' ) ) {
			return $argument_value;
		}

		if ( ! is_object( $argument_value->value ) ) {
			return $argument_value->value;
		}

		if ( ! property_exists( $argument_value->value, 'value' ) ) {
			return $argument_value->value;
		}

		return $argument_value->value->value;
	}

	/**
	 * Determines whether the passed element has been deprecated.
	 *
	 * @param BaseReflector|ReflectionAbstract $element The element to check.
	 *
	 * @return bool Whether or not the element is deprecated.
	 */
	protected function is_deprecated( $element ) {
		$deprecations = [
			'_deprecated_file',
			'_deprecated_function',
			'_deprecated_argument',
			'_deprecated_hook'
		];

		return in_array( $element->getName(), $deprecations, true );
	}

	/**
	 * Gets the method information for the passed element.
	 *
	 * @param BaseReflector|ReflectionAbstract $element The element to get the method information for.
	 *
	 * @return array The method information.
	 */
	protected function get_method_information( $element ) {
		$name = $element->getName();

		return [
			'name'     => $name[1],
			'class'    => $name[0],
			'static'   => $element->isStatic(),
			'line'     => $element->getLineNumber(),
			'end_line' => $element->getNode()->getAttribute( 'endLine' ),
		];
	}

	/**
	 * Gets the function information for the passed element.
	 *
	 * @param BaseReflector|ReflectionAbstract $element The element to get the function information for.
	 *
	 * @return array The function information.
	 */
	protected function get_function_information( $element ) {
		return [
			'name'     => $element->getName(),
			'line'     => $element->getLineNumber(),
			'end_line' => $element->getNode()->getAttribute( 'endLine' ),
		];
	}
}
