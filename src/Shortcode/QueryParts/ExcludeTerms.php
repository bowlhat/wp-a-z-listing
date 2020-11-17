<?php
/**
 * Exclude Terms Query Part.
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
 * Exclude Terms Query Part extension
 */
class ExcludeTerms extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'exclude-terms';

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
	 * @param string $display    The display/query type.
	 * @param string $attribute  The name of the attribute.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display_and_attribute( $query, string $display, string $attribute, string $value, array $attributes ) {
		$exclude_terms = Strings::maybe_mb_split( ',', $value );
		$exclude_terms = array_map( 'trim', $exclude_terms );
		$exclude_terms = array_map( 'intval', $exclude_terms );
		$exclude_terms = array_filter(
			$exclude_terms,
			function( int $value ): bool {
				return 0 < $value;
			}
		);
		$exclude_terms = array_unique( $exclude_terms );

		if ( empty( $exclude_terms ) ) {
			return $query;
		}

		$taxonomy = isset( $attributes['taxonomy'] ) ? $attributes['taxonomy'] : 'category';

		$tax_query = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $exclude_terms,
				'operator' => 'NOT IN',
			),
		);

		if ( isset( $query['tax_query'] ) ) {
			$query['tax_query'] = wp_parse_args( $query['tax_query'], $tax_query );
		} else {
			$query['tax_query'] = $tax_query;
		}
		return $query;
	}
}
