<?php
/**
 * Taxonomy Terms for taxonomies Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Strings;

/**
 * Taxonomy Terms Query Part extension for taxonomies
 */
class TermsTerms extends TermsCommon {
	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $display_types = array( 'terms' );

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param mixed  $query      The query.
	 * @param string $display    The display/query type.
	 * @param string $key        The name of the attribute.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display_and_attribute( $query, string $display, string $key, string $value, array $attributes ) {
		$taxonomies = isset( $attributes['taxonomy'] ) ? Strings::maybe_mb_split( ',', $attributes['taxonomy'] ) : array();

		$terms = $this->get_terms( $value, $taxonomies );

		$exclude_terms = $this->get_exclude_terms( $terms );
		$include_terms = $this->get_include_terms( $terms );
		$include_terms = array_diff( $include_terms, $exclude_terms );

		if ( ! empty( $include_terms ) ) {
			$query = wp_parse_args(
				$query,
				array(
					'include' => $include_terms,
				)
			);
		} else {
			$query = wp_parse_args(
				$query,
				array(
					'exclude' => $exclude_terms,
				)
			);
		}

		return $query;
	}
}
