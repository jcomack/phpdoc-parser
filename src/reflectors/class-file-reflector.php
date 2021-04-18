<?php

namespace WP_Parser;

use phpDocumentor\Reflection;
use phpDocumentor\Reflection\BaseReflector;
use phpDocumentor\Reflection\FileReflector;
use PhpParser\Node;
use PHPParser_Comment_Doc;
use PHPParser_Node_Expr_FuncCall;

/**
 * Reflection class for a full file.
 *
 * Extends the FileReflector from phpDocumentor to parse out WordPress
 * hooks and note function relationships.
 */
class File_Reflector extends FileReflector {
	/**
	 * List of elements used in global scope in this file, indexed by element type.
	 *
	 * @var array {
	 *      @type Hook_Reflector[] $hooks     The action and filters.
	 *      @type Function_Call_Reflector[] $functions The functions called.
	 * }
	 */
	public $uses = [];

	/**
	 * List of elements used in the current class scope, indexed by method.
	 *
	 * @var array[][] {@see \WP_Parser\File_Reflector::$uses}
	 */
	protected $method_uses_queue = [];

	/**
	 * Stack of classes/methods/functions currently being parsed.
	 *
	 * @see \WP_Parser\FileReflector::getLocation()
	 * @var BaseReflector[]
	 */
	protected $location = [];

	/**
	 * Last DocBlock associated with a non-documentable element.
	 *
	 * @var PHPParser_Comment_Doc
	 */
	protected $last_doc = null;

	public $hooks = [];

	/**
	 * Add hooks to the queue and update the node stack when we enter a node.
	 *
	 * If we are entering a class, function or method, we push it to the location
	 * stack. This is just so that we know whether we are in the file scope or not,
	 * so that hooks in the main file scope can be added to the file.
	 *
	 * We also check function calls to see if there are any actions or hooks. If
	 * there are, they are added to the file's hooks if in the global scope, or if
	 * we are in a function/method, they are added to the queue. They will be
	 * assigned to the function by leaveNode(). We also check for any other function
	 * calls and treat them similarly, so that we can export a list of functions
	 * used by each element.
	 *
	 * Finally, we pick up any docblocks for nodes that usually aren't documentable,
	 * so they can be assigned to the hooks to which they may belong.
	 *
	 * @param Node $node The node to enter.
	 *
	 * @return void
	 */
	public function enterNode( Node $node ) {
		parent::enterNode( $node );

		switch ( $node->getType() ) {
			// Add classes, functions, and methods to the current location stack
			case 'Stmt_Class':
			case 'Stmt_Function':
			case 'Stmt_ClassMethod':
				array_push( $this->location, $node );
				break;

			// Parse out hook definitions and function calls and add them to the queue.
			case 'Expr_FuncCall':
				$function = new Function_Call_Reflector( $node, $this->context );
				$this->getLocation()->uses['hooks'] = [];

				// Add the call to the list of functions used in this scope.
				$this->getLocation()->uses['functions'][] = $function;

				if ( $this->isFilter( $node ) ) {
					if ( $this->last_doc && ! $node->getDocComment() ) {
						$node->setAttribute( 'comments', [ $this->last_doc ] );
						$this->last_doc = null;
					}

					$hook = new Hook_Reflector( $node, $this->context );

					// Add it to the list of hooks used in this scope.
					$this->getLocation()->uses['hooks'][] = $hook;
					$this->hooks[] = $hook;
				}
				break;

			// Parse out method calls, so we can export where methods are used.
			case 'Expr_MethodCall':
			// Parse out `new Class()` calls as uses of Class::__construct().
			case 'Expr_New':
				$method = new Method_Call_Reflector( $node, $this->context );

				// Add it to the list of methods used in this scope.
				$this->addMethod( $method );
				break;

			// Parse out method calls, so we can export where methods are used.
			case 'Expr_StaticCall':
				$method = new Static_Method_Call_Reflector( $node, $this->context );

				// Add it to the list of methods used in this scope.
				$this->addMethod( $method );
				break;
		}

		// Pick up DocBlock from non-documentable elements so that it can be assigned
		// to the next hook if necessary. We don't do this for name nodes, since even
		// though they aren't documentable, they still carry the docblock from their
		// corresponding class/constant/function/etc. that they are the name of. If
		// we don't ignore them, we'll end up picking up docblocks that are already
		// associated with a named element, and so aren't really from a non-
		// documentable element after all.
		if ( ! $this->isNodeDocumentable( $node ) && 'Name' !== $node->getType() && ( $docblock = $node->getDocComment() ) ) {
			$this->last_doc = $docblock;
		}
	}

