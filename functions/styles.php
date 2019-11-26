<?php
/**
 * A-Z Listing Styles
 *
 * @package a-z-listing
 */

// declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue A-Z default styling on pages where either the widget or the shortcode are active
 *
 * @since 0.7
 * @param bool $force Set this to true if you want the styling to always be enqueued.
 * @return void
 */
function a_z_listing_enqueue_styles( bool $force = false ) {
	global $post;
	if ( $force || is_customize_preview() || ( is_singular() && has_shortcode( $post->post_content, 'a-z-listing' ) ) ) {
		wp_enqueue_style( 'a-z-listing' );
	}
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
 * @return void
 */
function a_z_listing_force_enqueue_styles() {
	a_z_listing_enqueue_styles( true );
}

/**
 * Replace the default styling enqueue function with `a_z_listing_force_enqueue_styles` to always add the styling to pages
 *
 * @since 1.3.0
 * @return void
 */
function a_z_listing_force_enable_styles() {
	if ( false !== has_action( 'wp_enqueue_scripts', 'a_z_listing_enqueue_styles' ) ) {
		remove_action( 'wp_enqueue_scripts', 'a_z_listing_enqueue_styles' );
	}
	add_action( 'wp_enqueue_scripts', 'a_z_listing_force_enqueue_styles' );
}
