<?php

declare(strict_types=1);

class AZ_Listing_ExcludePosts_Extension_Tests extends WP_UnitTestCase {
    public function test_exclusion() {
        $display = 'posts';
        $key = 'exclude-posts';
        $value = '2,4,6';
        $attributes = array( $key => $value );

        $expected = array( 'post__not_in' => array( 2, 4, 6 ) );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }
}
