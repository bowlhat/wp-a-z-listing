<?php

namespace A_Z_Listing\Shortcode\QueryParts;

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Strings;

class ExcludeTerms extends Shortcode_Extension {
	public $attribute_name = 'exclude-terms';

	public function shortcode_query( $query, $value, $attributes ) {
		$ex_terms = Strings::maybe_explode_string( ',', $value );
		$ex_terms = array_unique( $ex_terms );

		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $ex_terms,
			'operator' => 'NOT IN',
		);

		$query['tax_query'] = wp_parse_args( $query['tax_query'], $tax_query );
		return $query;
	}
}
