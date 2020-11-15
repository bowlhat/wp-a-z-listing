<?php
/**
 * Post Type Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Strings;

/**
 * Post Type Query Part extension
 */
class PostType extends Shortcode_Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'post-type';

	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $display_types = array( 'display' );

	/**
	 * Sanitize the shortcode attribute.
	 *
	 * @param string $value      The value of the shortcode attribute.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return string The sanitized value.
	 */
	public function sanitize_attribute( string $value, array $attributes ): string {
		return trim( $value ) ?? 'page';
	}

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
		if ( 'posts' !== $display ) {
			return $query;
		}

		$post_type = Strings::maybe_explode_string( ',', $value );
		$post_type = array_map( 'trim', $post_type );
		$post_type = array_unique( $post_type );

		$query['post_type'] = $post_type ?? array( 'page' );

		return $query;
	}
}
