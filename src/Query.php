<?php
/**
 * A-Z Listing main process
 *
 * @package  a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main A-Z Query class
 *
 * @since 0.1
 */
class Query {
	/**
	 * The taxonomy
	 *
	 * @var string|array
	 */
	private $taxonomy;

	/**
	 * Listing type, posts or terms
	 *
	 * @var string
	 */
	private $type = 'posts';

	/**
	 * All available characters in a single string for translation support
	 *
	 * @var \A_Z_Listing\Alphabet
	 */
	private $alphabet;

	/**
	 * All items returned by the query
	 *
	 * @var array
	 */
	private $items;

	/**
	 * Indices for only the items returned by the query - filtered version of $alphabet_chars
	 *
	 * @var array
	 */
	private $matched_item_indices;

	/**
	 * The current item for use in the a-z items loop. internal use only
	 *
	 * @var array
	 */
	private $current_item = null;

	/**
	 * The current item array-index in $items. internal use only
	 *
	 * @var int
	 */
	private $current_item_index = 0;

	/**
	 * The current letter for use in the a-z letter loop. internal use only
	 *
	 * @var array
	 */
	private $current_letter_items = array();

	/**
	 * The current letter array-index in $matched_item_indices. internal use only
	 *
	 * @var int
	 */
	private $current_letter_index = 0;

	/**
	 * The query for this instance of the A-Z Listing
	 *
	 * @var \WP_Query|array
	 */
	private $query;

