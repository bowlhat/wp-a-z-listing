<?php
/**
 * Exclude Terms Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Strings;

/**
 * Exclude Terms Query Part extension
 */
class ExcludeTerms extends Shortcode_Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'exclude-terms';

	/**
	 * Update the shortcode query
	 *
	 * @param mixed  $query      The query.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, $value, $attributes ) {
		$ex_terms = Strings::maybe_explode_string( ',', $value );
		$ex_terms = array_unique( $ex_terms );

		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $ex_terms,
			'operator' => 'NOT IN',
		);

		$query['tax_query'] = wp_parse_args( $query['tax_query'], $tax_query );
		return $query;
	}
}
