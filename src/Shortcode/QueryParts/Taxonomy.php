<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;

class Taxonomy extends Shortcode_Extension {
	public $attribute_name = 'taxonomy';
	public $display_types = array( 'terms' );

	public function sanitize_attribute( $value, $attributes ) {
		return $value ?? 'category';
	}

	public function shortcode_query_for_display( $query, $display, $value, $attributes ) {
		if ( 'terms' === $display ) {
			$query['taxonomy'] = $value;
		}
		return $query;
	}
}
