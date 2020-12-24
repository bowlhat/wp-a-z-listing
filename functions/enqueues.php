<?php
/**
 * A-Z Listing Styles and Scripts enqueue functions
 *
 * @package a-z-listing
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register default A-Z stylesheet, jQuery-UI Tabs script and add our enqueue
 * functions to the `wp_enqueue_scripts` action
 *
 * @since 2.0.0 Renamed from a_z_listing_add_styling. Added jQuery-UI Tabs support.
 * @return void
 */
function a_z_listing_do_enqueue() {
	wp_register_style(
		'a-z-listing',
		plugins_url( 'css/a-z-listing-default.css', dirname( __FILE__ ) ),
		array( 'dashicons' ),
		A_Z_LISTING_VERSION
	);

	wp_register_style(
		'a-z-listing-admin',
		plugins_url( 'css/a-z-listing-customize.css', dirname( __FILE__ ) ),
		array(),
		A_Z_LISTING_VERSION
	);

	wp_register_script(
		'a-z-listing-tabs',
		plugins_url( 'scripts/a-z-listing-tabs.js', dirname( __FILE__ ) ),
		array( 'jquery', 'jquery-ui-tabs' ),
		A_Z_LISTING_VERSION,
		true
	);

	wp_register_script(
		'a-z-listing-widget-admin',
		plugins_url( 'scripts/a-z-listing-widget-admin.js', dirname( __FILE__ ) ),
		array( 'jquery', 'jquery-ui-autocomplete' ),
		A_Z_LISTING_VERSION,
		true
	);
	wp_localize_script(
		'a-z-listing-widget-admin',
		'a_z_listing_widget_admin',
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
	);

	wp_register_script(
		'a-z-listing-scroll-fix',
		plugins_url( 'scripts/a-z-listing-scroll-fix.js', dirname( __FILE__ ) ),
		array(),
		A_Z_LISTING_VERSION,
		true
	);
	wp_localize_script(
		'a-z-listing-scroll-fix',
		'a_z_listing_scroll_fix',
		array( 'offset' => -120 )
	);

	$add_styles = get_option( 'a-z-listing-add-styling', true );
	/**
	 * Determine whether to add default listing styling
	 *
	 * @param bool True to add default styling, False to disable.
	 * @since 1.7.1
	 */
	$add_styles = apply_filters( 'a_z_listing_add_styling', $add_styles );
	/**
	 * Determine whether to add default listing styling
	 *
	 * @param bool True to add default styling, False to disable.
	 * @since 1.7.1
	 */
	$add_styles = apply_filters( 'a-z-listing-add-styling', $add_styles ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	if ( defined( 'A_Z_LISTING_LOG' ) && A_Z_LISTING_LOG ) {
		do_action( 'a_z_listing_log', 'A-Z Listing: Add Styles', $add_styles );
	}
	if ( true === $add_styles && ! has_action( 'wp_enqueue_scripts', 'a_z_listing_enqueue_styles' ) ) {
		add_action( 'wp_enqueue_scripts', 'a_z_listing_enqueue_styles' );
	}

	add_action( 'customize_controls_enqueue_scripts', 'a_z_listing_customize_enqueue_styles' );

	$tabify = get_option( 'a-z-listing-add-tabs', false );
	/**
	 * Determine whether to add jQuery-UI Tabs
	 *
	 * @param bool True to add jQuery-UI Tabs, False to disable.
	 * @since 2.0.0
	 */
	$tabify = apply_filters( 'a_z_listing_tabify', $tabify );
	/**
	 * Determine whether to add jQuery-UI Tabs
	 *
	 * @param bool True to add jQuery-UI Tabs, False to disable.
	 * @since 2.0.0
	 */
	$tabify = apply_filters( 'a-z-listing-tabify', $tabify ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	if ( defined( 'A_Z_LISTING_LOG' ) && A_Z_LISTING_LOG ) {
		do_action( 'a_z_listing_log', 'A-Z Listing: Tabify', $tabify );
	}
	if ( true === $tabify && ! has_action( 'wp_enqueue_scripts', 'a_z_listing_enqueue_tabs' ) ) {
		add_action( 'wp_enqueue_scripts', 'a_z_listing_enqueue_tabs' );
	}
}
add_action( 'init', 'a_z_listing_do_enqueue' );
