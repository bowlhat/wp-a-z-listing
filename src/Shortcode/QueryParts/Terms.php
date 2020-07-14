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

use \A_Z_Listing\Shortcode_Extension;
use \A_Z_Listing\Singleton;
use \A_Z_Listing\Extension;
use \A_Z_Listing\Strings;

/**
 * Taxonomy Terms Query Part extension common implementation
 */
abstract class TermsCommon extends Shortcode_Extension {
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
	 * @return array<string> The terms.
	 */
	public function get_terms( $value ) {
		$terms = Strings::maybe_explode_string( ',', $value );
		return array_unique( $terms );
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
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display( $query, $display, $value, $attributes ) {
		$terms = $this->get_terms( $value );

		$tax_query[] = array(
			'taxonomy' => $attributes['taxonomy'],
			'field'    => 'slug',
			'terms'    => $terms,
			'operator' => 'IN',
		);

		$query['tax_query'] = wp_parse_args( $query['tax_query'], $tax_query );
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
	 * @param string $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display( $query, $display, $value, $attributes ) {
		print_r( $value );
		$terms = $this->get_terms( $value );

		$terms = array_map( 'trim', $terms );
		$terms = array_map(
			function ( string $term ) use ( $taxonomies ) : int {
				if ( is_numeric( $term ) ) {
					return intval( $term );
				} else {
					foreach ( $taxonomies as $taxonomy ) {
						$term_obj = get_term_by( 'slug', $taxonomy, $term );
						if ( false !== $term_obj ) {
							return $term_obj->term_id;
						}
					}
				}
				return -1;
			},
			$terms
		);
		$terms = array_map( 'intval', $terms );
		$terms = array_filter(
			$terms,
			function( int $value ): bool {
				return 0 < $value;
			}
		);
		$terms = array_unique( $terms );

		$query = wp_parse_args(
			$query,
			array( $terms_process => $terms )
		);
		return $query;
	}
}

/**
 * Terms Query Parts wrapper extension.
 */
class Terms extends Singleton implements Extension {
	/**
	 * Activate the Terms Query Parts extensions
	 *
	 * @since 4.0.0
	 * @param string $file  The plugin file path.
	 * @param array  $plugin The plugin details.
	 * @return Extension
	 */
	final public function activate( string $file = '', array $plugin = array() ): Extension {
		PostsTerms::instance()->activate( $file );
		TermsTerms::instance()->activate( $file );
		return $this;
	}

	/**
	 * Initialize the Terms Query Part extensions
	 *
	 * @since 4.0.0
	 * @return void
	 */
	final public function initialize() {
		PostsTerms::instance()->initialize();
		TermsTerms::instance()->initialize();
	}
}
