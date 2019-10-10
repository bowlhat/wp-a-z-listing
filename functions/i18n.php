<?php
/**
 * A-Z Listing Internationalisation
 *
 * @package a-z-listing
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize the translations for the plugin
 *
 * @since 2.0.0
 * @return void
 */
function a_z_listing_init_translations() {
	load_plugin_textdomain( 'a-z-listing' );
}
add_action( 'init', 'a_z_listing_init_translations' );