	/**
	 * Assign queued hooks to functions and update the node stack on leaving a node.
	 *
	 * We can now access the function/method reflectors, so we can assign any queued
	 * hooks to them. The reflector for a node isn't created until the node is left.
	 *
	 * @param Node $node The node to leave.
	 *
	 * @return void
	 */
	public function leaveNode( Node $node ) {

		parent::leaveNode( $node );

		switch ( $node->getType() ) {
			case 'Stmt_Class':
				$class = end( $this->classes );
				if ( ! empty( $this->method_uses_queue ) ) {
					/** @var Reflection\ClassReflector\MethodReflector $method */
					foreach ( $class->getMethods() as $method ) {
						if ( isset( $this->method_uses_queue[ $method->getName() ] ) ) {
							if ( isset( $this->method_uses_queue[ $method->getName() ]['methods'] ) ) {
								/*
								 * For methods used in a class, set the class on the method call.
								 * That allows us to later get the correct class name for $this, self, parent.
								 */
								foreach ( $this->method_uses_queue[ $method->getName() ]['methods'] as $method_call ) {
									/** @var Method_Call_Reflector $method_call */
									$method_call->set_class( $class );
								}
							}

							$method->uses = $this->method_uses_queue[ $method->getName() ];
						}
					}
				}

				$this->method_uses_queue = [];
				array_pop( $this->location );
				break;

			case 'Stmt_Function':
				$item = array_pop( $this->location );

				if ( ! property_exists( $item, 'uses' ) ) {
					$uses = '';
				} else {
					$uses = $item->uses;
				}

				end( $this->functions )->uses = $uses;
				break;

			case 'Stmt_ClassMethod':
				$method = array_pop( $this->location );

				/*
				 * Store the list of elements used by this method in the queue.
				 * We'll assign them to the method upon leaving the class (see above).
				 */
				if ( ! empty( $method->uses ) ) {
					$this->method_uses_queue[ $method->name ] = $method->uses;
				}
				break;
		}
	}

	/**
	 * @param Node $node
	 *
	 * @return bool
	 */
	protected function isFilter( Node $node ) {
		$calling = (string) $node->name;

		$functions = [
			'apply_filters',
			'apply_filters_ref_array',
			'apply_filters_deprecated',
			'do_action',
			'do_action_ref_array',
			'do_action_deprecated',

			'\apply_filters',
			'\apply_filters_ref_array',
			'\apply_filters_deprecated',
			'\do_action',
			'\do_action_ref_array',
			'\do_action_deprecated',
		];

		return in_array( $calling, $functions, true );
	}

	/**
	 * @return File_Reflector
	 */
	protected function getLocation() {
		return empty( $this->location ) ? $this : end( $this->location );
	}

	/**
	 * Determines whether the node is documentable.
	 *
	 * @param Node $node The node to check.
	 *
	 * @return bool Whether or not the node is documentable.
	 */
	protected function isNodeDocumentable( Node $node ) {
		return parent::isNodeDocumentable( $node )
		|| ( $node instanceof PHPParser_Node_Expr_FuncCall
			&& $this->isFilter( $node ) );
	}

	/**
	 * Adds the passed method to the uses stack.
	 *
	 * @param Method_Call_Reflector $method The method to add.
	 */
	protected function addMethod( Method_Call_Reflector $method ): void {
		$this->getLocation()->uses['methods'][] = $method;
	}

	public function getHooks() {
		return array_map( function( $item ) {}, $this->uses );
	}
}
