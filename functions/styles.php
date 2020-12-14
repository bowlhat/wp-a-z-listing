<?php
/**
 * A-Z Listing Styles
 *
 * @package a-z-listing
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue A-Z default styling
 *
 * @since 0.7
 * @since 4.0.0 Don't conditionally load to alleviate issues with not loading.
 * @param bool $unused Not used.
 * @return void
 */
function a_z_listing_enqueue_styles( bool $unused = false ) {
	wp_enqueue_style( 'a-z-listing' );
}

/**
 * Enqueue A-Z customizer styles.
 *
 * @since 2.1.0
 * @return void
 */
function a_z_listing_customize_enqueue_styles() {
	wp_enqueue_style( 'a-z-listing-admin' );
}

/**
 * Forcibly enqueue styling. This is a helper function which can be hooked in-place of the default hook added in `a_z_listing_add_styling`
 *
 * @since 1.3.0
 * @since 4.0.0 deprecated
 * @deprecated
 * @return void
 */
function a_z_listing_force_enqueue_styles() {
	// no-op.
}

/**
 * Replace the default styling enqueue function with `a_z_listing_force_enqueue_styles` to always add the styling to pages
 *
 * @since 1.3.0
 * @since 4.0.0 deprecated
 * @deprecated
 * @return void
 */
function a_z_listing_force_enable_styles() {
	// no-op.
}
