<?php

declare(strict_types=1);

namespace A_Z_Listing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Extension;
use \A_Z_Listing\Singleton;

abstract class Query extends Singleton implements Extension {
	public $display;

	final public function initialize() {
		add_filter( 'a_z_listing_shortcode_query_types', array( $this, 'add_attribute' ) );
		add_filter( "a_z_listing_shortcode_query_for_display__{$this->display}", array( $this, 'apply_query_to_shortcode' ), 5, 2 );
		add_action( "a_z_listing_get_items_for_display__{$this->display}", array( $this, 'get_items' ), 5, 1 );
	}

	public function add_attribute( $query_types ) {
		$query_types[] = $this->display;
		return $query_types;
	}

	public function apply_query_to_shortcode( $query, $attributes ) {
		foreach ( $attributes as $key => $value ) {
			if ( ! empty( $value ) ) {
				$query = apply_filters( "a_z_listing_shortcode_query_for_attribute__$key", $query, $value, $attributes );
				$query = apply_filters( "a_z_listing_shortcode_query_for_display__{$this->display}__and_attribute__{$key}", $query, $this->display, $value, $attributes );
			}
		}
		return $query;
	}
}
