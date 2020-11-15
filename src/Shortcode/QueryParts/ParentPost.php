<?php
/**
 * Parent Post Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;

/**
 * Parent Post Query Part extension
 */
class ParentPost extends Shortcode_Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'parent-post';

	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $display_types = array( 'posts' );

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param mixed  $query      The query.
	 * @param string $value      The shortcode attribute value.
	 * @param string $display    The display/query type.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display( $query, string $value, string $display, array $attributes ) {
		if ( a_z_listing_is_truthy( $attributes['get-all-children'] ) ) {
			$child_query = array( 'child_of' => $value );
		} else {
			$child_query = array( 'post_parent' => $value );
		}
		$query = wp_parse_args( $query, $child_query );

		return $query;
	}
}
