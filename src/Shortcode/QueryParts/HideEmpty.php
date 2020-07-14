<?php
/**
 * Hide Empty Terms Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Singleton;
use \A_Z_Listing\Extension;

/**
 * Deprecated Hide Empty Terms Query Part extension
 */
class HideEmptyOld extends Shortcode_Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'hide-empty';

	/**
	 * Update the shortcode query
	 *
	 * @param mixed  $query      The query.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, $value, $attributes ) {
		$query['hide_empty'] = a_z_listing_is_truthy( $value );
		return $query;
	}
}

/**
 * Hide Empty Terms Query Part extension
 */
class HideEmptyTerms extends Shortcode_Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'hide-empty-terms';

	/**
	 * Update the shortcode query
	 *
	 * @param mixed  $query      The query.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, $value, $attributes ) {
		if ( ! isset( $query['hide_empty'] ) && a_z_listing_is_truthy( $value ) ) {
			$query['hide_empty'] = true;
		}
		return $query;
	}
}

/**
 * Hide Empty Terms Query Part extension wrapper
 */
class HideEmpty extends Singleton implements Extension {
	/**
	 * Activate the Hide Empty Terms Query Parts extensions
	 *
	 * @since 4.0.0
	 * @param string $file  The plugin file path.
	 * @param array  $plugin The plugin details.
	 * @return Extension
	 */
	final public function activate( string $file = '', array $plugin = array() ): Extension {
		HideEmptyOld::instance()->activate( $file );
		HideEmptyTerms::instance()->activate( $file );
		return $this;
	}

	/**
	 * Initialize the Hide Empty Terms Query Part extensions
	 *
	 * @since 4.0.0
	 * @return void
	 */
	final public function initialize() {
		HideEmptyOld::instance()->initialize();
		HideEmptyTerms::instance()->initialize();
	}
}
