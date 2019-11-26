<?php
/**
 * A-Z Listing version helper file for documentation site
 *
 * @package a-z-listing
 */

$path       = dirname( __DIR__ );
$azdata     = get_plugin_data( trailingslashit( $path ) . 'a-z-listing.php' );
$wp_version = $azdata['Version'];
