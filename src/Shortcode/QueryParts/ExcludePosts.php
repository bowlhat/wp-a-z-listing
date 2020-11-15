<?php
/**
 * Exclude Posts Query Part.
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
 * Exclude Posts Query Part extension
 */
class ExcludePosts extends Shortcode_Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'exclude-posts';

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
	 * @param string $value      The shortcode attribute value.
	 * @param string $display    The display/query type.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display( $query, string $value, string $display, array $attributes ) {
		$exclude_posts = Strings::maybe_explode_string( ',', $value );
		$exclude_posts = array_map( 'intval', $exclude_posts );

		array_filter(
			$exclude_posts,
			function( int $value ): bool {
				return 0 < $value;
			}
		);

		$exclude_posts = array_unique( $exclude_posts );

		if ( ! empty( $exclude_posts ) ) {
			$query = wp_parse_args( $query, array( 'post__not_in' => $exclude_posts ) );
		}
	}
}
