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

use \A_Z_Listing\Shortcode\Extension;
use \A_Z_Listing\Strings;

/**
 * Post Type Query Part extension
 */
class PostType extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'post-type';

	/**
	 * The default value for the attribute.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $default_value = 'page';

	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $display_types = array( 'posts' );

	/**
	 * Sanitize the shortcode attribute.
	 *
	 * @param string $value      The value of the shortcode attribute.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return string The sanitized value.
	 */
	public function sanitize_attribute( string $value, array $attributes ): string {
		$value = trim( $value );
		return $value ? $value : 'page';
	}

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
		$post_type = Strings::maybe_mb_split( ',', $value );
		$post_type = array_map( 'trim', $post_type );
		$post_type = array_filter( $post_type );
		$post_type = array_unique( $post_type );

		$query['post_type'] = $post_type ? $post_type : array( 'page' );

		return $query;
	}
}
