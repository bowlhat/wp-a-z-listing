<?php
/**
 * Javascripts enqueueing functions.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Tabs script on pages where the shortcode is active
 *
 * @since 2.0.0
 * @param bool $force Set this to true if you want the script to always be enqueued.
 * @return void
 */
function a_z_listing_enqueue_tabs( bool $force = false ) {
	global $post;
	if ( $force || ( is_singular() && has_shortcode( $post->post_content, 'a-z-listing' ) ) ) {
		wp_enqueue_script( 'a-z-listing-tabs' );
	}
}

/**
 * Forcibly enqueue Tabs script. This is a helper function which can be hooked in-place of the default hook added in `a_z_listing_add_styling`
 *
 * @since 2.0.0
 * @return void
 */
function a_z_listing_force_enqueue_tabs() {
	a_z_listing_enqueue_tabs( true );
}

/**
 * Replace the default Tabs script enqueue function with `a_z_listing_force_enqueue_tabs` to always add the Tabs script to pages
 *
 * @since 2.0.0
 * @return void
 */
function a_z_listing_force_enable_tabs() {
	if ( false !== has_action( 'wp_enqueue_scripts', 'a_z_listing_enqueue_tabs' ) ) {
		remove_action( 'wp_enqueue_scripts', 'a_z_listing_enqueue_tabs' );
	}
	add_action( 'wp_enqueue_scripts', 'a_z_listing_force_enqueue_tabs' );
}

/**
 * Enqueue the widget configuration support script
 *
 * @since 2.1.0
 * @return void
 */
function a_z_listing_enqueue_widget_admin_script() {
	wp_enqueue_script( 'a-z-listing-widget-admin' );
}

/**
 * Enqueue Scrollfix script
 *
 * @since 4.0.0
 * @return void
 */
function a_z_listing_enqueue_scroll_fix() {
	wp_enqueue_script( 'a-z-listing-scroll-fix' );
}
