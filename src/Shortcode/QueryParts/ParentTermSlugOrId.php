<?php
/**
 * Parent Term Query Part.
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
 * Parent Term Slug Or ID implementation.
 */
class ParentTermSlugOrId extends ParentTermCommon {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'parent-term';

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
		if ( is_numeric( $value ) ) {
			$parent_id = intval( $value );
		} else {
			$parent_id  = -1;
			$taxonomies = array( 'category' );
			if ( isset( $attributes['taxonomy'] ) ) {
				$taxonomies = Strings::maybe_mb_split( ',', $attributes['taxonomy'] );
			}

			foreach ( $taxonomies as $taxonomy ) {
				$parent_term = get_term_by( 'slug', $value, $taxonomy );
				if ( false !== $parent_term ) {
					$parent_id = $parent_term->term_id;
					break;
				}
			}
		}

		return $this->shortcode_query_with_parent_id( $query, $parent_id, $attributes );
	}
}