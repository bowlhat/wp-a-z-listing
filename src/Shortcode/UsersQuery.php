<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UsersQuery extends Query {
	public $display = 'users';

	public function get_items() {
	}
}
