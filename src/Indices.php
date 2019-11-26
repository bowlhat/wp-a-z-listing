<?php
/**
 * Default index parsing functions.
 *
 * @package a-z-listing
 */

// declare(strict_types=1); // disabled for php5.6.

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class A_Z_Indices
 */
class Indices extends Singleton implements Extension {
	/**
	 * Bind the index parsing functions to their respective filters.
	 *
	 * @return void
	 */
	final public function initialize() {
		add_filter( '_a-z-listing-extract-item-indices', array( $this, 'get_item_indices' ), 1, 3 );
	}

	/**
	 * Find and return the index letter for a post
	 *
	 * @since 2.0.0
	 * @param array<string,mixed>   $indices Previously discovered indices.
	 * @param int|\WP_Post|\WP_Term $item    ID of the item whose index letters we want to find.
	 * @param string                $type    Type of listing - terms or posts.
	 * @return array<string,mixed> The post's index letters (usually matching the first character of the post title)
	 */
	public static function get_item_indices( array $indices, $item, string $type ): array {
		if ( 'terms' === $type || $item instanceof \WP_Term ) {
			if ( ! $item instanceof \WP_Term ) {
				$item = get_term( $item );
			}
			if ( $item instanceof \WP_Term ) {
				$title     = $item->name;
				$item_id   = $item->term_id;
				$permalink = get_term_link( $item );
			} else {
				return array();
			}
		} else {
			if ( ! $item instanceof \WP_Post ) {
				$item = get_post( $item );
			}
			if ( $item instanceof \WP_Post ) {
				$title     = get_the_title( $item );
				$item_id   = $item->ID;
				$permalink = get_the_permalink( $item );
			} else {
				return array();
			}
		}

		/**
		 * Modify the title for this item before indexing
		 *
		 * @since 2.1.0
		 * @since 4.0.0 Remove `int` from passed parameter types for `$item`.
		 * @param string            $title The current title
		 * @param \WP_Post|\WP_Term $item The item
		 * @param string            $item_type The type of the item. Either 'posts' or 'terms'.
		 */
		$title = apply_filters( 'a-z-listing-pre-index-item-title', $title, $item, $type );

		/**
		 * Modify the title for this item before indexing
		 *
		 * @since 2.1.0
		 * @since 4.0.0 Remove `int` from passed parameter types for `$item`.
		 * @param string            $title The current title
		 * @param \WP_Post|\WP_Term $item The item
		 * @param string            $item_type The type of the item. Either 'posts' or 'terms'.
		 */
		$title = apply_filters( 'a_z_listing_pre_index_item_title', $title, $item, $type );

		if ( $type instanceof \WP_Term ) {
			$item->term_name = $title;
		} elseif ( $type instanceof \WP_Post ) {
			$item->post_title = $title;
		}

		$index = Strings::maybe_mb_substr( $title, 0, 1 );

		/**
		 * Modify the indice(s) to group this item under
		 *
		 * @since 2.1.0
		 * @since 4.0.0 Remove `int` from passed parameter types for `$item`.
		 * @param array                 $indices The current indices
		 * @param \WP_Post|\WP_Term $item The item
		 * @param string                $item_type The type of the item. Either 'posts' or 'terms'.
		 */
		$index_letters = apply_filters( 'a-z-listing-item-index-letter', array( $index ), $item, $type );

		/**
		 * Modify the indice(s) to group this item under
		 *
		 * @since 2.1.0
		 * @since 4.0.0 Remove `int` from passed parameter types for `$item`.
		 * @param array             $indices The current indices
		 * @param \WP_Post|\WP_Term $item The item
		 * @param string            $item_type The type of the item. Either 'posts' or 'terms'.
		 */
		$index_letters = \apply_filters( 'a_z_listing_item_index_letter', $index_letters, $item, $type );
		$index_letters = array_unique( array_filter( $index_letters ) );

		foreach ( $index_letters as $letter ) {
			$indices[ $letter ][] = array(
				'title' => $title,
				'item'  => ( $item instanceof \WP_Term ) ? "term:{$item_id}" : "post:{$item_id}",
				'link'  => $permalink,
			);
		}

		$filter_params = array( $indices, $item );
		if ( $item instanceof \WP_Term ) {
			/**
			 * Modify the indice(s) to group this term under
			 *
			 * @deprecated Use a_z_listing_item_index_letter and/or a_z_listing_item_title
			 * @see a_z_listing_item_index_letter, a_z_listing_item_title
			 */
			$indices = apply_filters_deprecated( 'a_z_listing_term_indices', $filter_params, '1.0.0', 'a_z_listing_item_index_letter' );
		} else {
			/**
			 * Modify the indice(s) to group this post under
			 *
			 * @deprecated Use a_z_listing_item_index_letter and/or a_z_listing_item_title
			 * @see a_z_listing_item_index_letter, a_z_listing_item_title
			 */
			$indices = apply_filters_deprecated( 'a_z_listing_post_indices', $filter_params, '1.5.0', 'a_z_listing_item_index_letter' );
		} // End if.

		$filter_params = array( $indices, $item, $type );

		/**
		 * Modify the indice(s) to group this item under
		 *
		 * @since 1.7.0
		 * @since 2.1.0 Deprecated
		 * @deprecated Use a_z_listing_item_index_letter and/or a_z_listing_item_title
		 * @see a_z_listing_item_index_letter, a_z_listing_item_title
		 * @param array                 $indices The current indices
		 * @param int|\WP_Post|\WP_Term $item The item
		 * @param string                $item_type The type of the item. Either 'posts' or 'terms'.
		 */
		$indices = apply_filters_deprecated( 'a_z_listing_item_indices', $filter_params, '2.1.0', 'a_z_listing_item_index_letter' );

		/**
		 * Modify the indice(s) to group this item under
		 *
		 * @since 1.7.1
		 * @since 2.1.0 Deprecated
		 * @since 4.0.0 Remove `int` from passed parameter types for `$item`.
		 * @deprecated Use a_z_listing_item_index_letter and/or a_z_listing_item_title
		 * @see a_z_listing_item_index_letter, a_z_listing_item_title
		 * @param array             $indices The current indices
		 * @param \WP_Post|\WP_Term $item The item
		 * @param string            $item_type The type of the item. Either 'posts' or 'terms'.
		 */
		$indices = apply_filters_deprecated( 'a-z-listing-item-indices', $filter_params, '2.1.0', 'a_z_listing_item_index_letter' );

		if ( defined( 'AZLISTINGLOG' ) && AZLISTINGLOG > 2 ) {
			\do_action( 'log', 'Item indices', $indices );
		}

		return $indices;
	}
}
