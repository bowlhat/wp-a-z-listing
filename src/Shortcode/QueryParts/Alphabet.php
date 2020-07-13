<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;

class Alphabet extends Shortcode_Extension {
	public $attribute_name = 'alphabet';

	public $alphabet = '';

	public function shortcode_query( $query, $value, $attributes ) {
		$this->alphabet = $value;
		add_filter( 'a-z-listing-alphabet', array( $this, 'return_alphabet' ) );
		return $query;
	}

	public function return_alphabet() {
		return $this->alphabet;
	}

	public function teardown() {
		remove_filter( 'a-z-listing-alphabet', array( $this, 'return_alphabet' ) );
	}
}
