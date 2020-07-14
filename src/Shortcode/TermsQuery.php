<?php
/**
 * Terms Query class
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TermsQuery
 */
class TermsQuery extends Query {
	/**
	 * The display/query type name.
	 *
	 * @var string
	 */
	public $display = 'terms';

	/**
	 * Get the items for the query.
	 *
	 * @since 4.0.0
	 * @param array $items The items.
	 * @param mixed $query The query.
	 * @return array<\WP_Term> The items.
	 */
	public function get_items( $items, $query ) {
		if ( is_array( $items ) && 0 < count( $items ) ) {
			return $items;
		}
		$query = wp_parse_args(
			(array) $query,
			array(
				'hide_empty' => false,
				'taxonomy'   => 'category',
			)
		);
		return get_terms( $query ); // @phan-suppress-current-line PhanAccessMethodInternal
	}
}