<?php
/**
 * Alphabet Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode_Extension;

/**
 * Alphabet Query Part extension
 */
class Alphabet extends Shortcode_Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public string $attribute_name = 'alphabet';

	/**
	 * The alphabet.
	 *
	 * @var string
	 */
	public string $alphabet = '';

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param mixed  $query      The query.
	 * @param string $display    The display/query type.
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display( $query, string $display, string $value, array $attributes ) {
		$this->alphabet = $value;
		add_filter( 'a-z-listing-alphabet', array( $this, 'return_alphabet' ) );
		return $query;
	}

	/**
	 * Return the Alphabet for this instance.
	 *
	 * @return string
	 */
	public function return_alphabet(): string {
		return $this->alphabet;
	}

	/**
	 * Remove the filters we added in `shortcode_query()`.
	 *
	 * @see shortcode_query
	 * @return void
	 */
	public function teardown() {
		remove_filter( 'a-z-listing-alphabet', array( $this, 'return_alphabet' ) );
	}
}
