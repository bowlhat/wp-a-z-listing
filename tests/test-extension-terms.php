<?php

declare(strict_types=1);

class AZ_Listing_Terms_Extension_Tests extends WP_UnitTestCase {
    public function test_include_term_ids_for_display_posts() {
        $display = 'posts';
        $key = 'terms';
        $value = '2,4,6';
        $attributes = array( $key => $value, 'taxonomy' => 'category' );

        $expected = array(
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => $attributes['taxonomy'],
                    'field'    => 'term_id',
                    'terms'    => array( '2', '4', '6' ),
                    'operator' => 'IN',
                ),
            ),
        );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_exclude_term_ids_for_display_posts() {
        $display = 'posts';
        $key = 'terms';
        $value = '-2,-4,-6';
        $attributes = array( $key => $value, 'taxonomy' => 'category' );

        $expected = array(
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => $attributes['taxonomy'],
                    'field'    => 'term_id',
                    'terms'    => array( '2', '4', '6' ),
                    'operator' => 'NOT IN',
                ),
            ),
        );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_include_and_exclude_term_ids_for_display_posts() {
        $display = 'posts';
        $key = 'terms';
        $value = '-2,4,-6';
        $attributes = array( $key => $value, 'taxonomy' => 'category' );

        $expected = array(
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => $attributes['taxonomy'],
                    'field'    => 'term_id',
                    'terms'    => array( '4' ),
                    'operator' => 'IN',
                ),
                array(
                    'taxonomy' => $attributes['taxonomy'],
                    'field'    => 'term_id',
                    'terms'    => array( '2', '6' ),
                    'operator' => 'NOT IN',
                ),
            ),
        );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_include_term_ids_for_display_terms() {
        $display = 'terms';
        $key = 'terms';
        $value = '2,4,6';
        $attributes = array( $key => $value, 'taxonomy' => 'category' );

        $expected = array(
            'include' => array( '2', '4', '6' ),
        );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_exclude_term_ids_for_display_terms() {
        $display = 'terms';
        $key = 'terms';
        $value = '-2,-4,-6';
        $attributes = array( $key => $value, 'taxonomy' => 'category' );

        $expected = array(
            'exclude' => array( '2', '4', '6' ),
        );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }

    public function test_include_and_exclude_term_ids_for_display_terms() {
        $display = 'terms';
        $key = 'terms';
        $value = '-2,4,-6';
        $attributes = array( $key => $value, 'taxonomy' => 'category' );

        $expected = array(
            'include' => array( '4' ),
        );
        $actual   = apply_filters( "a_z_listing_shortcode_query_for_display__{$display}__and_attribute__{$key}", array(), $display, $key, $value, $attributes );

        $this->assertEquals( $expected, $actual );
    }
}
