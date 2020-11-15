<?php
/**
 * Shortcode Extension class
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode Extension
 */
class Shortcode_Extension extends Singleton implements Extension {
	/**
	 * The attribute for this shortcode extension.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = '';

	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $display_types = array();

	/**
	 * Our hooks
	 *
	 * @since 4.0.0
	 * @var array
	 */
	protected $hooks = array(
		'action' => array(),
		'filter' => array(),
	);

	/**
	 * Initialize the extension.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	final public function initialize() {
		add_action( '_a_z_listing_shortcode_start', array( $this, 'handler' ), 10, 1 );
		add_action( '_a_z_listing_shortcode_end', array( $this, 'cleanup' ), 10, 1 );

		if ( ! empty( $this->attribute_name ) ) {
			add_filter( "a_z_listing_sanitize_shortcode_attribute__{$this->attribute_name}", array( $this, 'sanitize_attribute' ), 10, 2 );
			add_filter( "a_z_listing_shortcode_query_for_attribute__{$this->attribute_name}", array( $this, 'shortcode_query' ), 10, 3 );
			foreach ( $this->display_types as $display ) {
				add_filter( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$this->attribute_name}", array( $this, 'shortcode_query_for_display' ), 10, 4 );
			}
		}
	}

	/**
	 * Add a hook.
	 *
	 * @since 1.0.0
	 * @param string   $type The hook type (filter or action).
	 * @param string   $name The hook name.
	 * @param callable $function The function to call.
	 * @param int      $order The order to call this function.
	 * @param int      $arguments The number of arguments the function expects.
	 */
	final protected function add_hook( string $type, string $name, callable $function, int $order = 10, int $arguments = 1 ) {
		if ( ! defined( 'PHPUNIT_TEST_SUITE' ) && ( ! in_array( $type, array( 'action', 'filter' ), true ) || ! $this->activator || ! $this->activator->get_api_key_status( false ) ) ) {
			return;
		}

		$hook = array( $name, $function, $order, $arguments );
		if ( defined( 'PHPUNIT_TEST_SUITE' ) || ( $this->activator && $this->activator->get_api_key_status( false ) ) ) {
			call_user_func_array( "add_$type", $hook );
			$this->hooks[ $type ][] = $hook;
		}
	}

	/**
	 * Remove a hook.
	 *
	 * @since 1.0.0
	 * @param string   $type The hook type (filter or action).
	 * @param string   $name The hook name.
	 * @param callable $function The function to call.
	 * @param int      $order The order to call this function.
	 * @param int      $arguments The number of arguments the function expects.
	 */
	final protected function remove_hook( string $type, string $name, callable $function, int $order = 10, int $arguments = 1 ) {
		$hook = array( $name, $function, $order, $arguments );
		call_user_func_array( "remove_$type", $hook );
		array_filter(
			$this->filters[ $type ],
			function( $item ) use ( $hook ) {
				return $item === $hook;
			}
		);
	}

	/**
	 * Unhook all our filters and actions.
	 *
	 * @since 1.0.0
	 */
	final public function cleanup() {
		foreach ( array_keys( $this->hooks ) as $type ) {
			while ( $hook = array_shift( $this->hooks[ $type ] ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				call_user_func_array( "remove_$type", $hook );
			}
		}
	}

	/**
	 * Handle the shortcode.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function handler() {}

	/**
	 * Sanitize the shortcode attribute.
	 *
	 * @param string $value      The value of the shortcode attribute.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return string The sanitized value.
	 */
	public function sanitize_attribute( string $value, array $attributes ): string {
		return trim( $value );
	}

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param mixed  $query      The query.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, string $value, array $attributes ) {
		return $query;
	}

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param mixed  $query      The query.
	 * @param string $display    The display/query type.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display( $query, string $display, string $value, array $attributes ) {
		return $query;
	}
}
