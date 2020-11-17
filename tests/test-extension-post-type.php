<?php

declare(strict_types=1);

class AZ_Listing_PostType_Extension_Tests extends WP_UnitTestCase {
    public function test_post_type_is_post() {
        $display = 'posts';
        $key = 'post-type';
        $value = 'post';
        $attributes = array( $key => $value );

        $expected = array( 'post_type' => array( 'post' ) );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_post_type_is_page() {
        $display = 'posts';
        $key = 'post-type';
        $value = 'page';
        $attributes = array( $key => $value );

        $expected = array( 'post_type' => array( 'page' ) );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_post_type_is_multiple() {
        $display = 'posts';
        $key = 'post-type';
        $value = 'post,page';
        $attributes = array( $key => $value );

        $expected = array( 'post_type' => array( 'post', 'page' ) );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }
}
