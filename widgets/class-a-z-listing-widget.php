<?php
/**
 * Definition for the a-z-listing's main widget
 *
 * @package  a-z-listing
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Definition for the AZ_Widget which displays alphabetically-ordered list of latin letters linking to the A-Z Listing page
 *
 * @since 0.1
 */
class A_Z_Listing_Widget extends WP_Widget {
	/**
	 * Register the widget's meta information
	 *
	 * @since 0.1
	 * @param string              $id_base         Optional Base ID for the widget, lowercase and unique. If left empty,
	 *                                             a portion of the widget's class name will be used Has to be unique.
	 * @param string              $name            Name for the widget displayed on the configuration page.
	 * @param array<string,mixed> $widget_options  Optional. Widget options. See wp_register_sidebar_widget() for information
	 *                                             on accepted arguments. Default empty array.
	 * @param array<string,mixed> $control_options Optional. Widget control options. See wp_register_widget_control() for
	 *                                             information on accepted arguments. Default empty array.
	 */
	public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() ) {
		$widget_options['classname']   = $widget_options['classname'] ?? 'a-z-listing-widget';
		$widget_options['description'] = $widget_options['description'] ?? __(
			'Alphabetised links to the A-Z site map',
			'a-z-listing'
		);

		parent::__construct(
			$id_base ?? 'bh_az_widget',
			$name ?? __( 'A-Z Site Map', 'a-z-listing' ),
			$widget_options,
			$control_options
		);

		add_action( 'admin_enqueue_scripts', 'a_z_listing_enqueue_widget_admin_script' );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_if_active' ) );
	}

	/**
	 * Enqueue scripts and stylesheets if the widget is active on the page
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function enqueue_if_active() {
		if ( false !== is_active_widget( false, false, $this->id_base, true ) ) {
			a_z_listing_do_enqueue();
		}
	}

	/**
	 * Print-out the configuration form for the widget
	 *
	 * @since 0.1
	 * @param  array<string,mixed> $instance Widget instance as provided by WordPress core.
	 * @return string
	 */
	public function form( array $instance ) {
		$args = array(
			'public' => true,
		);

		$public_post_types = get_post_types( $args, 'objects' );
		$public_taxonomies = get_taxonomies( $args, 'objects' );

		$widget_title      = $instance['title'] ?? '';
		$widget_title_id   = $this->get_field_id( 'title' );
		$widget_title_name = $this->get_field_name( 'title' );

		$display_type      = ( isset( $instance['type'] ) && 'terms' === $instance['type'] ) ? 'terms' : 'posts';
		$display_type_id   = $this->get_field_id( 'type' );
		$display_type_name = $this->get_field_name( 'type' );

		$target_post            = isset( $instance['post'] ) ? intval( $instance['post'] ) : ( isset( $instance['page'] ) ? intval( $instance['page'] ) : 0 );
		$target_post_id         = $this->get_field_id( 'post' );
		$target_post_name       = $this->get_field_name( 'post' );
		$target_post_title      = $instance['target_post'] ?? ( ( 0 < $target_post ) ? get_the_title( $target_post ) : '' );
		$target_post_title_id   = $this->get_field_id( 'target_post_title' );
		$target_post_title_name = $this->get_field_name( 'target_post_title' );

		$listing_post_type      = $instance['post_type'] ?? 'page';
		$listing_post_type_id   = $this->get_field_id( 'post_type' );
		$listing_post_type_name = $this->get_field_name( 'post_type' );

		$listing_parent_post            = $instance['parent_post'] ?? '';
		$listing_parent_post_id         = $this->get_field_id( 'parent_post' );
		$listing_parent_post_name       = $this->get_field_name( 'parent_post' );
		$listing_parent_post_title      = $instance['parent_post_title'] ?? ( ( 0 < $listing_parent_post ) ? get_the_title( $listing_parent_post ) : '' );
		$listing_parent_post_title_id   = $this->get_field_id( 'parent_post_title' );
		$listing_parent_post_title_name = $this->get_field_name( 'parent_post_title' );

		$listing_all_children      = $instance['all_children'] ?? 'true';
		$listing_all_children_id   = $this->get_field_id( 'all_children' );
		$listing_all_children_name = $this->get_field_name( 'all_children' );

		$listing_taxonomy      = $instance['taxonomy'] ?? 'page';
		$listing_taxonomy_id   = $this->get_field_id( 'taxonomy' );
		$listing_taxonomy_name = $this->get_field_name( 'taxonomy' );

		$listing_parent_term      = $instance['parent_term'] ?? '';
		$listing_parent_term_id   = $this->get_field_id( 'parent_term' );
		$listing_parent_term_name = $this->get_field_name( 'parent_term' );

		$listing_terms_include      = $instance['terms'] ?? '';
		$listing_terms_include_id   = $this->get_field_id( 'terms' );
		$listing_terms_include_name = $this->get_field_name( 'terms' );

		$listing_terms_exclude      = $instance['terms_exclude'] ?? '';
		$listing_terms_exclude_id   = $this->get_field_id( 'terms_exclude' );
		$listing_terms_exclude_name = $this->get_field_name( 'terms_exclude' );

		$listing_hide_empty_terms      = $instance['hide_empty_terms'] ?? '';
		$listing_hide_empty_terms_id   = $this->get_field_id( 'hide_empty_terms' );
		$listing_hide_empty_terms_name = $this->get_field_name( 'hide_empty_terms' );
		?>

		<div class="a-z-listing-widget">
			<div class="a-z-listing-widget-title-wrapper">
				<p>
					<label for="<?php echo esc_attr( $widget_title_id ); ?>">
						<?php esc_html_e( 'Widget Title', 'a-z-listing' ); ?>
					</label>
					<input type="text" class="widefat a-z-listing-title"
						id="<?php echo esc_attr( $widget_title_id ); ?>"
						name="<?php echo esc_attr( $widget_title_name ); ?>"
						placeholder="<?php esc_attr_e( 'Widget Title', 'a-z-listing' ); ?>"
						value="<?php echo esc_attr( $widget_title ); ?>" />
				</p>
				<p style="color: #333;">
					<?php esc_html_e( 'Leave the title field blank, above, to use the title from the page set in the next field', 'a-z-listing' ); ?>
				</p>
			</div>

			<div class="a-z-listing-target-post-wrapper">
				<p>
					<label for="<?php echo esc_attr( $target_post_title_id ); ?>">
						<?php esc_html_e( 'Sitemap A-Z page', 'a-z-listing' ); ?>
					</label>
					<input type="text" class="widefat a-z-listing-target-post-title"
						id="<?php echo esc_attr( $target_post_title_id ); ?>"
						name="<?php echo esc_attr( $target_post_title_name ); ?>"
						value="<?php echo esc_attr( $target_post_title ); ?>" />
					<input type="hidden"
						id="<?php echo esc_attr( $target_post_id ); ?>"
						name="<?php echo esc_attr( $target_post_name ); ?>"
						value="<?php echo esc_attr( $target_post ); ?>" />
				</p>
				<p>
					<?php esc_html_e( 'Type some or all of the title of the page you want links to point at. Ensure this input field is not selected when you save the settings.', 'a-z-listing' ); ?>
					<?php esc_html_e( 'Matching posts will be shown as you type. Click on the correct post from the matches to update the setting.', 'a-z-listing' ); ?>
				</p>
			</div>

			<div class="a-z-listing-display-type-wrapper">
				<p>
					<label for="<?php echo esc_attr( $display_type_id ); ?>">
						<?php esc_html_e( 'Display posts or terms?', 'a-z-listing' ); ?>
					</label>
					<select class="widefat a-z-listing-display-type"
						id="<?php echo esc_attr( $display_type_id ); ?>"
						name="<?php echo esc_attr( $display_type_name ); ?>">
						<option value="posts"
							<?php echo ( 'terms' !== $display_type ) ? 'selected' : ''; ?>>
							<?php esc_html_e( 'Posts', 'a-z-listing' ); ?>
						</option>
						<option value="terms"
							<?php echo ( 'terms' === $display_type ) ? 'selected' : ''; ?>>
							<?php esc_html_e( 'Taxonomy terms', 'a-z-listing' ); ?>
						</option>
					</select>
				</p>
			</div>

			<div class="a-z-listing-post-type-wrapper" <?php echo ( 'terms' !== $display_type ) ? '' : 'style="display: none;"'; ?>>
				<p>
					<label for="<?php echo esc_attr( $listing_post_type_id ); ?>">
						<?php esc_html_e( 'Post-type to display', 'a-z-listing' ); ?>
					</label>
					<select class="widefat a-z-listing-post-type"
						id="<?php echo esc_attr( $listing_post_type_id ); ?>"
						name="<?php echo esc_attr( $listing_post_type_name ); ?>"
						<?php echo ( 'terms' !== $display_type ) ? '' : 'disabled'; ?>>
						<?php foreach ( $public_post_types as $k => $t ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>"
								<?php echo ( $k === $listing_post_type ) ? 'selected' : ''; ?>>
								<?php echo esc_html( $t->labels->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			</div>

			<div class="a-z-listing-parent-post-wrapper" <?php echo ( 'terms' !== $display_type ) ? '' : 'style="display: none"'; ?>>
				<p>
					<label for="<?php echo esc_attr( $listing_parent_post_title_id ); ?>">
						<?php esc_html_e( 'Show only children of this post (ID)', 'a-z-listing' ); ?>
					</label>
					<input type="text" class="widefat a-z-listing-parent-post-title"
						id="<?php echo esc_attr( $listing_parent_post_title_id ); ?>"
						name="<?php echo esc_attr( $listing_parent_post_title_name ); ?>"
						<?php echo ( 'terms' !== $display_type ) ? '' : 'disabled'; ?>
						value="<?php echo esc_attr( $listing_parent_post_title ); ?>" />
					<input type="hidden"
						id="<?php echo esc_attr( $listing_parent_post_id ); ?>"
						name="<?php echo esc_attr( $listing_parent_post_name ); ?>"
						value="<?php echo esc_attr( $listing_parent_post ); ?>" />
				</p>
				<p>
					<?php esc_html_e( 'Type some or all of the title of the post to limit the listing to only the children of that post. Ensure this input field is not selected when you save the settings.', 'a-z-listing' ); ?>
					<?php esc_html_e( 'Matching posts will be shown as you type. Click on the correct post from the matches to update the setting.', 'a-z-listing' ); ?>
				</p>
				<p>
					<label for="<?php echo esc_attr( $listing_all_children_id ); ?>">
						<?php esc_html_e( 'Include grand-children?', 'a-z-listing' ); ?>
					</label>
					<input type="checkbox" class="a-z-listing-all-children"
						id="<?php echo esc_attr( $listing_all_children_id ); ?>"
						name="<?php echo esc_attr( $listing_all_children_name ); ?>"
						<?php echo ( isset( $listing_all_children ) && 'true' === $listing_all_children ) ? 'checked' : ''; ?> />
				</p>
			</div>

			<div class="a-z-listing-taxonomy-wrapper" <?php echo ( 'terms' === $display_type ) ? '' : 'style="display: none;"'; ?>>
				<p>
					<label for="<?php echo esc_attr( $listing_taxonomy_id ); ?>">
						<?php esc_html_e( 'Taxonomy to display', 'a-z-listing' ); ?>
					</label>
					<select class="widefat a-z-listing-taxonomy"
						id="<?php echo esc_attr( $listing_taxonomy_id ); ?>"
						name="<?php echo esc_attr( $listing_taxonomy_name ); ?>"
						<?php echo ( 'terms' === $display_type ) ? '' : 'disabled'; ?>>
						<?php foreach ( $public_taxonomies as $k => $t ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>"
								<?php echo ( $k === $listing_taxonomy ) ? 'selected' : ''; ?>>
								<?php echo esc_html( $t->labels->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			</div>

			<div class="a-z-listing-parent-term-wrapper" <?php echo ( 'terms' === $display_type ) ? '' : 'style="display: none;"'; ?>>
				<p>
					<label for="<?php echo esc_attr( $listing_parent_term_id ); ?>">
						<?php esc_html_e( 'Parent term to display children of', 'a-z-listing' ); ?>
					</label>
					<input type="text" class="widefat a-z-listing-parent-term"
						id="<?php echo esc_attr( $listing_parent_term_id ); ?>"
						name="<?php echo esc_attr( $listing_parent_term_name ); ?>"
						value="<?php echo esc_attr( $listing_parent_term ); ?>" />
				</p>
			</div>

			<div class="a-z-listing-include-terms-wrapper">
				<p>
					<label for="<?php echo esc_attr( $listing_terms_include_id ); ?>">
						<?php esc_html_e( 'Terms to include (IDs)', 'a-z-listing' ); ?>
					</label>
					<input type="text" class="widefat a-z-listing-include-terms"
						id="<?php echo esc_attr( $listing_terms_include_id ); ?>"
						name="<?php echo esc_attr( $listing_terms_include_name ); ?>"
						value="<?php echo esc_attr( $listing_terms_include ); ?>" />
				</p>
			</div>

			<div class="a-z-listing-exclude-terms-wrapper" <?php echo ( 'terms' === $display_type ) ? '' : 'style="display: none;"'; ?>>
				<p>
					<label for="<?php echo esc_attr( $listing_terms_exclude_id ); ?>">
						<?php esc_html_e( 'Terms to exclude (IDs)', 'a-z-listing' ); ?>
					</label>
					<input type="text" class="widefat a-z-listing-exclude-terms"
						id="<?php echo esc_attr( $listing_terms_exclude_id ); ?>"
						name="<?php echo esc_attr( $listing_terms_exclude_name ); ?>"
						value="<?php echo esc_attr( $listing_terms_exclude ); ?>" />
				</p>
			</div>

			<div class="a-z-listing-hide-empty-terms-wrapper" <?php echo ( 'terms' === $display_type ) ? '' : 'style="display: none;"'; ?>>
				<p>
					<label for="<?php echo esc_attr( $listing_hide_empty_terms_id ); ?>">
						<?php esc_html_e( 'Hide empty terms', 'a-z-listing' ); ?>
					</label>
					<input type="checkbox" class="a-z-listing-hide-empty-terms"
						id="<?php echo esc_attr( $listing_hide_empty_terms_id ); ?>"
						name="<?php echo esc_attr( $listing_hide_empty_terms_name ); ?>"
						<?php echo ( isset( $listing_hide_empty_terms ) && 'true' === $listing_hide_empty_terms ) ? 'checked' : ''; ?> />
				</p>
			</div>
		</div>
		<?php
		return '';
	}

	/**
	 * Called by WordPress core. Sanitises changes to the Widget's configuration
	 *
	 * @since 0.1
	 * @param  array<string,mixed> $new_instance the new configuration values.
	 * @param  array<string,mixed> $old_instance the previous configuration values.
	 * @return array<string,mixed> sanitised version of the new configuration values to be saved
	 */
	public function update( array $new_instance, array $old_instance ): array {
		$instance = $old_instance;

		$instance['title']             = strip_tags( $new_instance['title'] );
		$instance['type']              = strip_tags( $new_instance['type'] );
		$instance['post']              = (int) $new_instance['post']; // target.
		$instance['target_post_title'] = strip_tags( $new_instance['target_post_title'] );
		$instance['post_type']         = strip_tags( $new_instance['post_type'] );
		$instance['taxonomy']          = strip_tags( $new_instance['taxonomy'] );
		$instance['parent_post']       = (int) $new_instance['parent_post'];
		$instance['all_children']      = 'on' === $new_instance['all_children'] ? 'true' : 'false';
		$instance['parent_term']       = strip_tags( $new_instance['parent_term'] );
		$instance['terms']             = strip_tags( $new_instance['terms'] );
		$instance['exclude_terms']     = strip_tags( $new_instance['exclude_terms'] );
		$instance['hide_empty_terms']  = 'on' === $new_instance['hide_empty_terms'] ? 'true' : 'false';

		if ( empty( $new_instance['target_post_title'] ) ) {
			$instance['post'] = 0;
		}
		if ( empty( $new_instance['parent_post_title'] ) ) {
			$instance['parent_post'] = 0;
		}

		return $instance;
	}

	/**
	 * Print the user-visible widget to the page
	 *
	 * @since 0.1
	 * @param  array<string,mixed> $args     General widget configuration. Often shared between all widgets on the site.
	 * @param  array<string,mixed> $instance Configuration of this Widget. Unique to this invocation.
	 * @return void
	 */
	public function widget( array $args, array $instance ) {
		the_section_a_z_widget( $args, $instance );
	}
}

/**
 * Print the user-visible widget to the page implentation
 *
 * @since 0.1
 * @since 0.8.0 deprecated.
 * @see A_Z_Widget::the_section_a_z_widget()
 * @deprecated use the_section_a_z_widget()
 * @param  array<string,mixed> $args     General widget configuration. Often shared between all widgets on the site.
 * @param  array<string,mixed> $instance Configuration of this Widget. Unique to this invocation.
 * @return void
 */
function the_section_az_widget( array $args, array $instance ) {
	_deprecated_function( __FUNCTION__, '0.8.0', 'the_section_a_z_widget' );
	the_section_a_z_widget( $args, $instance );
}

/**
 * Print the user-visible widget to the page implentation
 *
 * @since 0.8.0
 * @param  array<string,mixed> $args     General widget configuration. Often shared between all widgets on the site.
 * @param  array<string,mixed> $instance Configuration of this Widget. Unique to this invocation.
 * @return void
 */
function the_section_a_z_widget( array $args, array $instance ) {
	echo get_the_section_a_z_widget( $args, $instance ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Deprecated print the user-visible widget to the page implementation.
 *
 * @since 0.1
 * @since 0.8.0 deprecated.
 * @see A_Z_Widget::get_the_section_a_z_widget()
 * @deprecated use get_the_section_a_z_widget()
 * @param  array<string,mixed> $args General widget configuration. Often shared between all widgets on the site.
 * @param  array<string,mixed> $instance Configuration of this Widget. Unique to this invocation.
 * @return string The complete A-Z Widget HTML ready for echoing to the page.
 */
function get_the_section_az_widget( array $args, array $instance ): string {
	_deprecated_function( __FUNCTION__, '0.8.0', 'get_the_section_a_z_widget' );
	return get_the_section_a_z_widget( $args, $instance );
}

/**
 * Get the user-visible widget html
 *
 * @since 0.8.0
 * @param  array<string,mixed> $args     General widget configuration. Often shared between all widgets on the site.
 * @param  array<string,mixed> $instance Configuration of this Widget. Unique to this invocation.
 * @return  string The complete A-Z Widget HTML ready for echoing to the page.
 */
function get_the_section_a_z_widget( array $args, array $instance ): string {
	do_action( 'log', 'A-Z Listing: Running widget' );

	$instance = wp_parse_args(
		$instance,
		array(
			'all_children'     => 'true',
			'exclude_terms'    => '',
			'hide_empty_terms' => false,
			'parent_post'      => '',
			'parent_term'      => '',
			'post'             => -1, // target.
			'page'             => -1, // obsolete target.
			'post_type'        => 'page',
			'taxonomy'         => '',
			'terms'            => '',
			'title'            => '',
			'type'             => 'posts',
		)
	);

	$title      = esc_html( $instance['title'] );
	$target_url = '';
	if ( 0 < $instance['post'] || 0 < $instance['page'] ) { // target.
		$target_id = (int) $instance['post']; // target.
		if ( ! ( 0 < $instance['post'] ) ) {
			$target_id = (int) $instance['page']; // obsolete target.
		}

		$target_url = get_the_permalink( $target_id );
		if ( empty( $title ) ) {
			$title = get_the_title( $target_id );
		}
	} elseif ( empty( $title ) ) {
		$title = esc_html__( 'A-Z Listing', 'a-z-listing' );
	}

	$hide_empty_terms = true === $instance['hide_empty_terms'] ? 'true' : 'false';

	$ret  = '';
	$ret .= $args['before_widget'];

	$ret .= $args['before_title'];
	$ret .= $title;
	$ret .= $args['after_title'];

	$ret .= do_shortcode(
		"[a-z-listing
            alphabet=''
            display='{$instance['type']}'
            exclude-posts=''
            exclude-terms='{$instance['exclude_terms']}'
            get-all-children='{$instance['all_children']}'
            group-numbers=''
            grouping=''
            hide-empty-terms='{$hide_empty_terms}'
            numbers='hide'
            parent-post='{$instance['parent_post']}'
            parent-term='{$instance['parent_term']}'
            post-type='{$instance['post_type']}'
            return='letters'
            target='{$target_url}'
            taxonomy='{$instance['taxonomy']}'
            terms='{$instance['terms']}'
        ]"
	);

	$ret .= $args['after_widget'];

	return $ret;
}

/**
 * Retrive posts by title.
 *
 * @since 2.1.0
 * @param string $post_title the title to search for.
 * @param string $post_type the post type to search within.
 * @return array<int,object> the post IDs that are found.
 */
function a_z_listing_get_posts_by_title( string $post_title, string $post_type = '' ): array {
	global $wpdb;

	$post_title = '%' . $wpdb->esc_like( $post_title ) . '%';

	if ( ! empty( $post_type ) ) {
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `ID`, `post_title` FROM `$wpdb->posts`
                WHERE `post_title` LIKE %s AND `post_type` = %s AND `post_status` = 'publish'",
				$post_title,
				$post_type
			)
		);
	} else {
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `ID`, `post_title` FROM `$wpdb->posts`
                WHERE `post_title` LIKE %s AND `post_status` = 'publish'",
				$post_title
			)
		);
	}
}

