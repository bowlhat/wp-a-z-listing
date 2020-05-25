<?php

namespace A_Z_Listing\Shortcode\QueryParts;

use \A_Z_Listing\Shortcode_Extension;

class ParentPost extends Shortcode_Extension {
	public $attribute_name = 'parent-post';

	public function shortcode_query( $query, $value, $attributes ) {
		if ( a_z_listing_is_truthy( $attributes['get-all-children'] ) ) {
			$child_query = array( 'child_of' => $value );
		} else {
			$child_query = array( 'post_parent' => $value );
		}
		$query = wp_parse_args( $query, $child_query );

		return $query;
	}
}
