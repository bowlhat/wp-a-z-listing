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

/**
 * Taxonomy Terms Query Part extension for posts
 */
class PostsTerms extends TermsCommon {
	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $display_types = array( 'posts' );

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
	public function shortcode_query_for_display_and_attribute( $query, string $display, string $attribute, string $value, array $attributes ) {
		$taxonomies = isset( $attributes['taxonomy'] ) ? Strings::maybe_mb_split( ',', $attributes['taxonomy'] ) : array();

		$terms = $this->get_terms( $value, $taxonomies );

		$exclude_terms = $this->get_exclude_terms( $terms );
		$include_terms = $this->get_include_terms( $terms );
		$include_terms = array_diff( $include_terms, $exclude_terms );

		$tax_query_defaults = array( 'relation' => 'AND' );
		if ( ! empty( $include_terms ) ) {
			$tax_query_defaults[] = array(
				'taxonomy' => $attributes['taxonomy'],
				'field'    => 'term_id',
				'terms'    => $include_terms,
				'operator' => 'IN',
			);
		}
		if ( ! empty( $exclude_terms ) ) {
			$tax_query_defaults[] = array(
				'taxonomy' => $attributes['taxonomy'],
				'field'    => 'term_id',
				'terms'    => $exclude_terms,
				'operator' => 'NOT IN',
			);
		}

		$tax_query = isset( $query['tax_query'] ) ? $query['tax_query'] : array();

		$query['tax_query'] = wp_parse_args( $tax_query, $tax_query_defaults );
		return $query;
	}
}

/**
 * Taxonomy Terms Query Part extension for taxonomies
 */
class TermsTerms extends TermsCommon {
	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $display_types = array( 'terms' );

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
	public function shortcode_query_for_display_and_attribute( $query, string $display, string $attribute, string $value, array $attributes ) {
		$taxonomies = isset( $attributes['taxonomy'] ) ? Strings::maybe_mb_split( ',', $attributes['taxonomy'] ) : array();

		$terms = $this->get_terms( $value, $taxonomies );

		$exclude_terms = $this->get_exclude_terms( $terms );
		$include_terms = $this->get_include_terms( $terms );
		$include_terms = array_diff( $include_terms, $exclude_terms );

		if ( ! empty( $include_terms ) ) {
			$query = wp_parse_args(
				$query,
				array(
					'include' => $include_terms,
				)
			);
		} else {
			$query = wp_parse_args(
				$query,
				array(
					'exclude' => $exclude_terms,
				)
			);
		}

		return $query;
	}
}
