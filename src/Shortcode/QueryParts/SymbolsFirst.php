<?php
/**
 * Symbols First Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode\Extension;

/**
 * Symbols First Query Part extension
 */
class SymbolsFirst extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'symbols-first';

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param mixed  $query      The query.
	 * @param string $display    The display/query type.
	 * @param string $attribute  The name of the attribute.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, string $display, string $attribute, string $value, array $attributes ) {
		if ( \a_z_listing_is_truthy( $value ) ) {
			$this->add_hook( 'filter', 'a_z_listing_unknown_letter_is_first', '__return_true', 10, 1 );
		} else {
			$this->add_hook( 'filter', 'a_z_listing_unknown_letter_is_first', '__return_false', 10, 1 );
		}
		return $query;
	}
}
