<?php
/**
 * Taxonomy Terms for posts Query Part.
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
 * Taxonomy Terms Query Part extension for posts
 */
class PostsTerms extends TermsCommon {
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

		$tax_query_defaults = array( 'relation' => 'AND' );
		if ( ! empty( $include_terms ) ) {
			$tax_query_defaults[] = array(
				'taxonomy'         => $attributes['taxonomy'],
				'field'            => 'term_id',
				'terms'            => $include_terms,
				'operator'         => 'IN',
				'include_children' => false,
			);
		}
		if ( ! empty( $exclude_terms ) ) {
			$tax_query_defaults[] = array(
				'taxonomy'         => $attributes['taxonomy'],
				'field'            => 'term_id',
				'terms'            => $exclude_terms,
				'operator'         => 'NOT IN',
				'include_children' => false,
			);
		}

		$tax_query = isset( $query['tax_query'] ) ? $query['tax_query'] : array();

		$query['tax_query'] = wp_parse_args( $tax_query, $tax_query_defaults ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		return $query;
	}
}
