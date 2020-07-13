<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Singleton;
use \A_Z_Listing\Extension;
use \A_Z_Listing\Strings;

abstract class TermsCommon extends Shortcode_Extension {
	public $attribute_name = 'terms';

	public function get_terms( $value ) {
		$terms = Strings::maybe_explode_string( ',', $value );
		return array_unique( $terms );
	}
}
class PostsTerms extends TermsCommon {
	public $display_types = array( 'posts' );

	public function shortcode_query_for_display( $query, $display, $value, $attributes ) {
		$terms = $this->get_terms( $value );

		$tax_query[] = array(
			'taxonomy' => $attributes['taxonomy'],
			'field'    => 'slug',
			'terms'    => $terms,
			'operator' => 'IN',
		);

		$query['tax_query'] = wp_parse_args( $query['tax_query'], $tax_query );
		return $query;
	}
}
class TermsTerms extends TermsCommon {
	public $display_types = array( 'terms' );

	public function shortcode_query_for_display( $query, $display, $value, $attributes ) {
		print_r( $value );
		$terms = $this->get_terms( $value );

		$terms = array_map( 'trim', $terms );
		$terms = array_map(
			function ( string $term ) use ( $taxonomies ) : int {
				if ( is_numeric( $term ) ) {
					return intval( $term );
				} else {
					foreach ( $taxonomies as $taxonomy ) {
						$term_obj = get_term_by( 'slug', $taxonomy, $term );
						if ( false !== $term_obj ) {
							return $term_obj->term_id;
						}
					}
				}
				return -1;
			},
			$terms
		);
		$terms = array_map( 'intval', $terms );
		$terms = array_filter(
			$terms,
			function( int $value ): bool {
				return 0 < $value;
			}
		);
		$terms = array_unique( $terms );

		$query = wp_parse_args(
			$query,
			array( $terms_process => $terms )
		);
		return $query;
	}
}
class Terms extends Singleton implements Extension {
	final public function activate( string $file = '', array $plugin = array() ): Extension {
		PostsTerms::instance()->activate( $file );
		TermsTerms::instance()->activate( $file );
		return $this;
	}
	final public function initialize() {
		PostsTerms::instance()->initialize();
		TermsTerms::instance()->initialize();
	}
}
