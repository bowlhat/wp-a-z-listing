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
	 * Execute this query extension.
	 *
	 * @param mixed $query      The query.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return mixed The query.
	 */
	public function apply_query_to_shortcode( $query, array $attributes ) {
		$query = wp_parse_args(
			(array) $query,
			array(
				'hide_empty' => 0,
				'taxonomy'   => 'category',
			)
		);

		return parent::apply_query_to_shortcode( $query, $attributes );
	}

	/**
	 * Get the items for the query.
	 *
	 * @since 4.0.0
	 * @param array $items The items.
	 * @param mixed $query The query.
	 * @return array<\WP_Term> The items.
	 */
	public function get_items( array $items, $query ): array {
		if ( is_array( $items ) && 0 < count( $items ) ) {
			return $items;
		}

		return get_terms( $query ); // @phan-suppress-current-line PhanAccessMethodInternal
	}

	/**
	 * Get the item.
	 * 
	 * @param mixed $item The item object or ID.
	 * @return \WP_Term The item object.
	 */
	public function get_item( $item ) {
		if ( $item instanceof \WP_Term ) {
			return $item;
		}

		return get_term( $item );
	}

	/**
	 * Get the item ID.
	 * 
	 * @param int      $item_id The item ID.
	 * @param \WP_Term $item    The item object.
	 * @return int The item ID.
	 */
	public function get_item_id( int $item_id, $item ) {
		if ( ! $item instanceof \WP_Term ) {
			$item = get_term( $item );
		}

		if ( $item instanceof \WP_Term ) {
			$item_id = $item->term_id;
		}

		return $item_id;
	}

	/**
	 * Get the item title.
	 * 
	 * @param string   $title The item title.
	 * @param \WP_Term $item  The item object.
	 * @return string The item title.
	 */
	public function get_item_title( string $title, $item ) {
		if ( ! $item instanceof \WP_Term ) {
			$item = get_term( $item );
		}

		if ( $item instanceof \WP_Term ) {
			$title = $item->name;
		}

		return $title;
	}

	/**
	 * Get the item permalink.
	 *
	 * @param string   $permalink The item permalink.
	 * @param \WP_Term $item      The item object.
	 * @return string The item permalink
	 */
	public function get_item_permalink( string $permalink, $item ) {
		if ( ! $item instanceof \WP_Term ) {
			$item = get_term( $item );
		}

		if ( $item instanceof \WP_Term ) {
			$permalink = get_term_link( $item );
		}

		return $permalink;
	}
}
