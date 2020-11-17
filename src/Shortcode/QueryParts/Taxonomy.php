<?php
/**
 * Taxonomy Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode\Extension;
use \A_Z_Listing\Strings;

/**
 * Taxonomy Query Part extension
 */
class Taxonomy extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'taxonomy';

	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $display_types = array( 'terms' );

	/**
	 * Sanitize the shortcode attribute.
	 *
	 * @param string $value      The value of the shortcode attribute.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return string The sanitized value.
	 */
	public function sanitize_attribute( string $value, array $attributes ): string {
		$value = trim( $value );
		return $value ? $value : '';
	}

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
	public function shortcode_query_for_display_and_attribute( $query, string $display, string $attribute, string $value, array $attributes ) {
		$query['taxonomy'] = Strings::maybe_mb_split( ',', $value );
		return $query;
	}
}
