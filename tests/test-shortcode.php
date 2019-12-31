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
		$p     = $this->factory->post->create(
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
		$p = $this->factory->post->create(
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
		$parent = $this->factory->post->create(
			array(
				'post_title' => 'Parent post',
				'post_type'  => 'page',
			)
		);
		$p     = $this->factory->post->create(
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

	public function test_populated_pages_filtered_by_taxonomy_terms_listing() {
		$this->factory->post->create(
			array(
				'post_title' => 'Must not be visible',
				'post_type'  => 'page',
			)
		);

		$post_title = 'Test Page';
		$p          = $this->factory->post->create(
			array(
				'post_title' => $post_title,
				'post_type'  => 'page',
			)
		);

		$term_title = 'test category';
		$t          = $this->factory->term->create(
			array(
				'name'     => $term_title,
				'taxonomy' => 'category',
				'slug'     => 'test-category',
			)
		);

		$this->factory->term->add_post_terms( $p, $t, 'category', true );

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing.txt' ), $post_title, $p );
		$actual   = do_shortcode( '[a-z-listing post-type="page" taxonomy="category" terms="test-category"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_posts_filtered_by_taxonomy_terms_listing() {
		$this->factory->post->create(
			array(
				'post_title' => 'Must not be visible',
				'post_type'  => 'post',
			)
		);

		$post_title = 'Test Post';
		$p          = $this->factory->post->create(
			array(
				'post_title' => $post_title,
				'post_type'  => 'post',
			)
		);

		$term_title = 'test category';
		$t          = $this->factory->term->create(
			array(
				'name'     => $term_title,
				'taxonomy' => 'category',
				'slug'     => 'test-category',
			)
		);

		$this->factory->term->add_post_terms( $p, $t, 'category', true );

		$expected = sprintf( file_get_contents( 'tests/data/populated-listing.txt' ), $post_title, $p );
		$actual   = do_shortcode( '[a-z-listing post-type="post" taxonomy="category" terms="test-category"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_multiple_post_type_listing() {
		$title1 = 'Test title 1';
		$post1  = $this->factory->post->create(
			array(
				'post_title' => $title1,
				'post_type'  => 'post',
			)
		);

		$title2 = 'Test title 2';
		$post2  = $this->factory->post->create(
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
		$p     = $this->factory->post->create(
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
		$exclude_id = $this->factory->post->create(
			array(
				'post_title' => 'Must not be visible',
				'post_type'  => 'page',
			)
		);

		$post_title = 'Test Post';
		$p          = $this->factory->post->create(
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
}
