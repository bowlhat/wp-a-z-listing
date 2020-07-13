<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TermsQuery extends Query {
	public $display = 'terms';

	public function get_items() {
	}
}
