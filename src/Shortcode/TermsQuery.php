<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TermsQuery extends Query {
	public $display = 'terms';

	public function get_items( $items, $query ) {
		if ( is_array( $items ) && 0 < count( $items ) ) {
			return $items;
		}
		$query = wp_parse_args(
			(array) $query,
			array(
				'hide_empty' => false,
				'taxonomy'   => 'category',
			)
		);
		return get_terms( $query ); // @phan-suppress-current-line PhanAccessMethodInternal
	}
}
