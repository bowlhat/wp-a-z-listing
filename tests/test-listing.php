<?php

declare(strict_types=1);

// Load a-z-listing-specific test extension
require_once 'html-assertions.php';

class AZ_Listing_Tests extends WP_UnitTestCase {
	use HtmlAssertions;

	public function test_empty_letters() {
		$expected = file_get_contents( 'tests/data/default-letters.txt' );
		$actual   = get_the_a_z_letters( null, false, false, false );

		$this->assertHTMLEquals( $expected, $actual );
	}
	public function test_empty_listing() {
		$expected = file_get_contents( 'tests/data/default-listing.txt' );
		$actual   = get_the_a_z_listing( null, false );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_letters() {
		$p = $this->factory->post->create(
			array(
				'post_title' => 'Test Page',
				'post_type'  => 'page',
			)
		);
		$q = new WP_Query(
			array(
				'post_type' => 'page',
			)
		);

		$expected = file_get_contents( 'tests/data/populated-letters.txt' );
		$actual   = get_the_a_z_letters( $q, '', '', false );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_letters_linked() {
		$p = $this->factory->post->create(
			array(
				'post_title' => 'Test Page',
				'post_type'  => 'page',
			)
		);
		$q = new WP_Query(
			array(
				'post_type' => 'page',
			)
		);

		$expected = file_get_contents( 'tests/data/populated-letters-linked.txt' );
		$actual   = get_the_a_z_letters( $q, '/test-path', false, false );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_listing() {
		$title = 'Test Page';
		$p     = $this->factory->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);
		$q     = new WP_Query(
			array(
				'post_type' => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing.txt' ), $title, $p );
		$actual   = get_the_a_z_listing( $q, false );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_listing_with_unknown_letters() {
		$title = '*Test Page';
		$p     = $this->factory->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);
		$q     = new WP_Query(
			array(
				'post_type' => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-unknown-letters.txt' ), $title, $p );
		$actual   = get_the_a_z_listing( $q, false );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_taxonomy_listing_string_query() {
		$title = 'Test Category';
		$t     = $this->factory->term->create(
			array(
				'name'     => $title,
				'taxonomy' => 'category',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing.txt' ), $title, $t );
		$actual   = get_the_a_z_listing( 'category', false );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_taxonomy_listing_array_query() {
		$title = 'Test Category';
		$t     = $this->factory->term->create(
			array(
				'name'     => $title,
				'taxonomy' => 'category',
			)
		);

		$expected    = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing.txt' ), $title, $t );
		$a_z_listing = new \A_Z_Listing\Query(
			array(
				'taxonomy' => 'category',
			),
			'terms'
		);
		$actual      = $a_z_listing->get_the_listing();

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_multiple_taxonomy_listing_array_query() {
		$cat_title = 'Test Category';
		$cat       = $this->factory->term->create(
			array(
				'name'     => $cat_title,
				'taxonomy' => 'category',
			)
		);
		$tag_title = 'Test Tag';
		$tag       = $this->factory->term->create(
			array(
				'name'     => $tag_title,
				'taxonomy' => 'post_tag',
			)
		);

		$expected    = sprintf( file_get_contents( 'tests/data/populated-multiple-taxonomy-listing.txt' ), $cat_title, $cat, $tag_title, $tag );
		$a_z_listing = new \A_Z_Listing\Query(
			array(
				'taxonomy' => array(
					'category',
					'post_tag',
				),
			),
			'terms'
		);
		$actual      = $a_z_listing->get_the_listing();

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_lowercase_letters() {
		$p = $this->factory->post->create(
			array(
				'post_title' => 'test page',
				'post_type'  => 'page',
			)
		);
		$q = new WP_Query(
			array(
				'post_type' => 'page',
			)
		);

		$expected = file_get_contents( 'tests/data/populated-letters.txt' );
		$actual   = get_the_a_z_letters( $q, false );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_lowercase_letters_linked() {
		$p = $this->factory->post->create(
			array(
				'post_title' => 'test page',
				'post_type'  => 'page',
			)
		);
		$q = new WP_Query(
			array(
				'post_type' => 'page',
			)
		);

		$expected = file_get_contents( 'tests/data/populated-letters-linked.txt' );
		$actual   = get_the_a_z_letters( $q, '/test-path', false, false );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_lowercase_listing() {
		$title = 'test page';
		$p     = $this->factory->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);
		$q     = new WP_Query(
			array(
				'post_type' => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing.txt' ), $title, $p );
		$actual   = get_the_a_z_listing( $q, false );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_lowercase_taxonomy_listing() {
		$title = 'test category';
		$t     = $this->factory->term->create(
			array(
				'name'     => $title,
				'taxonomy' => 'category',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing.txt' ), $title, $t );
		$actual   = get_the_a_z_listing( 'category', false );

		$this->assertHTMLEquals( $expected, $actual );
	}
}
