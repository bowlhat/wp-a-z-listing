<?php
/**
 * Plugin Name:     A-Z Listing
 * Plugin URI:      https://a-z-listing.com/
 * Description:     Display an A to Z listing of posts
 * Author:          Daniel Llewellyn
 * Author URI:      https://bowlhat.net/
 * Text Domain:     a-z-listing
 * Domain Path:     /languages
 * Version:         4.0.0
 *
 * @package         A_Z_Listing
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'AZLISTINGLOG' ) ) {
	define( 'AZLISTINGLOG', false );
}

if ( file_exists( __DIR__ . '/build/vendor/autoload.php' ) ) {
	require __DIR__ . '/build/vendor/autoload.php';
} else {
	require __DIR__ . '/vendor/autoload.php';
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function a_z_listing_init() {
	\A_Z_Listing\Indices::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\GutenBlock::instance()->activate( __FILE__ )->initialize();

	// Shortcode handler.
	\A_Z_Listing\Shortcode::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\PostsQuery::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\TermsQuery::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\UsersQuery::instance()->activate( __FILE__ )->initialize();

	// Shortcode attribute handlers.
	\A_Z_Listing\Shortcode\QueryParts\Alphabet::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\QueryParts\ExcludePosts::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\QueryParts\ExcludeTerms::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\QueryParts\HideEmpty::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\QueryParts\ParentPost::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\QueryParts\ParentTerm::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\QueryParts\PostType::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\QueryParts\Taxonomy::instance()->activate( __FILE__ )->initialize();
	\A_Z_Listing\Shortcode\QueryParts\Terms::instance()->activate( __FILE__ )->initialize();
}

/**
 * Load all the plugin code.
 *
 * @return void
 */
function a_z_listing_plugins_loaded() {
	require __DIR__ . '/functions/i18n.php';
	require __DIR__ . '/functions/health-check.php';
	require __DIR__ . '/functions/helpers.php';
	require __DIR__ . '/functions/styles.php';
	require __DIR__ . '/functions/scripts.php';
	require __DIR__ . '/functions/enqueues.php';

	require __DIR__ . '/widgets/class-a-z-listing-widget.php';

	add_action( 'init', 'a_z_listing_init', 5 );
}
add_action( 'plugins_loaded', 'a_z_listing_plugins_loaded', 5 );
