<?php

// Load a-z-listing-specific test extension
require_once 'html-assertions.php';

class AZ_Shortcode_Tests extends WP_UnitTestCase {
	use HtmlAssertions;

	public function test_empty_listing() {
		$expected = file_get_contents( 'tests/data/default-listing.txt' );
		$actual   = do_shortcode( '[a-z-listing]' );

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

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing.txt' ), $title, $p );
		$actual   = do_shortcode( '[a-z-listing]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_lowercase_titles_listing() {
		$p = self::factory()->post->create(
			array(
				'post_title' => 'test page',
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-lowercase.txt' ), $p );
		$actual   = do_shortcode( '[a-z-listing]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_children_listing() {
		$title  = 'Test Page';
		$parent = self::factory()->post->create(
			array(
				'post_title' => 'Parent post',
				'post_type'  => 'page',
			)
		);
		$p     = self::factory()->post->create(
			array(
				'post_title'  => $title,
				'post_type'   => 'page',
				'post_parent' => $parent,
			)
		);

		$expected  = sprintf( file_get_contents( 'tests/data/populated-listing.txt' ), $title, $p + 5 );
		$shortcode = sprintf( '[a-z-listing parent-post="%s"]', $parent );
		$actual    = do_shortcode( $shortcode );

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
	
		$expected = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing.txt' ), $title, $t );
		$actual   = do_shortcode( '[a-z-listing display="terms" taxonomy="category"]' );
	
		$this->assertHTMLEquals( $expected, $actual );
	}
	
	public function test_populated_multiple_taxonomy_listing() {
		$cat_title = 'Test Category';
		$cat       = self::factory()->term->create(
			array(
				'name'     => $cat_title,
				'taxonomy' => 'category',
			)
		);
		$tag_title = 'Test Tag';
		$tag       = self::factory()->term->create(
			array(
				'name'     => $tag_title,
				'taxonomy' => 'post_tag',
			)
		);
	
		$expected = sprintf( file_get_contents( 'tests/data/populated-multiple-taxonomy-listing.txt' ), $cat_title, $cat, $tag_title, $tag );
		$actual   = do_shortcode( '[a-z-listing display="terms" taxonomy="category,post_tag"]' );
	
		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_pages_filtered_by_taxonomy_terms_listing() {
		self::factory()->post->create(
			array(
				'post_title' => 'Must not be visible',
				'post_type'  => 'page',
			)
		);

		$post_title = 'Test Page';
		$p          = self::factory()->post->create(
			array(
				'post_title' => $post_title,
				'post_type'  => 'page',
			)
		);

		$t = self::factory()->term->create(
			array(
				'name'     => 'test category',
				'taxonomy' => 'category',
				'slug'     => 'test-category',
			)
		);

		$this->assertNotWPError( self::factory()->term->add_post_terms( $p, $t, 'category', true ) );

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing.txt' ), $post_title, $p );
		$actual   = do_shortcode( '[a-z-listing post-type="page" taxonomy="category" terms="test-category"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_posts_filtered_by_taxonomy_terms_listing() {
		self::factory()->post->create(
			array(
				'post_title' => 'Must not be visible',
				'post_type'  => 'post',
			)
		);

		$post_title = 'Test Post';
		$p          = self::factory()->post->create(
			array(
				'post_title' => $post_title,
				'post_type'  => 'post',
			)
		);

		$t = self::factory()->term->create(
			array(
				'name'     => 'test category',
				'taxonomy' => 'category',
				'slug'     => 'test-category',
			)
		);

		$this->assertNotWPError( self::factory()->term->add_post_terms( $p, $t, 'category', true ) );

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing.txt' ), $post_title, $p );
		$actual   = do_shortcode( '[a-z-listing post-type="post" taxonomy="category" terms="test-category"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_multiple_post_type_listing() {
		$title1 = 'Test title 1';
		$post1  = self::factory()->post->create(
			array(
				'post_title' => $title1,
				'post_type'  => 'post',
			)
		);

		$title2 = 'Test title 2';
		$post2  = self::factory()->post->create(
			array(
				'post_title' => $title2,
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-multiple-post-types.txt' ), $title1, $post1, $title2, $post2 );
		$actual   = do_shortcode( '[a-z-listing post-type="post,page"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_empty_listing_after_exclude_posts() {
		$title = 'Test Page';
		$p     = self::factory()->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);

		$expected  = file_get_contents( 'tests/data/default-listing.txt' );
		$shortcode = sprintf( '[a-z-listing display="posts" post-type="page" exclude-posts="%s"]', $p );
		$actual    = do_shortcode( $shortcode );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_listing_after_exclude_posts() {
		$exclude_id = self::factory()->post->create(
			array(
				'post_title' => 'Must not be visible',
				'post_type'  => 'page',
			)
		);

		$post_title = 'Test Post';
		$p          = self::factory()->post->create(
			array(
				'post_title' => $post_title,
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing.txt' ), $post_title, $p );
		$shortcode = sprintf( '[a-z-listing display="posts" post-type="page" exclude-posts="%s"]', $exclude_id );
		$actual    = do_shortcode( $shortcode );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_symbols_last_listing() {
		$title = '%Test Page';
		$p     = self::factory()->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-symbols-last.txt' ), $title, $p );
		$actual   = do_shortcode( '[a-z-listing]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_symbols_last_override_listing() {
		$title = '%Test Page';
		$p     = self::factory()->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-symbols-last.txt' ), $title, $p );
		$actual   = do_shortcode( '[a-z-listing symbols-first="no"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_symbols_first_override_listing() {
		$title = '%Test Page';
		$p     = self::factory()->post->create(
			array(
				'post_title' => $title,
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing-symbols-first.txt' ), $title, $p );
		$actual   = do_shortcode( '[a-z-listing symbols-first="yes"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}
}
