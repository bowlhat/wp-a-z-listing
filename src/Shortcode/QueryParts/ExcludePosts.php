<?php

namespace A_Z_Listing\Shortcode\QueryParts;

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Strings;

class ExcludePosts extends Shortcode_Extension {
	public $attribute_name = 'exclude-posts';

	public function shortcode_query( $query, $value, $attributes ) {
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
