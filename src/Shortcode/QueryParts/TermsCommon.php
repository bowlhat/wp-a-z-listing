<?php
/**
 * Taxonomy Terms Query Part.
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \A_Z_Listing\Shortcode\Extension;
use \A_Z_Listing\Strings;

/**
 * Taxonomy Terms Query Part extension common implementation
 */
abstract class TermsCommon extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'terms';

	/**
	 * Get the configured terms.
	 *
	 * @since 4.0.0
	 * @param string $value The shortcode attribute value.
	 * @param array  $taxonomies The configured taxonomies.
	 * @return array<string> The terms.
	 */
	public function get_terms( string $value, array $taxonomies ): array {
		$terms = Strings::maybe_mb_split( ',', $value );
		$terms = array_map( 'trim', $terms );
		$terms = array_filter( $terms );
		$terms = array_unique( $terms );
		$terms = array_map(
			function ( string $term ) use ( $taxonomies ) : int {
				if ( is_numeric( $term ) ) {
					return intval( $term );
				} else {
					foreach ( $taxonomies as $taxonomy ) {
						$negate = false;
						$first  = substr( $term, 0, 1 );
						if ( '!' === $first ) {
							$term = substr( $term, 1 );
						}
						$term_obj = get_term_by( 'slug', $term, $taxonomy );
						if ( false !== $term_obj ) {
							if ( $negate ) {
								return -$term_obj->term_id;
							}
							return $term_obj->term_id;
						}
					}
				}
				return 0;
			},
			$terms
		);
		return array_unique( $terms );
	}

	/**
	 * Get the configured terms for exclusion.
	 *
	 * @since 4.0.0
	 * @param array $terms The terms.
	 * @return array<string> The terms for exclusion.
	 */
	public function get_exclude_terms( array $terms ): array {
		$terms = array_filter(
			$terms,
			function( int $value ): bool {
				return 0 > $value;
			}
		);
		$terms = array_map(
			function( $term ) {
				return -$term;
			},
			$terms
		);
		return array_values( array_unique( $terms ) );
	}

	/**
	 * Get the configured terms for inclusion.
	 *
	 * @since 4.0.0
	 * @param array $terms The terms.
	 * @return array<string> The terms for inclusion.
	 */
	public function get_include_terms( array $terms ): array {
		$terms = array_filter(
			$terms,
			function( int $value ): bool {
				return 0 < $value;
			}
		);
		return array_values( array_unique( $terms ) );
	}
}
