<?php

declare(strict_types=1);

class AZ_Listing_ParentPost_Extension_Tests extends WP_UnitTestCase {
    public function test_parent_post() {
        $display = 'posts';
        $key = 'parent-post';
        $value = '256';
        $attributes = array( $key => $value );

        $expected = array( 'post_parent' => $value );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_parent_post_get_all_children() {
        $display = 'posts';
        $key = 'parent-post';
        $value = '256';
        $attributes = array( $key => $value, 'get-all-children' => 'true' );

        $expected = array( 'child_of' => $value );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }
}
