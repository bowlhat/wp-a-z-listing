<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Strings;

class PostType extends Shortcode_Extension {
	public $attribute_name = 'post-type';

	public function shortcode_query( $query, $value, $attributes ) {
		$post_type = Strings::maybe_explode_string( ',', $value );
		$post_type = array_unique( $post_type );

		$query['post_type'] = ( is_string( $post_type ) && ! empty( $post_type ) ) ? $post_type : 'page';

		return $query;
	}
}
