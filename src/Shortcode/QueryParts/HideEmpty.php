<?php

namespace A_Z_Listing\Shortcode\QueryParts;

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Singleton;
use \A_Z_Listing\Extension;

class HideEmptyOld extends Shortcode_Extension {
	public $attribute_name = 'hide-empty';

	public function shortcode_query( $query, $value, $attributes ) {
		$query['hide_empty'] = a_z_listing_is_truthy( $value );
		return $query;
	}
}
class HideEmptyTerms extends Shortcode_Extension {
	public $attribute_name = 'hide-empty-terms';

	public function shortcode_query( $query, $value, $attributes ) {
		if ( ! isset( $query['hide_empty'] ) && a_z_listing_is_truthy( $value ) ) {
			$query['hide_empty'] = true;
		}
		return $query;
	}
}
class HideEmpty extends Singleton implements Extension {
	final public function activate( string $file = '', array $plugin = array() ): Extension {
		HideEmptyOld::instance()->activate( $file );
		HideEmptyTerms::instance()->activate( $file );
		return $this;
	}
	final public function initialize() {
		HideEmptyOld::instance()->initialize();
		HideEmptyTerms::instance()->initialize();
	}
}