/**
 * Ajax responder for A_Z_Listing_Widget configuration
 *
 * @since 2.0.0
 * @return void
 */
function a_z_listing_autocomplete_post_titles() {
	$post_title = stripslashes( $_POST['post_title']['term'] );
	$post_type  = stripslashes( $_POST['post_type'] );

	$results = a_z_listing_get_posts_by_title( $post_title, $post_type );

	$titles = array();
	foreach ( $results as $result ) {
		$titles[] = array(
			'value' => intval( $result->ID ),
			'label' => addslashes( $result->post_title ),
		);
	}

	echo json_encode( $titles );

	exit();
}
add_action( 'wp_ajax_nopriv_get_a_z_listing_autocomplete_post_titles', 'a_z_listing_autocomplete_post_titles' );
add_action( 'wp_ajax_get_a_z_listing_autocomplete_post_titles', 'a_z_listing_autocomplete_post_titles' );

/**
 * Register the A_Z_Widget widget
 *
 * @since 2.0.0
 * @return void
 */
function a_z_listing_widget() {
	register_widget( 'A_Z_Listing_Widget' );
}
add_action( 'widgets_init', 'a_z_listing_widget' );

/**
 * Enqueue the jquery-ui autocomplete script
 *
 * @since 2.0.0
 * @return void
 */
function a_z_listing_autocomplete_script() {
	wp_enqueue_script( 'jquery-ui-autocomplete' );
}
add_action( 'admin_enqueue_scripts', 'a_z_listing_autocomplete_script' );