	/**
	 * A_Z_Listing constructor
	 *
	 * @since 0.1
	 * @since 1.9.2 Instantiate the \WP_Query object here instead of in `A_Z_Listing::construct_query()`
	 * @since 2.0.0 add $type and $use_cache parameters
	 * @param null|\WP_Query|array|string $query     A \WP_Query-compatible query definition or a taxonomy name.
	 * @param string                      $type      Specify the listing type; either 'posts' or 'terms'.
	 * @param bool                     $use_cache Cache the Listing via WordPress transients.
	 */
	public function __construct( $query = null, string $type = 'posts', bool $use_cache = true ) {
		global $post;
		$this->alphabet = new \A_Z_Listing\Alphabet();

		if ( is_string( $query ) && ! empty( $query ) ) {
			$type = 'terms';
		}

		if ( 'terms' === $type ) {
			if ( AZLISTINGLOG ) {
				do_action( 'log', 'A-Z Listing: Setting taxonomy mode', $query );
			}

			$this->type = 'terms';

			$defaults = array(
				'hide_empty' => false,
			);

			if ( is_array( $query ) ) {
				$query = wp_parse_args( $query, $defaults );
			} elseif ( is_string( $query ) ) {
				$taxonomies = explode( ',', $query );
				$taxonomies = array_unique( array_filter( array_map( 'trim', $taxonomies ) ) );

				$query = wp_parse_args(
					array(
						'taxonomy' => (array) $taxonomies,
					),
					$defaults
				);
			}

			/**
			 * Modify or replace the query
			 *
			 * @since 1.0.0
			 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
			 * @param array|Object|\WP_Query  $query  The query object
			 * @param string  $type  The type of the query. Either 'posts' or 'terms'.
			 */
			$query = apply_filters( 'a_z_listing_query', $query, 'terms' );

			/**
			 * Modify or replace the query
			 *
			 * @since 1.7.1
			 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
			 * @param array|Object|\WP_Query  $query  The query object
			 * @param string  $type  The type of the query. Either 'posts' or 'terms'.
			 */
			$query = apply_filters( 'a-z-listing-query', $query, 'terms' );

			if ( is_object( $query ) ) {
				$query = (array) $query;
			}
			$this->taxonomy = $query['taxonomy'];

			if ( $this->check_cache( $query, $type, $use_cache ) ) {
				return $this;
			}

			$items       = get_terms( $query );
			$this->query = $query;

			if ( AZLISTINGLOG ) {
				do_action( 'log', 'A-Z Listing: Terms', '!ID', $items );
			}
		} else {
			if ( AZLISTINGLOG ) {
				do_action( 'log', 'A-Z Listing: Setting posts mode', $query );
			}

			$this->type = 'posts';

			if ( ! $query ) {
				$query = array();
			}

			/**
			 * Modify or replace the query
			 *
			 * @since 1.0.0
			 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
			 * @param array|Object|\WP_Query $query The query object
			 */
			$query = apply_filters( 'a_z_listing_query', $query );

			/**
			 * Modify or replace the query
			 *
			 * @since 1.7.1
			 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
			 * @param array|Object|\WP_Query $query The query object
			 */
			$query = apply_filters( 'a-z-listing-query', $query );

			if ( ! $query instanceof \WP_Query ) {
				$query = (array) $query;

				if ( isset( $query['post_type'] ) ) {
					if ( is_array( $query['post_type'] ) && count( $query['post_type'] ) === 1 ) {
						$query['post_type'] = array_shift( $query['post_type'] );
					}
				}

				if ( ! isset( $query['post_parent'] ) && ! isset( $query['child_of'] ) ) {
					if ( isset( $query['post_type'] ) && isset( $post ) ) {
						if ( 'page' === $query['post_type'] && 'page' === $post->post_type ) {
							$section = self::get_section();
							if ( $section && $section instanceof \WP_Post ) {
								$query['child_of'] = $section->ID;
							}
						}
					}
				}

				$query = wp_parse_args(
					$query,
					array(
						'post_type'   => 'page',
						'numberposts' => -1,
						'nopaging'    => true,
					)
				);
			}

			if ( $this->check_cache( (array) $query, $type, $use_cache ) ) {
				return $this;
			}

			if ( $query instanceof \WP_Query ) {
				$items       = $query->posts;
				$this->query = $query;
			} else {
				add_filter( 'posts_fields', array( $this, 'wp_query_fields' ), 10, 2 );
				if ( isset( $query['child_of'] ) ) {
					$items       = get_pages( $query );
					$this->query = $query;
				} else {
					$wq          = new \WP_Query( $query );
					$items       = $wq->posts;
					$this->query = $wq;
				}
				remove_filter( 'posts_fields', array( $this, 'wp_query_fields' ), 10, 2 );
			}

			if ( AZLISTINGLOG ) {
				do_action( 'log', 'A-Z Listing: Posts', '!ID', $items );
			}
		} // End if ( type is terms ).

		/**
		 * Filter items from the query results
		 *
		 * @param array  $items The query results.
		 * @param string $type  The query type - terms or posts.
		 * @param array  $query The query as an array.
		 */
		$items = apply_filters( 'a-z-listing-filter-items', $items, $type, (array) $query );

		$this->matched_item_indices = $this->get_all_indices( $items );

		if ( $use_cache ) {
			do_action( 'a_z_listing_save_cache', $query, $type, $this->matched_item_indices );
		}
	}

	/**
	 * Set the fields we require on \WP_Query.
	 *
	 * @since 3.0.0 Introduced.
	 * @param string    $fields The current fields in SQL format.
	 * @param \WP_Query $query The \WP_Query object.
	 * @return string The new fields in SQL format.
	 */
	public function wp_query_fields( string $fields, \WP_Query $query ): string {
		global $wpdb;
		return "{$wpdb->posts}.ID, {$wpdb->posts}.post_title, {$wpdb->posts}.post_type, {$wpdb->posts}.post_name, {$wpdb->posts}.post_parent, {$wpdb->posts}.post_date";
	}

