<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PostsQuery extends Query {
	public $display = 'posts';

	public function get_items() {
	}
}
