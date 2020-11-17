<?php
/**
 * Alphabet Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode\Extension;

/**
 * Instance ID Query Part extension
 */
class InstanceId extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'instance-id';

	/**
	 * The instance_id.
	 *
	 * @var string
	 */
	public $instance_id = '';

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param mixed  $query      The query.
	 * @param string $display    The display/query type.
	 * @param string $attribute  The name of the attribute.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, string $display, string $attribute, string $value, array $attributes ) {
		$this->instance_id = $value;
		add_filter( 'a_z_listing_instance_id', array( $this, 'return_instance_id' ) );
		return $query;
	}

	/**
	 * Return the ID for this instance.
	 *
	 * @return string
	 */
	public function return_instance_id(): string {
		return $this->instance_id;
	}

	/**
	 * Remove the filters we added in `shortcode_query()`.
	 *
	 * @see shortcode_query
	 * @return void
	 */
	public function teardown() {
		remove_filter( 'a-z-listing-alphabet', array( $this, 'return_instance_id' ) );
	}
}
