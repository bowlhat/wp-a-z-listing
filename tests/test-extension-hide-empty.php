<?php

declare(strict_types=1);

class AZ_Listing_HideEmpty_Extension_Tests extends WP_UnitTestCase {
    public function test_hide_empty_is_true() {
        $display = 'terms';
        $key = 'hide-empty';
        $value = 'true';
        $attributes = array( $key => $value );

        $expected = array( 'hide_empty' => true );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_hide_empty_is_on() {
        $display = 'terms';
        $key = 'hide-empty';
        $value = 'on';
        $attributes = array( $key => $value );

        $expected = array( 'hide_empty' => true );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_hide_empty_is_one() {
        $display = 'terms';
        $key = 'hide-empty';
        $value = '1';
        $attributes = array( $key => $value );

        $expected = array( 'hide_empty' => true );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_hide_empty_is_false() {
        $display = 'terms';
        $key = 'hide-empty';
        $value = 'false';
        $attributes = array( $key => $value );

        $expected = array( 'hide_empty' => false );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_hide_empty_is_off() {
        $display = 'terms';
        $key = 'hide-empty';
        $value = 'off';
        $attributes = array( $key => $value );

        $expected = array( 'hide_empty' => false );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_hide_empty_is_zero() {
        $display = 'terms';
        $key = 'hide-empty';
        $value = '0';
        $attributes = array( $key => $value );

        $expected = array( 'hide_empty' => false );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }
}
