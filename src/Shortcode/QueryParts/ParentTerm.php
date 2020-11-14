<?php
/**
 * Parent Term Query Part.
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
 * Parent Term Common implementation.
 */
abstract class ParentTermCommon extends Shortcode_Extension {
	/**
	 * Update the shortcode query
	 *
	 * @param mixed  $query      The query.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, $value, $attributes ) {
		if ( ! empty( $attributes['get-all-children'] ) && a_z_listing_is_truthy( $attributes['get-all-children'] ) ) {
			$parent_selector = 'child_of';
		} else {
			$parent_selector = 'parent';
		}

		if ( 0 <= $parent_id ) {
			$query = wp_parse_args(
				$query,
				array( $parent_selector => $value )
			);
		}

		return $query;
	}
}

/**
 * Parent Term Slug Or ID implementation.
 */
class ParentTermSlugOrId extends ParentTermCommon {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'parent-term';

	/**
	 * Update the shortcode query
	 *
	 * @param mixed  $query      The query.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, $value, $attributes ) {
		if ( is_numeric( $value ) ) {
			$parent_id = intval( $value );
		} else {
			$parent_term = get_term_by( 'slug', $value, $attributes['taxonomy'] );
			if ( false !== $parent_term ) {
				$parent_id = $parent_term->term_id;
			} else {
				$parent_id = -1;
			}
		}

		return parent::shortcode_query( $query, $parent_id, $attributes );
	}
}

/**
 * Parent Term ID implementation.
 */
class ParentTermId extends ParentTermCommon {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'parent-term-id';

	/**
	 * Update the shortcode query
	 *
	 * @param mixed  $query      The query.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, $value, $attributes ) {
		if ( is_numeric( $value ) ) {
			$parent_term = intval( $value );
		} else {
			$parent_id = -1;
		}

		return parent::shortcode_query( $query, $parent_id, $attributes );
	}
}

/**
 * Parent Term Query Parts wrapper extension.
 */
class ParentTerm extends Singleton implements Extension {
	/**
	 * Activate the Parent Term Query Parts extensions
	 *
	 * @since 4.0.0
	 * @param string $file  The plugin file path.
	 * @param array  $plugin The plugin details.
	 * @return Extension
	 */
	final public function activate( string $file = '', array $plugin = array() ): Extension {
		ParentTermSlugOrId::instance()->activate( $file );
		ParentTermId::instance()->activate( $file );
		return $this;
	}

	/**
	 * Initialize the Parent Term Query Part extensions
	 *
	 * @since 4.0.0
	 * @return void
	 */
	final public function initialize() {
		ParentTermSlugOrId::instance()->initialize();
		ParentTermId::instance()->initialize();
	}
}
