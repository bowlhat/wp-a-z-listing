<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Singleton;
use \A_Z_Listing\Extension;

abstract class ParentTermCommon extends Shortcode_Extension {
	public function shortcode_query( $query, $value, $attributes ) {
		if ( ! empty( $attributes['get-all-children'] ) && a_z_listing_is_truthy( $attributes['get-all-children'] ) ) {
			$parent_selector = 'child_of';
		} else {
			$parent_selector = 'parent';
		}

		if ( 0 <= $parent_id ) {
			$query = wp_parse_args(
				$query,
				array( $parent_selector => $value )
			);
		}

		return $query;
	}
}
class ParentTermSlugOrId extends ParentTermCommon {
	public $attribute_name = 'parent-term';

	public function shortcode_query( $query, $value, $attributes ) {
		if ( is_numeric( $value ) ) {
			$parent_term = intval( $value );
		} else {
			$parent_term = get_term_by( 'slug', $value, $attributes['taxonomy'] );
			if ( false !== $parent_term ) {
				$parent_id = $parent_term->term_id;
			} else {
				$parent_id = -1;
			}
		}

		return parent::shortcode_query( $query, $parent_id, $attributes );
	}
}
class ParentTermId extends ParentTermCommon {
	public $attribute_name = 'parent-term-id';

	public function shortcode_query( $query, $value, $attributes ) {
		if ( is_numeric( $value ) ) {
			$parent_term = intval( $value );
		} else {
			$parent_id = -1;
		}

		return parent::shortcode_query( $query, $parent_id, $attributes );
	}
}
class ParentTerm extends Singleton implements Extension {
	final public function activate( string $file = '', array $plugin = array() ): Extension {
		ParentTermSlugOrId::instance()->activate( $file );
		ParentTermId::instance()->activate( $file );
		return $this;
	}
	final public function initialize() {
		ParentTermSlugOrId::instance()->initialize();
		ParentTermId::instance()->initialize();
	}
}