	/**
	 * Check for cached queries
	 *
	 * @since 2.0.0
	 * @param array   $query     the query.
	 * @param string  $type      the type of query.
	 * @param bool    $use_cache whether to check the cache.
	 * @return bool whether we found a cached query
	 */
	private function check_cache( array $query, string $type, bool $use_cache ): bool {
		if ( $use_cache ) {
			/**
			 * Get the cached data
			 *
			 * @since 1.0.0
			 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
			 * @param array  $items  The items from previous cache modules.
			 * @param array  $query  The query.
			 * @param string  $type  The type of the query. Either 'posts' or 'terms'.
			 */
			$cached = apply_filters( 'a_z_listing_get_cached_query', array(), (array) $query, $type );
			if ( ! empty( $cached ) ) {
				$this->matched_item_indices = $cached;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Find a post's parent post. Will return the original post if the post-type is not hierarchical or the post does not have a parent.
	 *
	 * @since 1.4.0
	 * @param \WP_Post|int $page The post whose parent we want to find.
	 * @return \WP_Post|bool The parent post or the original post if no parents were found. Will be false if the function is called with incorrect arguments.
	 */
	public static function find_post_parent( $page ) {
		if ( ! $page ) {
			return false;
		}
		if ( ! $page instanceof \WP_Post ) {
			$page = get_post( $page );
		}
		if ( ! $page->post_parent ) {
			return $page;
		}
		return self::find_post_parent( $page->post_parent );
	}

	/**
	 * Calculate the top-level section of the requested page
	 *
	 * @since 0.1
	 * @param \WP_Post|int $page Optional: The post object, or post-ID, of the page whose section we want to find.
	 * @return \WP_Post|null The post object of the current section's top-level page.
	 */
	protected static function get_section( $page = 0 ) {
		global $post;

		$pages = get_pages(
			array(
				'parent' => 0,
			)
		);

		$sections = array_map(
			function( $item ) {
					return $item->post_name;
			},
			$pages
		);
		/**
		 * Override the detected top-level sections for the site. Defaults to contain each page with no post-parent.
		 *
		 * @deprecated Use a_z_listing_sections
		 * @see a_z_listing_sections
		 */
		$sections = apply_filters_deprecated( 'az_sections', array( $sections ), '1.0.0', 'a_z_listing_sections' );
		/**
		 * Override the detected top-level sections for the site. Defaults to contain each page with no post-parent.
		 *
		 * @param array $sections The sections for the site.
		 */
		$sections = apply_filters( 'a_z_listing_sections', $sections );
		/**
		 * Override the detected top-level sections for the site. Defaults to contain each page with no post-parent.
		 *
		 * @since 1.7.1
		 * @param array $sections The sections for the site.
		 */
		$sections = apply_filters( 'a-z-listing-sections', $sections );

		if ( ! $page ) {
			$page = $post;
		}
		if ( is_int( $page ) ) {
			$page = get_post( $page );
		}

		$section_object = self::find_post_parent( $page );
		$section_name   = null;
		if ( $section_object === $page ) {
			$section_object = null;
		} elseif ( null !== $section_object ) {
			if ( isset( $section_object->post_name ) ) {
				$section_name = $section_object->post_name;
			} else {
				$section_name   = null;
				$section_object = null;
			}
		}

		if ( AZLISTINGLOG ) {
			do_action( 'log', 'A-Z Listing: Section selection', $section_name, $sections );
		}

		if ( null !== $section_name && ! in_array( $section_name, $sections, true ) ) {
			$section_name   = null;
			$section_object = null;
		}

		if ( AZLISTINGLOG ) {
			do_action( 'log', 'A-Z Listing: Proceeding with section', $section_name );
		}
		return $section_object;
	}

	/**
	 * Fetch the query we are currently using
	 *
	 * @since 1.0.0
	 * @return \WP_Query The query object
	 */
	public function get_the_query() {
		return $this->query;
	}

	/**
	 * Reducer used by get_the_item_indices() to filter the indices for each post to unique array_values (see: https://secure.php.net/array_reduce)
	 *
	 * @param array $carry Holds the return value of the previous iteration.
	 * @param array $value  Holds the value of the current iteration.
	 * @return array The previous iteration return value with the current iteration added after running through array_unique()
	 */
	public function index_reduce( $carry, $value ) {
		$v = array_unique( $value );
		if ( ! empty( $v ) ) {
			$carry[] = $v;
		}
		return $carry;
	}

	/**
	 * Sort the letters to be used as indices and return as an Array
	 *
	 * @since 0.1
	 * @param array $items The items to index.
	 * @return array The index letters
	 */
	protected function get_all_indices( array $items = [] ): array {
		$indexed_items = array();

		if ( ! is_array( $items ) || empty( $items ) ) {
			$items = $this->items;
		}

		if ( is_array( $items ) && ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$item_indices = apply_filters( '_a-z-listing-extract-item-indices', array(), $item, $this->type );

				if ( empty( $item_indices ) ) {
					continue;
				}

				foreach ( $item_indices as $index => $index_entries ) {
					if ( ! empty( $index_entries ) ) {
						$index = $this->alphabet->get_letter( $index );

						if ( ! isset( $indexed_items[ $index ] ) || ! is_array( $indexed_items[ $index ] ) ) {
							$indexed_items[ $index ] = array();
						}
						$indexed_items[ $index ] = array_merge_recursive( $indexed_items[ $index ], $index_entries );
					}
				}
			}

			$this->alphabet->loop(
				array_key_exists( $this->alphabet->unknown(), $indexed_items ),
				function( string $character ) use ( $indexed_items ) {
					if ( ! empty( $indexed_items[ $character ] ) ) {
						usort(
							$indexed_items[ $character ],
							function ( $a, $b ) {
								$atitle = strtolower( $a['title'] );
								$btitle = strtolower( $b['title'] );
	
								$default_sort = strcmp( $atitle, $btitle );
	
								/**
								 * Compare two titles to determine sorting order.
								 *
								 * @since 3.1.0
								 * @param int The previous order preference: -1 if $a is less than $b. 1 if $a is greater than $b. 0 if they are identical.
								 * @param string $a The first title. Converted to lower case.
								 * @param string $b The second title. Converted to lower case.
								 * @return int The new order preference: -1 if $a is less than $b. 1 if $a is greater than $b. 0 if they are identical.
								 */
								$sort = apply_filters(
									'a_z_listing_item_sorting_comparator',
									$default_sort,
									$atitle,
									$btitle
								);
	
								if ( is_int( $sort ) ) {
									if ( AZLISTINGLOG ) {
										do_action( 'log', 'A-Z Listing: value returned from `a_z_listing_item_sorting_comparator` filter sorting was not an integer', $sort, $atitle, $btitle );
									}
									return $sort;
								}
	
								return $default_sort;
							}
						);
					}
				}
			);
		}

		return $indexed_items;
	}

	/**
	 * Print the letter links HTML
	 *
	 * @since 1.0.0
	 * @param string $target The page to point links toward.
	 * @param string $style CSS classes to apply to the output.
	 */
	public function the_letters( string $target = '', string $style = null ) {
		echo $this->get_the_letters( $target, $style ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Print the letter links HTML
	 *
	 * @since 0.1
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::get_the_letters()
	 * @deprecated use A_Z_Listing::get_the_letters().
	 * @param string $target The page to point links toward.
	 * @param string $style CSS classes to apply to the output.
	 * @return string The letter links HTML
	 */
	public function get_letter_display( $target = '', $style = null ) {
		_deprecated_function( __METHOD__, '1.0.0', 'A_Z_Listing::get_the_letters' );
		return $this->get_the_letters( $target, $style );
	}

	/**
	 * Retrieve the letter links HTML
	 *
	 * @since 1.0.0
	 * @param string $target The page to point links toward.
	 * @param string $style CSS classes to apply to the output.
	 * @return string The letter links HTML
	 */
	public function get_the_letters( string $target = '', string $style = null ): string {
		$classes = array( 'az-links' );
		if ( null !== $style ) {
			if ( is_array( $style ) ) {
				$classes = array_merge( $classes, $style );
			} elseif ( is_string( $style ) ) {
				$c       = explode( ' ', $style );
				$classes = array_merge( $classes, $c );
			}
		}
		$classes = array_unique( $classes );

		$that     = $this;
		$alphabet = $this->alphabet;
		$indices  = $this->matched_item_indices;
		$i        = 0;
		$ret      = '<ul class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		$this->alphabet->loop(
			array_key_exists( $this->alphabet->unknown(), $this->matched_item_indices ),
			function( string $character, $key, int $count ) use ( $that, $alphabet, $indices, $i, $ret ) {
				$i++;
				$id = $character;
				if ( $alphabet->unknown_letter() === $id ) {
					$id = '_';
				}
	
				$classes = [];
				if ( 1 === $i ) {
					$classes[] = 'first';
				} elseif ( $count === $i ) {
					$classes[] = 'last';
				}
	
				if ( 0 === $i % 2 ) {
					$classes[] = 'even';
				} else {
					$classes[] = 'odd';
				}
	
				if ( ! empty( $indices[ $character ] ) ) {
					$classes[] = 'has-posts';
				} else {
					$classes[] = 'no-posts';
				}
	
				$ret .= '<li class="' . esc_attr( implode( ' ', $classes ) ) . '">';
				if ( ! empty( $indices[ $character ] ) ) {
					$ret .= '<a href="' . esc_url( $target . '#letter-' . $id ) . '">';
				}
				$ret .= '<span>' . esc_html( $that->get_the_letter_title( $character ) ) . '</span>';
				if ( ! empty( $indices[ $character ] ) ) {
					$ret .= '</a>';
				}
				$ret .= '</li>';
			}
		);

		$ret .= '</ul>';
		return $ret;
	}

	/**
	 * Print the index list HTML created by a theme template
	 *
	 * @since 2.0.0
	 */
	public function the_listing() {
		global $post;
		if ( 'terms' === $this->type ) {
			if ( is_array( $this->taxonomy ) ) {
				$section = join( '_', $this->taxonomy );
			} else {
				$section = $this->taxonomy;
			}
		} else {
			$section = self::get_section();
			if ( $section instanceof \WP_Post ) {
				$section = $section->post_name;
			}
		}

		$templates = array(
			'a-z-listing-' . $section . '.php',
			'a-z-listing.php',
		);

		if ( $post ) {
			array_unshift(
				$templates,
				'a-z-listing-' . $post->post_name . '.php'
			);
		}

		$template = locate_template( $templates );
		if ( $template ) {
			_do_template( $this, $template );
		} else {
			_do_template( $this, plugin_dir_path( __DIR__ ) . 'templates/a-z-listing.php' );
		}
		\wp_reset_postdata();
	}

	/**
	 * Retrieve the index list HTML created by a theme template
	 *
	 * @since 0.7
	 * @return string The index list HTML.
	 */
	public function get_the_listing(): string {
		ob_start();
		$this->the_listing();
		$r = ob_get_clean();

		return $r;
	}

	/**
	 * Used by theme templates. Returns true when we still have letters to iterate.
	 *
	 * @since 0.7
	 * @see A_Z_Listing::have_letters()
	 * @deprecated use A_Z_Listing::have_letters()
	 */
	public function have_a_z_letters() {
		_deprecated_function( __METHOD__, '1.0.0', 'A_Z_Listing::have_letters' );
		return $this->have_letters();
	}

	/**
	 * Used by theme templates. Returns true when we still have letters to iterate.
	 *
	 * @since 1.0.0
	 * @return bool True if we have more letters to iterate, otherwise false
	 */
	public function have_letters(): bool {
		return ( $this->num_letters > $this->current_letter_index );
	}

	/**
	 * Used by theme templates. Returns true when we still have posts to iterate.
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::have_items()
	 * @deprecated use A_Z_Listing::have_items()
	 */
	public function have_a_z_posts() {
		_deprecated_function( __METHOD__, '1.0.0', 'have_items' );
		return $this->have_items();
	}

	/**
	 * Used by theme templates. Returns true when we still have items/posts within the current letter.
	 *
	 * To advance the letter use A_Z_Listing::the_letter()
	 * To advance the item/post use A_Z_Listing::the_item()
	 *
	 * @since 1.0.0
	 * @return bool True if there are posts left to iterate within the current letter, otherwise false.
	 */
	public function have_items(): bool {
		return is_array( $this->current_letter_items ) &&
			$this->get_the_letter_count() > $this->current_item_index;
	}

	/**
	 * Advance the Letter Loop onto the next letter
	 *
	 * @since 1.0.0
	 */
	public function the_letter() {
		$this->current_item_index   = 0;
		$this->current_letter_items = array();
		$letter = $this->alphabet->chars[ $this->current_letter_index ];
		if ( isset( $this->matched_item_indices[ $letter ] ) ) {
			$this->current_letter_items = $this->matched_item_indices[ $letter ];
		}
		$this->current_letter_index += 1;
	}

	/**
	 * Advance the Post loop within the Letter Loop onto the next post
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::the_item()
	 * @deprecated use A_Z_Listing::the_item()
	 */
	public function the_a_z_post() {
		_deprecated_function( __METHOD__, '1.0.0', 'A_Z_Listing::the_item' );
		$this->the_item();
	}

	/**
	 * Advance the Post loop within the Letter Loop onto the next post
	 *
	 * @since 1.0.0
	 */
	public function the_item() {
		$this->current_item        = $this->current_letter_items[ $this->current_item_index ];
		$this->current_item_index += 1;
	}

	/**
	 * Retrieve the item object for the current post
	 *
	 * @since 2.0.0
	 * @param string $force Set this to 'I understand the issues!' to acknowledge that this function will cause slowness on large sites.
	 * @return array|\WP_Error|\WP_Post|\WP_Term
	 */
	public function get_the_item_object( string $force = '' ) {
		global $post;
		if ( 'I understand the issues!' === $force ) {
			$current_item = $this->current_item['item'];
			if ( is_string( $current_item ) ) {
				$item = explode( ':', $current_item, 2 );

				if ( isset( $item[1] ) ) {
					if ( 'term' === $item[0] ) {
						return get_term( $item[1] );
					}
					if ( 'post' === $item[0] ) {
						$post = get_post( $item[1] );
						setup_postdata( $post );
						return $post;
					}
				}
			}
			if ( $current_item instanceof \WP_Post ) {
				$post = $current_item;
				setup_postdata( $post );
				return $post;
			}
			if ( $current_item instanceof \WP_Term ) {
				return get_term( $current_item );
			}
			return $current_item;
		} else {
			return new \WP_Error( 'understanding', 'You must tell the plugin "I understand the issues!" when calling get_the_item_object().' );
		}
	}

	/**
	 * Retrieve meta field for an item.
	 *
	 * @since 2.1.0
	 * @param string $key The meta key to retrieve. By default returns data for all keys.
	 * @param bool   $single Whether to return a single value.
	 * @return mixed|\WP_Error Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	function get_item_meta( string $key = '', bool $single = false ) {
		if ( is_string( $this->current_item['item'] ) ) {
			$item = explode( ':', $this->current_item['item'], 2 );

			if ( 'term' === $item[0] ) {
				return get_term_meta( $item[1], $key, $single );
			}
			if ( 'post' === $item[0] ) {
				return get_post_meta( $item[1], $key, $single );
			}
		} elseif ( $this->current_item['item'] instanceof \WP_Term ) {
			return get_term_meta( $this->current_item['item']->term_id, $key, $single );
		} elseif ( $this->current_item['item'] instanceof \WP_Post ) {
			return get_post_meta( $this->current_item['item']->ID, $key, $single );
		} else {
			return new \WP_Error( 'no-type', 'Unknown item type.' );
		}
	}

	/**
	 * Print the number of posts assigned to the current term
	 *
	 * @since 2.2.0
	 */
	function the_item_post_count() {
		echo esc_html( $this->get_the_item_post_count() );
	}

	/**
	 * Retrieve the number of posts assigned to the current term
	 *
	 * @since 2.2.0
	 * @return int The number of posts
	 */
	function get_the_item_post_count(): int {
		if ( is_string( $this->current_item['item'] ) ) {
			$item = explode( ':', $this->current_item['item'], 2 );
			$term = null;
			if ( 'term' === $item[0] ) {
				$term = get_term( $item[1] );
				if ( $term ) {
					return $term->count;
				}
			}
		}
		if ( $this->current_item['item'] instanceof \WP_Term ) {
			$term = get_term( $this->current_item['item'] );
			if ( $term ) {
				return $term->count;
			}
		}
		return 0;
	}

	/**
	 * Retrieve the number of letters in the loaded alphabet
	 *
	 * @since 1.0.0
	 * @return int The number of letters
	 */
	public function num_letters() {
		return $this->alphabet->count( array_key_exists( $this->alphabet->unknown(), $this->matched_item_indices ) );;
	}

	/**
	 * Retrieve the number of posts within the current letter
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::get_the_letter_count()
	 * @deprecated use A_Z_Listing::get_the_letter_count()
	 */
	public function num_a_z_posts() {
		_deprecated_function( __METHOD__, '1.0.0', 'A_Z_Listing::get_the_letter_count' );
		return $this->get_the_letter_count();
	}

	/**
	 * Retrieve the number of posts within the current letter
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::get_the_letter_count()
	 * @deprecated use A_Z_Listing::get_the_letter_count()
	 */
	public function num_a_z_items() {
		_deprecated_function( __METHOD__, '1.0.0', 'A_Z_Listing::get_the_letter_count' );
		return $this->get_the_letter_count();
	}

	/**
	 * Print the number of posts within the current letter
	 *
	 * @since 1.0.0
	 */
	public function the_letter_count() {
		echo esc_html( $this->get_the_letter_count() );
	}

	/**
	 * Retrieve the number of posts within the current letter
	 *
	 * @since 1.0.0
	 * @return int The number of posts
	 */
	public function get_the_letter_count(): int {
		return count( $this->current_letter_items );
	}

	/**
	 * Print the escaped ID of the current letter.
	 *
	 * @since 0.7
	 */
	public function the_letter_id() {
		echo esc_attr( $this->get_the_letter_id() );
	}

	/**
	 * Retrieve the ID of the current letter. This is not escaped!
	 *
	 * @since 0.7
	 * @return string The letter ID
	 */
	public function get_the_letter_id(): string {
		$id = $this->alphabet[ $this->alphabet_chars[ $this->current_letter_index - 1 ] ];
		if ( $this->unknown_letters === $id ) {
			$id = '_';
		}
		return 'letter-' . $id;
	}

	/**
	 * Print the escaped ID of the current item.
	 *
	 * @since 2.4.0
	 */
	public function the_item_id() {
		echo esc_attr( $this->get_the_item_id() );
	}

	/**
	 * Retreive the ID of the current item. This is not escaped!
	 *
	 * @since 2.4.0
	 * @return int The item ID.
	 */
	public function get_the_item_id(): int {
		$current_item = $this->current_item['item'];
		if ( is_string( $current_item ) ) {
			$item = explode( ':', $current_item, 2 );

			if ( isset( $item[1] ) ) {
				return $item[1];
			}
		} elseif ( $current_item instanceof \WP_Post ) {
			return $current_item->ID;
		} elseif ( $current_item instanceof \WP_Term ) {
			return $current_item->term_id;
		} else {
			return $current_item;
		}
	}

	/**
	 * Retreive the type of the current item.
	 *
	 * @since 2.4.0
	 * @return string|\WP_Error The type of the current item. Either `post` or `term`. Will return a \WP_Error object if the type of the current item cannot be determined.
	 */
	public function get_the_item_type() {
		$current_item = $this->current_item['item'];
		if ( $current_item instanceof \WP_Post ) {
			return 'post';
		} elseif ( $current_item instanceof \WP_Term ) {
			return 'term';
		} elseif ( is_string( $current_item ) ) {
			$item = explode( ':', $current_item, 2 );
			if ( isset( $item[0] ) && in_array( $item[0], array( 'post', 'term' ), true ) ) {
				return $item[0];
			}
		}
		if ( in_array( $this->type, array( 'terms', 'posts' ), true ) ) {
			return 'terms' === $this->type ? 'term' : 'post';
		}

		return new \WP_Error( 'no-type', 'Unknown item type.' );
	}

	/**
	 * Print the escaped title of the current letter. For example, upper-case A or B or C etc.
	 *
	 * @since 0.7
	 * @param string $index The index for which to print the title.
	 */
	public function the_letter_title( string $index = '' ) {
		echo esc_html( $this->get_the_letter_title( $index ) );
	}

	/**
	 * Retrieve the title of the current letter. For example, upper-case A or B or C etc. This is not escaped!
	 *
	 * @since 0.7
	 * @since 1.8.0 Add filters to modify the title of the letter.
	 * @param string $index The index for which to return the title.
	 * @return string The letter title
	 */
	public function get_the_letter_title( string $index = '' ): string {
		if ( '' !== $index ) {
			if ( isset( $this->alphabet[ $index ] ) ) {
				$letter = $this->alphabet[ $index ];
			} else {
				$letter = $index;
			}
		} else {
			$letter = $this->alphabet[ $this->alphabet->chars[ $this->current_letter_index - 1 ] ];
		}

		/**
		 * Modify the letter title or heading
		 *
		 * @since 1.8.0
		 * @param string $letter The title of the letter.
		 */
		$letter = apply_filters( 'the_a_z_letter_title', $letter );
		/**
		 * Modify the letter title or heading
		 *
		 * @since 1.8.0
		 * @param string $letter The title of the letter.
		 */
		$letter = apply_filters( 'the-a-z-letter-title', $letter );

		return $letter;
	}

	/**
	 * Print the escaped title of the current post
	 *
	 * @since 1.0.0
	 */
	public function the_title() {
		// to match core we do NOT escape the output!
		echo $this->get_the_title(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Retrieve the title of the current post. This is not escaped!
	 *
	 * @since 1.0.0
	 * @return string The post title
	 */
	public function get_the_title(): string {
		$title = $this->current_item['title'];
		if ( is_string( $this->current_item['item'] ) ) {
			$item = explode( ':', $this->current_item['item'], 2 );
		} else {
			$item = $this->current_item['item'];
		}

		if ( is_array( $item ) ) {
			if ( 'post' === $item[0] ) {
				return apply_filters( 'the_title', $title, $item[1] );
			}
			if ( 'term' === $item[0] ) {
				return apply_filters( 'term_name', $title, $item[1] );
			}
		} else {
			if ( $item instanceof \WP_Post ) {
				return apply_filters( 'the_title', $title, $item->ID );
			}
			if ( $item instanceof \WP_Term ) {
				return apply_filters( 'term_name', $title, $item->term_id );
			}
		}

		return $title;
	}

	/**
	 * Print the escaped permalink of the current post.
	 *
	 * @since 1.0.0
	 */
	public function the_permalink() {
		echo esc_url( $this->get_the_permalink() );
	}

	/**
	 * Retrieve the permalink of the current post. This is not escaped!
	 *
	 * @since 1.0.0
	 * @return string The permalink
	 */
	public function get_the_permalink(): string {
		return $this->current_item['link'];
	}
}

/**
 * Load and execute a theme template
 *
 * @since 2.1.0
 * @param \A_Z_Listing\Query $a_z_query The Query object.
 * @param string    $template_file The path of the template to execute.
 */
function _do_template( \A_Z_Listing\Query $a_z_query, string $template_file ) {
	require $template_file;
}
