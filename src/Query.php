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

define( 'DEFAULT_A_Z_TEMPLATE', plugin_dir_path( dirname( __DIR__ ) ) . 'templates/a-z-listing.php' );

/**
 * The main A-Z Query class
 *
 * @since 0.1
 */
class Query {
	/**
	 * The taxonomy
	 *
	 * @var string|array<int,string>
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
	 * @var Alphabet
	 */
	private $alphabet;

	/**
	 * All items returned by the query
	 *
	 * @var array<int,mixed>
	 */
	private $items;

	/**
	 * Indices for only the items returned by the query - filtered version of $alphabet_chars
	 *
	 * @var array<string,mixed>
	 */
	private $matched_item_indices;

	/**
	 * The current item for use in the a-z items loop. internal use only
	 *
	 * @var array<string,\WP_Post>|array<string,\WP_Term>|array<string,string>
	 */
	private $current_item = null;

	/**
	 * The current item array-index in $items. internal use only
	 *
	 * @var int
	 */
	private $current_item_offset = 0;

	/**
	 * The items for the current letter. Used internally for the items loop.
	 *
	 * @var array<int,mixed>
	 */
	private $current_letter_items = array();

	/**
	 * The current letter array-index in $matched_item_indices. internal use only
	 *
	 * @var int
	 */
	private $current_letter_offset = 0;

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
	 * @since 1.9.2 Instantiate the WP_Query object here instead of in `A_Z_Listing::construct_query()`
	 * @since 2.0.0 add $type and $use_cache parameters
	 * @param null|\WP_Query|array|string $query     A \WP_Query-compatible query definition or a taxonomy name.
	 * @param string                      $type      Specify the listing type; either 'posts' or 'terms'.
	 * @param bool                        $use_cache Cache the Listing via WordPress transients.
	 */
	public function __construct( $query = null, string $type = 'posts', bool $use_cache = true ) {
		global $post;
		$this->alphabet = new Alphabet();

		if ( 'terms' === $type || ( is_string( $query ) && ! empty( $query ) ) ) {
			if ( defined( 'AZLISTINGLOG' ) && AZLISTINGLOG ) {
				do_action( 'log', 'A-Z Listing: Setting taxonomy mode', $query );
			}

			$this->type = 'terms';

			if ( is_string( $query ) ) {
				$taxonomies = explode( ',', $query );
				$taxonomies = array_unique( array_filter( array_map( 'trim', $taxonomies ) ) );

				$query['taxonomy'] = (array) $taxonomies;
			}

			/**
			 * Modify or replace the query
			 *
			 * @since 1.0.0
			 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
			 * @param array|Object|\WP_Query  $query  The query object
			 * @param string  $type  The type of the query. Either 'posts' or 'terms'.
			 */
			$query = (array) apply_filters( 'a_z_listing_query', (array) $query, 'terms' );

			/**
			 * Modify or replace the query
			 *
			 * @since 1.7.1
			 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
			 * @param array|Object|\WP_Query  $query  The query object
			 * @param string  $type  The type of the query. Either 'posts' or 'terms'.
			 */
			$query = (array) apply_filters( 'a-z-listing-query', (array) $query, 'terms' );

			$query = wp_parse_args(
				(array) $query,
				array(
					'hide_empty' => false,
					'taxonomy'   => 'category',
				)
			);

			$this->taxonomy = $query['taxonomy'];

			if ( $this->check_cache( $query, $type, $use_cache ) ) {
				return;
			}

			$items       = get_terms( $query ); // @phan-suppress-current-line PhanAccessMethodInternal
			$this->query = $query;

			if ( defined( 'AZLISTINGLOG' ) && AZLISTINGLOG ) {
				\do_action( 'log', 'A-Z Listing: Terms', '!ID', $items );
			}
		} else {
			if ( defined( 'AZLISTINGLOG' ) && AZLISTINGLOG ) {
				\do_action( 'log', 'A-Z Listing: Setting posts mode', $query );
			}

			$this->type = 'posts';

			if ( empty( $query ) ) {
				$query = array();
			}

			$query = apply_filters( 'a_z_listing_query', $query, 'posts' );
			$query = apply_filters( 'a-z-listing-query', $query, 'posts' );

			if ( ! $query instanceof \WP_Query ) {
				$query = (array) $query;

				if ( isset( $query['post_type'] ) ) {
					if ( is_array( $query['post_type'] ) && count( $query['post_type'] ) === 1 ) {
						$post_type          = array_shift( $query['post_type'] );
						$query['post_type'] = $post_type;
						unset( $post_type );
					}
				}

				if ( ! isset( $query['post_parent'] ) && ! isset( $query['child_of'] ) ) {
					if ( isset( $query['post_type'] ) && isset( $post ) ) {
						if ( 'page' === $query['post_type'] && 'page' === $post->post_type ) {
							$section = self::get_section();
							if ( $section && $section instanceof \WP_Post ) {
								$query['child_of'] = $section->ID;
							}
							unset( $section );
						}
					}
				}

				$query = \wp_parse_args( $query, array( 'post_type' => 'page' ) );
			}

			if ( $this->check_cache( (array) $query, $type, $use_cache ) ) {
				return;
			}

			if ( $query instanceof \WP_Query ) {
				$this->query = $query;
			} else {
				if ( isset( $query['child_of'] ) ) {
					$items       = get_pages( $query );
					$this->query = $query;
				} else {
					$wq          = new \WP_Query( $query );
					$this->query = $wq;
					$items       = $wq->posts;
				}
			}

			if ( defined( 'AZLISTINGLOG' ) && AZLISTINGLOG ) {
				\do_action( 'log', 'A-Z Listing: Posts', '!ID', $items );
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
			do_action( 'a_z_listing_save_cache', (array) $query, $type, $this->matched_item_indices );
		}
	}

	/**
	 * Set the fields we require on \WP_Query.
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Converted to static function.
	 * @param string    $fields The current fields in SQL format.
	 * @param \WP_Query $query  The \WP_Query instance.
	 * @return string The new fields in SQL format.
	 */
	public static function wp_query_fields( string $fields, \WP_Query $query ): string {
		global $wpdb;
		return "{$wpdb->posts}.ID, {$wpdb->posts}.post_title, {$wpdb->posts}.post_type, {$wpdb->posts}.post_name, {$wpdb->posts}.post_parent, {$wpdb->posts}.post_date";
	}

	/**
	 * Tell WP_Query to split the query.
	 *
	 * @since 4.0.0
	 * @param bool      $split_the_query Whether or not to split the query.
	 * @param \WP_Query $query           The \WP_Query instance.
	 */
	public static function split_the_query( bool $split_the_query, \WP_Query $query ): bool {
		return true;
	}

	/**
	 * Check for cached queries
	 *
	 * @since 2.0.0
	 * @param array<string,string> $query     the query.
	 * @param string               $type      the type of query.
	 * @param bool                 $use_cache whether to check the cache.
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
	 * @return \WP_Post|null The parent post or the original post if no parents were found. Will be false if the function is called with incorrect arguments.
	 */
	public static function find_post_parent( $page ) {
		if ( empty( $page ) ) {
			return null;
		}
		if ( ! $page instanceof \WP_Post ) {
			$page = get_post( $page );
		}
		if ( empty( $page->post_parent ) ) {
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

		$pages = \get_pages( array( 'parent' => 0 ) );

		$sections = array_map(
			function( \WP_Post $item ): string {
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

		if ( ! $page instanceof \WP_Post ) {
			$page = get_post( $page );
		}
		if ( ! $page instanceof \WP_Post ) {
			$page = $post;
		}
		if ( ! $page instanceof \WP_Post ) {
			return null;
		}

		$section_object = self::find_post_parent( $page );
		$section_name   = null;
		if ( $section_object === $page ) {
			$section_object = null;
		} elseif ( $section_object instanceof \WP_Post ) {
			$section_name = $section_object->post_name;
		} else {
			$section_name   = null;
			$section_object = null;
		}

		if ( defined( 'AZLISTINGLOG' ) ) {
			\do_action( 'log', 'A-Z Listing: Section selection', $section_name, $sections );
		}

		if ( null !== $section_name && ! in_array( $section_name, $sections, true ) ) {
			$section_name   = null;
			$section_object = null;
		}

		if ( defined( 'AZLISTINGLOG' ) ) {
			\do_action( 'log', 'A-Z Listing: Proceeding with section', $section_name );
		}
		return $section_object;
	}

	/**
	 * Fetch the query we are currently using
	 *
	 * @since 1.0.0
	 * @return \WP_Query The query object
	 */
	public function get_the_query(): \WP_Query {
		return $this->query;
	}

	/**
	 * Reducer used by get_the_item_indices() to filter the indices for each post to unique array_values (see: https://secure.php.net/array_reduce)
	 *
	 * @param array<int,mixed> $carry Holds the return value of the previous iteration.
	 * @param array<int,mixed> $value  Holds the value of the current iteration.
	 * @return array<int,mixed> The previous iteration return value with the current iteration added after running through array_unique()
	 */
	public static function index_reduce( array $carry, array $value ): array {
		$v = array_unique( $value );
		if ( ! empty( $v ) ) {
			$carry[] = $v;
		}
		return $carry;
	}

	/**
	 * Extract an item's indices
	 *
	 * @param \WP_Post|\WP_Term $item The item.
	 * @return array<string,mixed> The indices. This is an associative array of `[ 'index-char' => $item_array ]`.
	 */
	protected function get_all_indices_for_item( $item ) {
		$indexed_items = array();
		$item_indices  = \apply_filters( '_a-z-listing-extract-item-indices', array(), $item, $this->type );

		if ( ! empty( $item_indices ) ) {
			foreach ( $item_indices as $key => $entries ) {
				if ( ! empty( $entries ) ) {
					if ( ! isset( $indexed_items[ $key ] ) || ! is_array( $indexed_items[ $key ] ) ) {
						$indexed_items[ $key ] = array();
					}
					$indexed_items[ $key ] = array_merge_recursive( $indexed_items[ $key ], $entries );
				}
			}
		}

		return $indexed_items;
	}

	/**
	 * Sort the letters to be used as indices and return as an Array
	 *
	 * @since 0.1
	 * @param array<int,mixed> $items The items to index.
	 * @return array<string,mixed> The index letters
	 */
	protected function get_all_indices( array $items = array() ): array {
		global $post;
		$indexed_items = array();

		if ( ! is_array( $items ) || empty( $items ) ) {
			$items = $this->items;
		}

		if ( is_array( $items ) && ! empty( $items ) ) {
			foreach ( $items as $item ) {
				foreach ( $this->get_all_indices_for_item( $item ) as $key => $value ) {
					$indexed_items[ $key ] = $value;
				}
			}
		} elseif ( $this->query instanceof \WP_Query ) {
			$offset         = 0;
			$posts_per_page = $this->query->posts_per_page;
			$found_posts    = $this->query->found_posts;
			while ( $offset < $found_posts ) {
				$this->query->the_post();

				foreach ( $this->get_all_indices_for_item( $post ) as $key => $value ) {
					$indexed_items[ $key ] = $value;
				}

				$offset++;
				if ( 0 === $offset % $posts_per_page ) {
					$q           = $this->query->query;
					$q['offset'] = $offset * $posts_per_page;
					$this->query = new WP_Query( $q );
				}
			}
			wp_reset_postdata();
		}

		$this->alphabet->loop(
			function( string $character ) use ( $indexed_items ) {
				if ( ! empty( $indexed_items[ $character ] ) ) {
					usort(
						$indexed_items[ $character ],
						/**
						 * Closure to sort the indexed items based on their titles
						 *
						 * @param array<string,string> $a
						 * @param array<string,string> $b
						 */
						function ( array $a, array $b ): int {
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
								return $sort;
							}

							if ( defined( 'AZLISTINGLOG' ) && AZLISTINGLOG ) {
								\do_action( 'log', 'A-Z Listing: value returned from `a_z_listing_item_sorting_comparator` filter sorting was not an integer', $sort, $atitle, $btitle );
							}
							return $default_sort;
						}
					);
				}
			},
			array_key_exists( $this->alphabet->get_unknown_letter(), $indexed_items )
		);

		return $indexed_items;
	}

	/**
	 * Print the letter links HTML
	 *
	 * @since 1.0.0
	 * @param string       $target The page to point links toward.
	 * @param string|array $style CSS classes to apply to the output.
	 * @return void
	 */
	public function the_letters( string $target = '', $style = null ) {
		echo $this->get_the_letters( $target, $style ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Print the letter links HTML
	 *
	 * @since 0.1
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::get_the_letters()
	 * @deprecated use A_Z_Listing::get_the_letters().
	 * @param string       $target The page to point links toward.
	 * @param string|array $style CSS classes to apply to the output.
	 * @return string The letter links HTML
	 */
	public function get_letter_display( string $target = '', $style = '' ): string {
		_deprecated_function( __METHOD__, '1.0.0', 'A_Z_Listing::get_the_letters' );
		return $this->get_the_letters( $target, $style );
	}

	/**
	 * Retrieve the letter links HTML
	 *
	 * @since 1.0.0
	 * @param string       $target The page to point links toward.
	 * @param string|array $style CSS classes to apply to the output.
	 * @return string The letter links HTML
	 */
	public function get_the_letters( string $target = '', $style = '' ): string {
		$classes = array( 'az-links' );
		if ( is_array( $style ) ) {
			$classes = array_merge( $classes, $style );
		} elseif ( is_string( $style ) ) {
			$c       = preg_split( '[,\s]', $style );
			$classes = array_merge( $classes, $c );
		}
		$classes = array_unique( array_filter( $classes ) );

		$that     = $this;
		$alphabet = $this->alphabet;
		$indices  = $this->matched_item_indices;
		$i        = 0;
		$ret      = '<ul class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		$this->alphabet->loop(
			/**
			 * Closure to build each letter in the letter view
			 *
			 * @param string     $character
			 * @param int|string $key
			 * @param int        $count
			 * @return void
			 */
			function( string $character, $key, int $count ) use ( $that, $target, $alphabet, $indices, $i, &$ret ) {
				$i++;
				$id = $character;
				if ( $alphabet->get_unknown_letter() === $id ) {
					$id = '_';
				}

				$classes = array();
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
			},
			array_key_exists( $this->alphabet->get_unknown_letter(), $this->matched_item_indices )
		);

		$ret .= '</ul>';
		return $ret;
	}

	/**
	 * Print the index list HTML created by a theme template
	 *
	 * @since 2.0.0
	 * @return void
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
				'a-z-listing-' . $post->post_type . '.php'
			);
			array_unshift(
				$templates,
				'a-z-listing-' . $post->post_type . '.php'
			);
			array_unshift(
				$templates,
				'a-z-listing-' . $post->post_name . '.php'
			);
		}

		$template = locate_template( $templates );
		if ( empty( $template ) ) {
			$template = DEFAULT_A_Z_TEMPLATE;
		}

		_do_template( $this, $template );

		wp_reset_postdata();
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
	 * @return bool True if we have more letters to iterate, otherwise false
	 */
	public function have_a_z_letters(): bool {
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
		return ( $this->num_letters() > $this->current_letter_offset );
	}

	/**
	 * Used by theme templates. Returns true when we still have posts to iterate.
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::have_items()
	 * @deprecated use A_Z_Listing::have_items()
	 * @return bool True if there are posts left to iterate within the current letter, otherwise false.
	 */
	public function have_a_z_posts(): bool {
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
			$this->get_the_letter_items_count() > $this->current_item_offset;
	}

	/**
	 * Advance the Letter Loop onto the next letter
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function the_letter() {
		$this->current_item_offset  = 0;
		$this->current_letter_items = array();
		$key                        = $this->alphabet->get_key_for_offset( $this->current_letter_offset );
		if ( isset( $this->matched_item_indices[ $key ] ) ) {
			$this->current_letter_items = $this->matched_item_indices[ $key ];
		}
		$this->current_letter_offset += 1;
	}

	/**
	 * Advance the Post loop within the Letter Loop onto the next post
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::the_item()
	 * @deprecated use A_Z_Listing::the_item()
	 * @return void
	 */
	public function the_a_z_post() {
		_deprecated_function( __METHOD__, '1.0.0', 'A_Z_Listing::the_item' );
		$this->the_item();
	}

	/**
	 * Advance the Post loop within the Letter Loop onto the next post
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function the_item() {
		$this->current_item         = $this->current_letter_items[ $this->current_item_offset ];
		$this->current_item_offset += 1;
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
						return \get_term( intval( $item[1] ) );
					}
					if ( 'post' === $item[0] ) {
						$post = \get_post( intval( $item[1] ) );
						\setup_postdata( $post );
						return $post;
					}
				}
			} elseif ( $current_item instanceof \WP_Post ) {
				$post = $current_item;
				setup_postdata( $post );
				return $post;
			} elseif ( $current_item instanceof \WP_Term ) {
				return get_term( $current_item );
			} else {
				return $current_item;
			}
		}

		return new WP_Error( 'understanding', 'You must tell the plugin "I understand the issues!" when calling get_the_item_object().' );
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
				return \get_term_meta( intval( $item[1] ), $key, $single );
			} elseif ( 'post' === $item[0] ) {
				return \get_post_meta( intval( $item[1] ), $key, $single );
			}
		} elseif ( $this->current_item['item'] instanceof \WP_Term ) {
			return get_term_meta( $this->current_item['item']->term_id, $key, $single );
		} elseif ( $this->current_item['item'] instanceof \WP_Post ) {
			return get_post_meta( $this->current_item['item']->ID, $key, $single );
		}

		return new WP_Error( 'no-type', 'Unknown item type.' );
	}

	/**
	 * Print the number of posts assigned to the current term
	 *
	 * @since 2.2.0
	 * @return void
	 */
	function the_item_post_count() {
		echo \esc_html( strval( $this->get_the_item_post_count() ) );
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
				$term = \get_term( intval( $item[1] ) );
				if ( ! empty( $term ) ) {
					return $term->count;
				}
			}
		} elseif ( $this->current_item['item'] instanceof \WP_Term ) {
			$term = get_term( $this->current_item['item'] );
			if ( ! empty( $term ) ) {
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
		return $this->alphabet->count( array_key_exists( $this->alphabet->get_unknown_letter(), $this->matched_item_indices ) );
	}

	/**
	 * Retrieve the number of posts within the current letter
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::get_the_letter_items_count()
	 * @deprecated use A_Z_Listing::get_the_letter_items_count()
	 * @return int The number of posts.
	 */
	public function num_a_z_posts(): int {
		_deprecated_function( __METHOD__, '1.0.0', 'A_Z_Listing::get_the_letter_items_count' );
		return $this->get_the_letter_items_count();
	}

	/**
	 * Retrieve the number of posts within the current letter
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see A_Z_Listing::get_the_letter_items_count()
	 * @deprecated use A_Z_Listing::get_the_letter_items_count()
	 * @return int The number of posts.
	 */
	public function num_a_z_items(): int {
		_deprecated_function( __METHOD__, '1.0.0', 'A_Z_Listing::get_the_letter_items_count' );
		return $this->get_the_letter_items_count();
	}

	/**
	 * Print the number of posts within the current letter
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function the_letter_items_count() {
		echo esc_html( strval( $this->get_the_letter_items_count() ) );
	}

	/**
	 * Retrieve the number of posts within the current letter
	 *
	 * @since 1.0.0
	 * @return int The number of posts
	 */
	public function get_the_letter_items_count(): int {
		return count( $this->current_letter_items );
	}

	/**
	 * Print the escaped ID of the current letter.
	 *
	 * @since 0.7
	 * @return void
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
		$key = $this->alphabet->get_key_for_offset( $this->current_letter_offset - 1 );
		$id  = $this->alphabet->get_letter_for_key( $key );
		if ( $this->alphabet->get_unknown_letter() === $id ) {
			$id = '_';
		}
		return 'letter-' . $id;
	}

	/**
	 * Print the escaped ID of the current item.
	 *
	 * @since 2.4.0
	 * @return void
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

			return intval( $item[1] ?? -1 );
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

		return new WP_Error( 'no-type', 'Unknown item type.' );
	}

	/**
	 * Print the escaped title of the current letter. For example, upper-case A or B or C etc.
	 *
	 * @since 0.7
	 * @param string $index The index for which to print the title.
	 * @return void
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
			$letter = $this->alphabet->get_letter_for_key( $index ) ?? $index;
		} else {
			$key    = $this->alphabet->get_key_for_offset( $this->current_letter_offset - 1 );
			$letter = $this->alphabet->get_letter_for_key( $key );
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
	 * @return void
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
			} elseif ( 'term' === $item[0] ) {
				return apply_filters( 'term_name', $title, $item[1] );
			}
		} else {
			if ( $item instanceof \WP_Post ) {
				return apply_filters( 'the_title', $title, $item->ID );
			} elseif ( $item instanceof \WP_Term ) {
				return apply_filters( 'term_name', $title, $item->term_id );
			}
		}

		return $title;
	}

	/**
	 * Print the escaped permalink of the current post.
	 *
	 * @since 1.0.0
	 * @return void
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
 * @param Query $a_z_query The Query object.
 * @return void
 */
function _do_template( Query $a_z_query ) {
	if ( func_get_arg( 1 ) ) {
		require func_get_arg( 1 );
	} else {
		require DEFAULT_A_Z_TEMPLATE;
	}
}
