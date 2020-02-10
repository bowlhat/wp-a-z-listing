<?php

// Load a-z-listing-specific test extension
require_once 'html-assertions.php';

class AZ_Shortcode_Taxonomies_Tests extends WP_UnitTestCase {
	use HtmlAssertions;

	public function test_populated_taxonomy_listing() {
		$title = 'test category';
		$t     = $this->factory->term->create(
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
		
		$expected = sprintf( file_get_contents( 'tests/data/populated-multiple-taxonomy-listing.txt' ), $cat_title, $cat, $tag_title, $tag );
		$actual   = do_shortcode( '[a-z-listing display="terms" taxonomy="category,post_tag"]' );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_taxonomy_child_terms_by_id_in_id_attribute_listing() {
		$cat_title = 'Parent-Category';
		$cat_slug  = 'parent-category';
		$cat       = $this->factory->term->create(
			array(
				'slug'     => $cat_slug,
				'name'     => $cat_title,
				'taxonomy' => 'category',
			)
		);
		$title = 'test category';
		$t     = $this->factory->term->create(
			array(
				'parent'   => $cat,
				'name'     => $title,
				'taxonomy' => 'category',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing.txt' ), $cat_title, $cat, $title, $t );
		$actual   = do_shortcode( sprintf( '[a-z-listing display="terms" taxonomy="category,post_tag" parent-term-id="%s"]', $cat ) );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_taxonomy_child_terms_by_id_in_slug_attribute_listing() {
		$cat_title = 'Parent-Category';
		$cat_slug  = 'parent-category';
		$cat       = $this->factory->term->create(
			array(
				'slug'     => $cat_slug,
				'name'     => $cat_title,
				'taxonomy' => 'category',
			)
		);
		$title = 'test category';
		$t     = $this->factory->term->create(
			array(
				'parent'   => $cat,
				'name'     => $title,
				'taxonomy' => 'category',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing.txt' ), $cat_title, $cat, $title, $t );
		$actual   = do_shortcode( sprintf( '[a-z-listing display="terms" taxonomy="category,post_tag" parent-term="%s"]', $cat ) );

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_taxonomy_child_terms_by_slug_in_slug_attribute_listing() {
		$cat_title = 'Parent-Category';
		$cat_slug  = 'parent-category';
		$cat       = $this->factory->term->create(
			array(
				'slug'     => $cat_slug,
				'name'     => $cat_title,
				'taxonomy' => 'category',
			)
		);
		$title = 'test category';
		$t     = $this->factory->term->create(
			array(
				'parent'   => $cat,
				'name'     => $title,
				'taxonomy' => 'category',
			)
		);
		
		$expected = sprintf( file_get_contents( 'tests/data/populated-taxonomy-listing.txt' ), $cat_title, $cat, $title, $t );
		$actual   = do_shortcode( sprintf( '[a-z-listing display="terms" taxonomy="category,post_tag" parent-term="%s"]', $cat_slug ) );

		$this->assertHTMLEquals( $expected, $actual );
	}
}
