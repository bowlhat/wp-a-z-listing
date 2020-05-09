<?php
/**
 * Server-side rendering of the `a-z-listing` block.
 *
 * @package WordPress
 */

namespace A_Z_Listing;

class GutenBlock extends Singleton implements Extension {
    /**
     * The excerpt length set by the A-Z Listing block
     * set at render time and used by the block itself.
     *
     * @var int
     */
    protected $excerpt_length = 0;

    /**
     * Callback for the excerpt_length filter used by
     * the Latest Posts block at render time.
     *
     * @return int Returns the global $block_core_latest_posts_excerpt_length variable
     *             to allow the excerpt_length filter respect the Latest Block setting.
     */
    public function get_excerpt_length() {
        return $this->excerpt_length;
    }

    public function render( $attributes ) {
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
				'parent-term-id'   => '',
				'post-type'        => 'page',
				'return'           => 'listing',
				'target'           => '',
				'taxonomy'         => '',
				'terms'            => '',
			),
			$attributes,
			'a-z-listing'
        );

        $this->excerpt_length = $attributes['excerpt-length'];
        add_filter( 'excerpt_length', array( $this, 'get_excerpt_length' ), 20 );
    }

    final public function initialize() {
        $dir = dirname( __DIR__ );

        $script_asset_path = "$dir/build/index.asset.php";
        if ( ! file_exists( $script_asset_path ) ) {
            throw new Error(
                'You need to run `npm start` or `npm run build` for the "a-z-listing/block" block first.'
            );
        }
        $index_js     = 'build/index.js';
        $script_asset = require( $script_asset_path );
        wp_register_script(
            'a-z-listing-block-editor',
            plugins_url( $index_js, __FILE__ ),
            $script_asset['dependencies'],
            $script_asset['version']
        );

        $editor_css = 'css/editor.css';
        wp_register_style(
            'a-z-listing-block-editor',
            plugins_url( $editor_css, __FILE__ ),
            array(),
            filemtime( "$dir/$editor_css" )
        );

        $style_css = 'css/style.css';
        wp_register_style(
            'a-z-listing-block',
            plugins_url( $style_css, __FILE__ ),
            array(),
            filemtime( "$dir/$style_css" )
        );

        register_block_type( 'a-z-listing/block', array(
            'editor_script' => 'a-z-listing-block-editor',
            'editor_style'  => 'a-z-listing-block-editor',
            'style'         => 'a-z-listing-block',
            'render_callback' => array( $this, 'render' ),
        ) );
    }
}
