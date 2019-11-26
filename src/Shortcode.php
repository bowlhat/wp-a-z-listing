<?php
/**
 * Contains the A-Z Index shortcode functionality
 *
 * @package a-z-listing
 */

// declare(strict_types=1); // disabled for php5.6.

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
	 * @param  array<string,mixed> $attributes Provided by WordPress core. Contains the shortcode attributes.
	 * @return string The A-Z Listing HTML.
	 * @suppress PhanPluginPossiblyStaticPublicMethod
	 */
	function handle( array $attributes ): string {
		/**
		 * Run extensions.
		 */
		do_action( '_a_z_listing_shortcode_start', $attributes );

		$attributes = shortcode_atts(
			array(
				'alphabet'         => '',
				'display'          => 'posts',
				'exclude-posts'    => '',
				'exclude-terms'    => '',
				'get-all-children' => 'false',
				'group-numbers'    => '',
				'grouping'         => '',
				'hide-empty-terms' => 'false',
				'numbers'          => 'hide',
				'parent-post'      => '',
				'parent-term'      => '',
				'post-type'        => 'page',
				'return'           => 'listing',
				'target'           => '',
				'taxonomy'         => '',
				'terms'            => '',
			),
			$attributes,
			'a-z-listing'
		);

		if ( ! empty( $attributes['alphabet'] ) ) {
			$override = $attributes['alphabet'];
			add_filter(
				'a-z-listing-alphabet',
				/**
				 * Closure to override the alphabet with one defined in the shortcode
				 *
				 * @return string The alphabet from the shortcode
				 */
				function() use ( $override ) {
					return $override;
				}
			);
		}

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

		if ( 'terms' === $attributes['display'] && ! empty( $attributes['taxonomy'] ) ) {
			$taxonomy = ! empty( $attributes['taxonomy'] ) ? $attributes['taxonomy'] : 'category';
			if ( isset( $attributes['hide-empty'] ) && ! empty( $attributes['hide-empty'] ) ) {
				$hide_empty = a_z_listing_is_truthy( $attributes['hide-empty'] );
			} else {
				$hide_empty = a_z_listing_is_truthy( $attributes['hide-empty-terms'] );
			}

			$taxonomies = explode( ',', $taxonomy );
			$taxonomies = array_unique( array_filter( array_map( 'trim', $taxonomies ) ) );

			$query = array(
				'taxonomy'   => $taxonomies,
				'hide_empty' => $hide_empty,
			);

			$terms_string  = '';
			$terms_process = 'include';

			if ( ! empty( $attributes['terms'] ) ) {
				$terms_string = $attributes['terms'];
			} elseif ( ! empty( $attributes['exclude-terms'] ) ) {
				$terms_string  = $attributes['exclude-terms'];
				$terms_process = 'exclude';
			}

			if ( ! empty( $terms_string ) ) {
				$terms = explode( ',', $terms_string );
				$terms = array_unique(
					array_filter(
						array_map(
							'intval',
							array_map( 'trim', $terms )
						),
						function( int $value ): bool {
							return 0 < $value;
						}
					)
				);

				$query = wp_parse_args(
					$query,
					array( $terms_process => $terms )
				);
			}

			if ( ! empty( $attributes['parent-term'] ) ) {
				$parent_id = intval( $attributes['parent-term'] );
				if ( ! empty( $attributes['get-all-children'] ) && a_z_listing_is_truthy( $attributes['get-all-children'] ) ) {
					$parent_selector = 'child_of';
				} else {
					$parent_selector = 'parent';
				}

				if ( 0 < $parent_id ) {
					$query = wp_parse_args(
						$query,
						array( $parent_selector => $parent_id )
					);
				}
			}

			$a_z_query = new Query( $query, 'terms' );
		} else {
			$post_type = explode( ',', $attributes['post-type'] );
			$post_type = array_unique( array_filter( array_map( 'trim', $post_type ) ) );

			$query = array( 'post_type' => $post_type );

			if ( ! empty( $attributes['exclude-posts'] ) ) {
				$exclude_posts = explode( ',', $attributes['exclude-posts'] );
				$exclude_posts = array_unique(
					array_filter(
						array_map(
							'intval',
							array_map( 'trim', $exclude_posts )
						),
						function( int $value ): bool {
							return 0 < $value;
						}
					)
				);

				if ( ! empty( $exclude_posts ) ) {
					$query = wp_parse_args( $query, array( 'post__not_in' => $exclude_posts ) );
				}
			}

			if ( ! empty( $attributes['parent-post'] ) ) {
				if ( a_z_listing_is_truthy( $attributes['get-all-children'] ) ) {
					$child_query = array( 'child_of' => $attributes['parent-post'] );
				} else {
					$child_query = array( 'post_parent' => $attributes['parent-post'] );
				}
				$query = wp_parse_args( $query, $child_query );
			}

			$taxonomy  = $attributes['taxonomy'] ?? 'category';
			$tax_query = array();
			if ( ! empty( $attributes['terms'] ) ) {
				$terms = explode( ',', $attributes['terms'] );
				$terms = array_unique( array_filter( array_map( 'trim', $terms ) ) );

				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $terms,
					'operator' => 'IN',
				);
			}
			if ( ! empty( $attributes['exclude-terms'] ) ) {
				$ex_terms = explode( ',', $attributes['exclude-terms'] );
				$ex_terms = array_unique( array_filter( array_map( 'trim', $ex_terms ) ) );

				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $ex_terms,
					'operator' => 'NOT IN',
				);
			}

			if ( ! empty( $tax_query ) ) {
				$query['tax_query'] = $tax_query;
			}

			$a_z_query = new Query( $query, 'posts' );
		}

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

		do_action( '_a_z_listing_shortcode_end', $attributes );

		return $ret;
	}
}
