<?php
/**
 * Contains the A-Z Index shortcode functionality
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode handler.
 */
class Shortcode extends Singleton implements Extension {
	/**
	 * Bind the shortcode to the handler.
	 *
	 * @return void
	 */
	final public function initialize() {
		add_shortcode( 'a-z-listing', array( $this, 'handle' ) );
	}

	/**
	 * Handle the a-z-listing shortcode
	 *
	 * @since 1.0.0
	 * @since 1.7.0 Add numbers attribute to append or prepend numerics to the listing.
	 * @since 1.8.0 Fix numbers attribute when selecting to display terms. Add grouping to numbers via attribute. Add alphabet override via new attribute.
	 * @since 2.0.0 Add parent-term and hide-empty parameters.
	 * @since 3.0.0 Move into a class and namespace.
	 * @since 4.0.0 Abstract away most of the specifics into separate classes.
	 * @param  string|array<string,mixed> $attributes Provided by WordPress core. Contains the shortcode attributes.
	 * @return string The A-Z Listing HTML.
	 * @suppress PhanPluginPossiblyStaticPublicMethod
	 */
	public function handle( $attributes = array() ): string {
		/**
		 * Run extensions.
		 */
		do_action( 'a_z_listing_shortcode_start', $attributes );

		$defaults   = apply_filters(
			'a_z_listing_get_shortcode_attributes',
			array(
				'display'          => 'posts',
				'get-all-children' => 'false',
				'group-numbers'    => '',
				'grouping'         => '',
				'numbers'          => 'hide',
				'return'           => 'listing',
				'target'           => '',
			)
		);
		$attributes = shortcode_atts(
			$defaults,
			$attributes,
			'a-z-listing'
		);

		foreach ( $attributes as $attribute => &$value ) {
			$value = apply_filters( "a_z_listing_sanitize_shortcode_attribute__$attribute", $value, $attributes );
		}
		$attributes = apply_filters( 'a_z_listing_sanitize_shortcode_attributes', $attributes );

		$grouping      = $attributes['grouping'];
		$group_numbers = false;
		if ( ! empty( $attributes['group-numbers'] ) && a_z_listing_is_truthy( $attributes['group-numbers'] ) ) {
			$group_numbers = true;
		}

		if ( 'numbers' === $grouping ) {
			$group_numbers = true;
			$grouping      = 0;
		} else {
			$grouping = intval( $grouping );
			if ( 1 < $grouping && empty( $attributes['group-numbers'] ) ) {
				$group_numbers = true;
			}
		}

		$grouping_obj = new Grouping( $grouping );
		$numbers_obj  = new Numbers( $attributes['numbers'], $group_numbers );

		$a_z_query = new Query( null, '', true, $attributes );

		$target = '';
		if ( ! empty( $attributes['target'] ) ) {
			if ( intval( $attributes['target'] ) > 0 ) {
				$target = get_permalink( $attributes['target'] );
			} else {
				$target = $attributes['target'];
			}
		}

		if ( 'letters' === $attributes['return'] ) {
			$ret = '<div class="az-letters">' . $a_z_query->get_the_letters( $target ) . '</div>';
		} else {
			$ret = $a_z_query->get_the_listing();
		}

		$grouping_obj->teardown();
		$numbers_obj->teardown();

		do_action( 'a_z_listing_shortcode_end', $attributes );

		return $ret;
	}
}
