<?php
/**
 * A-Z Listing version helper file for documentation site
 *
 * @package a-z-listing
 */

$a_z_listing_path = dirname( __DIR__ );
$a_z_listing_data = get_plugin_data( trailingslashit( $a_z_listing_path ) . 'a-z-listing.php' );
$wp_version       = $a_z_listing_data['Version'];
