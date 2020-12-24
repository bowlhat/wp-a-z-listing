<?php
/**
 * A-Z Listing Extension interface
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A-Z Listing Extension interface
 */
interface Extension {
	/**
	 * Singleton
	 *
	 * @since 4.0.0
	 * @return Extension extension object.
	 */
	public static function instance(): Extension;

	/**
	 * Activate
	 *
	 * @since 4.0.0
	 * @param string              $file   the plugin file.
	 * @param array<string,mixed> $plugin the plugin details.
	 * @return Extension extension object.
	 */
	public function activate( string $file = '', array $plugin = array() ): Extension;

	/**
	 * Initialize
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function initialize();
}
