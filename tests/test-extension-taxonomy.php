<?php

declare(strict_types=1);

class AZ_Listing_Taxonomy_Extension_Tests extends WP_UnitTestCase {
    public function test_taxonomy_is_category_for_terms_display() {
        $display = 'terms';
        $key = 'taxonomy';
        $value = 'category';
        $attributes = array( $key => $value );

        $expected = array( 'taxonomy' => array( 'category' ) );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_taxonomy_is_post_tag_for_terms_display() {
        $display = 'terms';
        $key = 'taxonomy';
        $value = 'post_tag';
        $attributes = array( $key => $value );

        $expected = array( 'taxonomy' => array( 'post_tag' ) );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_taxonomy_is_multiple_for_terms_display() {
        $display = 'terms';
        $key = 'taxonomy';
        $value = 'category,post_tag';
        $attributes = array( $key => $value );

        $expected = array( 'taxonomy' => array( 'category', 'post_tag' ) );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }
}
