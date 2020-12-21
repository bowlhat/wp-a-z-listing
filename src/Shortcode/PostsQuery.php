<?php
/**
 * Posts Query class
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PostsQuery
 */
class PostsQuery extends Query {
	/**
	 * The display/query type name.
	 *
	 * @var string
	 */
	public $display = 'posts';

	/**
	 * Get the items for the query.
	 *
	 * @since 4.0.0
	 * @param array $items The items.
	 * @param mixed $query The query.
	 * @return array<\WP_Post> The items.
	 */
	public function get_items( array $items, $query ): array {
		if ( is_array( $items ) && 0 < count( $items ) ) {
			return $items;
		}

		add_filter( 'posts_fields', array( $this, 'wp_query_fields' ), 10, 2 );
		if ( $query instanceof \WP_Query ) {
			$items = $query->posts;
		} elseif ( isset( $query['child_of'] ) ) {
			$items = get_pages( $query );
		} else {
			$items = ( new \WP_Query( $query ) )->posts;
		}
		remove_filter( 'posts_fields', array( $this, 'wp_query_fields' ) );

		return empty( $items ) ? array() : $items;
	}

	/**
	 * Set the fields we require on \WP_Query.
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Converted to static function, and moved to \A_Z_Listing\Shortcode\PostsQuery.
	 * @param string    $fields The current fields in SQL format.
	 * @param \WP_Query $query  The \WP_Query instance.
	 * @return string The new fields in SQL format.
	 */
	public static function wp_query_fields( string $fields, \WP_Query $query ): string {
		global $wpdb;
		return "{$wpdb->posts}.ID, {$wpdb->posts}.post_title, {$wpdb->posts}.post_type, {$wpdb->posts}.post_name, {$wpdb->posts}.post_parent, {$wpdb->posts}.post_date";
	}

	/**
	 * Get the item.
	 * 
	 * @param mixed The item object or ID.
	 * @return \WP_Post The item object.
	 */
	public function get_item( $item ) {
		if ( $item instanceof \WP_Post ) {
			return $item;
		}

		return get_term( $item );
	}

	/**
	 * Get the item ID.
	 * 
	 * @param int      $item_id The item ID.
	 * @param \WP_Post $item    The item object.
	 * @return int The item ID.
	 */
	public function get_item_id( int $item_id, $item ) {
		if ( ! $item instanceof \WP_Post ) {
			$item = get_post( $item );
		}

		if ( $item instanceof \WP_Post ) {
			$item_id = $item->ID;
		}

		return $item_id;
	}

	/**
	 * Get the item title.
	 * 
	 * @param string   $title The item title.
	 * @param \WP_Post $item  The item object.
	 * @return string The item title.
	 */
	public function get_item_title( string $title, $item ) {
		if ( ! $item instanceof \WP_Post ) {
			$item = get_post( $item );
		}

		if ( $item instanceof \WP_Post ) {
			$title = get_the_title( $item );
		}

		return $title;
	}

	/**
	 * Get the item permalink.
	 *
	 * @param string   $permalink The item permalink.
	 * @param \WP_Post $item      The item object.
	 * @return string The item permalink
	 */
	public function get_item_permalink( string $permalink, $item ) {
		if ( ! $item instanceof \WP_Post ) {
			$item = get_post( $item );
		}

		if ( $item instanceof \WP_Post ) {
			$permalink = get_the_permalink( $item );
		}

		return $permalink;
	}
}
