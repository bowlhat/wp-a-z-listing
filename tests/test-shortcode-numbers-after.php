<?php

declare(strict_types=1);

// Load a-z-listing-specific test extension
require_once 'html-assertions.php';

class AZ_Shortcode_Numbers_After_Tests extends WP_UnitTestCase {
	use HtmlAssertions;

	public function test_empty() {
		$expected = file_get_contents( 'tests/data/default-listing-numbers-after.txt' );
		$actual   = do_shortcode( '[a-z-listing numbers="after"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_empty_grouped() {
		$expected = file_get_contents( 'tests/data/default-listing-numbers-after-grouped.txt' );
		$actual   = do_shortcode( '[a-z-listing numbers="after" grouping="numbers"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_listing() {
		$title = 'Test Page';
		$p     = self::factory()->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-numbers-after.txt' ), $title, $p );
		$actual   = do_shortcode( '[a-z-listing numbers="after"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_listing_grouped() {
		$title = 'Test Page';
		$p     = self::factory()->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-numbers-after-grouped.txt' ), $title, $p );
		$actual   = do_shortcode( '[a-z-listing numbers="after" grouping="numbers"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_lowercase_titles() {
		$title = 'test page';
		$p     = self::factory()->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-numbers-after.txt' ), $title, $p );
		$actual   = do_shortcode( '[a-z-listing numbers="after"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_taxonomy_listing() {
		$title = 'test category';
		$t     = self::factory()->term->create(
			array(
				'name'     => $title,
				'taxonomy' => 'category',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing-numbers-after.txt' ), $title, $t );
		$actual   = do_shortcode( '[a-z-listing display="terms" taxonomy="category" numbers="after"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_taxonomy_listing_grouped() {
		$title = 'test category';
		$t     = self::factory()->term->create(
			array(
				'name'     => $title,
				'taxonomy' => 'category',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing-numbers-after-grouped.txt' ), $title, $t );
		$actual   = do_shortcode( '[a-z-listing display="terms" taxonomy="category" numbers="after" grouping="numbers"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_filtered_listing() {
		$cat = 'test category';
		$t   = self::factory()->term->create(
			array(
				'name'     => $cat,
				'taxonomy' => 'category',
			)
		);

		$title = 'Test Page';
		$p     = self::factory()->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);

		wp_set_post_terms( $p, $t, 'category' );

		$term = get_term( $t, 'category' );

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-numbers-after.txt' ), $title, $p );
		$actual   = do_shortcode( sprintf( '[a-z-listing taxonomy="category" terms="%s" numbers="after"]', $term->slug ) );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_filtered_listing_grouped() {
		$cat = 'test category';
		$t   = self::factory()->term->create(
			array(
				'name'     => $cat,
				'taxonomy' => 'category',
			)
		);

		$title = 'Test Page';
		$p     = self::factory()->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);

		wp_set_post_terms( $p, $t, 'category' );

		$term = get_term( $t, 'category' );

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-numbers-after-grouped.txt' ), $title, $p );
		$actual   = do_shortcode( sprintf( '[a-z-listing taxonomy="category" terms="%s" numbers="after" grouping="numbers"]', $term->slug ) );

		$this->assertHTMLEquals( $expected, $actual );
	}
}
