<?php

declare(strict_types=1);

// Load a-z-listing-specific test extension
require_once 'html-assertions.php';

class AZ_Shortcode_Grouping_Tests extends WP_UnitTestCase {
	use HtmlAssertions;

	public function test_empty() {
		$expected = file_get_contents( 'tests/data/default-listing-grouped.txt' );
		$actual   = do_shortcode( '[a-z-listing grouping="3"]' );

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

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-grouped.txt' ), $title, $p );
		$actual   = do_shortcode( '[a-z-listing grouping="3"]' );

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

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-grouped.txt' ), $title, $p );
		$actual   = do_shortcode( '[a-z-listing grouping="3"]' );

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

		$expected = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing-grouped.txt' ), $title, $t );
		$actual   = do_shortcode( '[a-z-listing display="terms" taxonomy="category" grouping="3"]' );

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

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-grouped.txt' ), $title, $p );
		$actual   = do_shortcode( sprintf( '[a-z-listing taxonomy="category" terms="%s" grouping="3"]', $term->slug ) );

		$this->assertHTMLEquals( $expected, $actual );
	}
}
