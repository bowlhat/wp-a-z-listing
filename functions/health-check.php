<?php
/**
 * Health Check functionality
 *
 * @package a-z-listing
 * @since 2.3.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add A-Z Listing Health Checks
 *
 * @since 2.3.0
 *
 * @param array<string,mixed> $tests The health checks.
 * @return array<string,mixed> The health checks.
 */
function a_z_listing_add_health_check( array $tests ): array {
	$tests['direct']['a_z_listing_mbstring'] = array(
		'label' => __( 'A to Z Listing plugin', 'a-z-listing' ),
		'test'  => 'a_z_listing_mbstring_health_check',
	);
	return $tests;
}
add_filter( 'site_status_tests', 'a_z_listing_add_health_check' );

/**
 * The mbstring health check
 *
 * @since 2.3.0
 *
 * @return array<string,mixed> The health check results.
 */
function a_z_listing_mbstring_health_check(): array {
	$result = array(
		'label'       => __( 'A-Z Listing: PHP mbstring module is enabled', 'a-z-listing' ),
		'status'      => 'good',
		'badge'       => array(
			'label' => __( 'Compatibility', 'a-z-listing' ),
			'color' => 'green',
		),
		'description' => sprintf(
			'<p>%s</p>',
			__( 'The mbstring PHP module improves support for non-latin languages in the A-Z Listing plugin.', 'a-z-listing' )
		),
		'actions'     => '',
		'test'        => 'a_z_listing_mbstring_health_check',
	);

	if ( ! extension_loaded( 'mbstring' ) ) {
		$result['status']         = 'recommended';
		$result['label']          = __( 'A-Z Listing: PHP mbstring module is not enabled', 'a-z-listing' );
		$result['badge']['color'] = 'orange';
		$result['description']    = sprintf(
			'<p>%s</p>',
			__( 'The mbstring PHP module is not enabled on your server. This module improves support for non-latin languages in the A-Z Listing plugin.', 'a-z-listing' )
		);
		$result['actions']        = __( 'Contact your web host to request that the mbstring PHP module is enabled for your site.', 'a-z-listing' );
	}

	return $result;
}

/**
 * Add mbstring to the recommended modules section of the health-check feature
 *
 * @since 2.3.0
 *
 * @param array<string,mixed> $modules An associated array of module properties used during testing.
 * @return array<string,mixed> The `$modules` array with `mbstring` added.
 */
function a_z_listing_php_modules_health_check( array $modules ): array {
	$modules['mbstring']['extension'] = 'mbstring';
	if ( ! isset( $modules['mbstring']['required'] ) ) {
		$modules['mbstring']['required'] = false;
	}
	return $modules;
}
add_filter( 'site_status_test_php_modules', 'a_z_listing_php_modules_health_check' );
