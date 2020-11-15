<?php
/**
 * Query Type extension parent class
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Extension;
use \A_Z_Listing\Singleton;

/**
 * Query Type extension parent class
 */
abstract class Query extends Singleton implements Extension {
	/**
	 * The display/query type name.
	 *
	 * @var string
	 */
	public string $display;

	/**
	 * Initialize the extension.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	final public function initialize() {
		add_filter( 'a_z_listing_shortcode_query_types', array( $this, 'add_query_type' ) );
		add_filter( "a_z_listing_shortcode_query_for_display__{$this->display}", array( $this, 'apply_query_to_shortcode' ), 5, 2 );
		add_filter( "a_z_listing_get_items_for_display__{$this->display}", array( $this, 'get_items' ), 5, 2 );
	}

	/**
	 * Add this query type to the list of supported query types.
	 *
	 * @param array<string> $query_types The supported query types.
	 * @return array<string> The updated query types.
	 */
	public function add_query_type( array $query_types ): array {
		$query_types[] = $this->display;
		return $query_types;
	}

	/**
	 * Execute this query extension.
	 *
	 * @param mixed $query      The query.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return mixed The query.
	 */
	public function apply_query_to_shortcode( $query, array $attributes ) {
		foreach ( $attributes as $key => $value ) {
			if ( ! empty( $value ) ) {
				$query = apply_filters( "a_z_listing_shortcode_query_for_attribute__$key", $query, $value, $this->display, $key, $attributes );
				$query = apply_filters( "a_z_listing_shortcode_query_for_display__{$this->display}__and_attribute__{$key}", $query, $value, $this->display, $key, $attributes );
			}
		}
		return $query;
	}
}
