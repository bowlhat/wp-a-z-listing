<?php
/**
 * A-Z Listing REST API Extensions
 *
 * @package a-z-listing
 */

/**
 * Shared REST API handler
 *
 * @since 1.0.0
 * @param WP_REST_Request $request The REST API Request.
 * @param array           $args Extra parameters set in the entrypoint functions.
 */
function a_z_listing_real_api_handler( WP_REST_Request $request, array $args ) {
	$output = a_z_shortcode_handler( $args );

	if ( $request->get_param( 'include-styles' ) ) {
		wp_enqueue_style( 'a-z-listing' );
		global $wp_styles;
		foreach ( $wp_styles->default_dirs as $key => $dir ) {
			if ( '/wp-includes/css/' === $dir ) {
				unset( $wp_styles->default_dirs[ $key ] );
			}
		}
		$wp_styles->do_concat = true;
		$wp_styles->do_items();
		$output = $wp_styles->print_html . $wp_styles->print_code . $output;
	}

	return array(
		'rendered' => $output,
	);
}

/**
 * REST API entrypoint for posts requests
 *
 * @since 2.0.0
 * @param WP_REST_Request $request The REST API Request.
 */
function a_z_listing_posts_api_handler( WP_REST_Request $request ) {
	$args = a_z_listing_api_handler_defaults( $request );

	$args['display']   = 'posts';
	$args['post_type'] = $request->get_param( 'post_type' );
	$args['taxonomy']  = $request->get_param( 'taxonomy' );
	$args['terms']     = $request->get_param( 'terms' );

	return a_z_listing_real_api_handler( $request, $args );
}

/**
 * REST API entrypoint for terms requests
 *
 * @since 2.0.0
 * @param WP_REST_Request $request The REST API Request.
 */
function a_z_listing_terms_api_handler( WP_REST_Request $request ) {
	$args = a_z_listing_api_handler_defaults( $request );

	$args['display']  = 'terms';
	$args['taxonomy'] = $request->get_param( 'taxonomy' );

	return a_z_listing_real_api_handler( $request, $args );
}

/**
 * Returns the default settings for the plugin's REST API Handler
 *
 * @since 2.0.0
 * @param WP_REST_Request $request The REST API Request.
 */
function a_z_listing_api_handler_defaults( WP_REST_Request $request ) {
	$args = array();

	$args['alphabet']      = $request->get_param( 'alphabet' );
	$args['grouping']      = $request->get_param( 'grouping' );
	$args['group-numbers'] = $request->get_param( 'group-numbers' );

	if ( $args['group-numbers'] ) {
		$args['numbers'] = true;
	} else {
		$args['numbers'] = $request->get_param( 'include-numbers' );
	}

	return $args;
}

add_action( 'rest_api_init', 'a_z_listing_register_rest_api' );
/**
 * Register the REST API extensions for the plugin
 *
 * @since 2.0.0
 */
function a_z_listing_register_rest_api() {
	$default_args = array(
		'alphabet'       => array(
			'description'       => __( 'Override default alphabet', 'a-z-listing' ),
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'grouping'       => array(
			'description' => __( 'Size of buckets to group the alphabet', 'a-z-listing' ),
			'type'        => 'integer',
			'default'     => 1,
			'minimum'     => 1,
		),
		'group-numbers'  => array(
			'description' => __( 'Include numbers as separate group. Implies {numbers:true}', 'a-z-listing' ),
			'type'        => 'boolean',
			'default'     => false,
		),
		'taxonomy'       => array(
			'description'       => __( 'Taxonomy', 'a-z-listing' ),
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'include-styles' => array(
			'description' => __( 'Include the stylesheet tags in the output', 'a-z-listing' ),
			'type'        => 'boolean',
			'default'     => false,
		),
	);

	register_rest_route(
		'a-z-listing/v1',
		'/posts/(?P<post_type>[a-z0-9-]+)',
		array(
			'methods'  => 'GET',
			'callback' => 'a_z_listing_posts_api_handler',
			'args'     => array(
				'post_type' => array(
					'description'       => __( 'Post type', 'a-z-listing' ),
					'type'              => 'string',
					'default'           => 'page',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'terms'     => array(
					'description'       => __( 'Terms to filter by', 'a-z-listing' ),
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
				$default_args,
			),
		)
	);
	register_rest_route(
		'a-z-listing/v1',
		'/terms/(?P<taxonomy>[a-z0-9-]+)',
		array(
			'methods'  => 'GET',
			'callback' => 'a_z_listing_terms_api_handler',
			'args'     => array(
				$default_args,
			),
		)
	);
}
