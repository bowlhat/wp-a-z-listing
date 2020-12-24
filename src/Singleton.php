<?php
/**
 * A-Z Listing singleton
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A_Z_Listing_Singleton
 */
abstract class Singleton implements Extension {
	/**
	 * Instances
	 *
	 * @since 4.0.0
	 * @var array<string,Extension>
	 */
	private static $instances = array();

	/**
	 * Singleton
	 *
	 * @since 4.0.0
	 * @see Extension::instance
	 * @suppress PhanPluginUnknownArrayMethodParamType
	 */
	final public static function instance(): Extension {
		$class = get_called_class();
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}
		return self::$instances[ $class ];
	}

	// phpcs:ignore Squiz.Commenting.FunctionComment.MissingParamTag
	/**
	 * Activate
	 *
	 * @since 4.0.0
	 * @see Extension::activate
	 * @suppress PhanPluginUnknownArrayMethodParamType,PhanPluginUnknownArrayMethodParamType
	 */
	public function activate( string $file = '', array $plugin = array() ): Extension {
		return $this;
	}

	/**
	 * Initialize
	 *
	 * @since 4.0.0
	 * @see Extension::initialize
	 * @suppress PhanPluginUnknownArrayMethodParamType
	 */
	abstract public function initialize();
}
