<?php

declare(strict_types=1);

// Load a-z-listing-specific test extension
require_once 'html-assertions.php';

class AZ_Widget_Tests extends WP_UnitTestCase {
	use HtmlAssertions;
	
	public function test_widget() {
		$p = self::factory()->post->create(
			array(
				'post_title' => 'Index Page',
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/default-widget.txt' ), $p );

		$actual = get_the_section_a_z_widget(
			array(
				'before_widget' => '<div>',
				'after_widget'  => '</div>',
				'before_title'  => '<h2>',
				'after_title'   => '</h2>',
			),
			array(
				'title' => 'Test Widget',
				'post'  => $p,
			)
		);

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_widget() {
		$p  = self::factory()->post->create(
			array(
				'post_title' => 'Index Page',
				'post_type'  => 'page',
			)
		);
		$p2 = self::factory()->post->create(
			array(
				'post_title' => 'Test Post',
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-widget.txt' ), $p );

		$actual = get_the_section_a_z_widget(
			array(
				'before_widget' => '<div>',
				'after_widget'  => '</div>',
				'before_title'  => '<h2>',
				'after_title'   => '</h2>',
			),
			array(
				'title' => 'Test Widget',
				'post'  => $p,
			)
		);

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_widget_obsolete_configuration() {
		$p  = self::factory()->post->create(
			array(
				'post_title' => 'Index Page',
				'post_type'  => 'page',
			)
		);
		$p2 = self::factory()->post->create(
			array(
				'post_title' => 'Test Post',
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-widget.txt' ), $p );

		$actual = get_the_section_a_z_widget(
			array(
				'before_widget' => '<div>',
				'after_widget'  => '</div>',
				'before_title'  => '<h2>',
				'after_title'   => '</h2>',
			),
			array(
				'title' => 'Test Widget',
				'page'  => $p,
			)
		);

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_populated_widget_lowercase_titles() {
		$p  = self::factory()->post->create(
			array(
				'post_title' => 'Index Page',
				'post_type'  => 'page',
			)
		);
		$p2 = self::factory()->post->create(
			array(
				'post_title' => 'test post',
				'post_type'  => 'page',
			)
		);

		$expected = sprintf( file_get_contents( 'tests/data/populated-widget.txt' ), $p );

		$actual = get_the_section_a_z_widget(
			array(
				'before_widget' => '<div>',
				'after_widget'  => '</div>',
				'before_title'  => '<h2>',
				'after_title'   => '</h2>',
			),
			array(
				'title' => 'Test Widget',
				'post'  => $p,
			)
		);

		$this->assertHTMLEquals( $expected, $actual );
	}

	public function test_a_z_listing_get_posts_by_title() {
		$p  = self::factory()->post->create(
			array(
				'post_title' => 'Test Page',
				'post_type'  => 'page',
			)
		);
		$p2 = self::factory()->post->create(
			array(
				'post_title' => 'Another Page',
				'post_type'  => 'page',
			)
		);

		$posts = a_z_listing_get_posts_by_title( 'Page', 'page' );
		$this->assertEquals( count( $posts ), 2 );
		$this->assertContains( $posts[0]->ID, array( $p, $p2 ) );
		$this->assertContains( $posts[1]->ID, array( $p, $p2 ) );

		$posts = a_z_listing_get_posts_by_title( 'Test', 'page' );
		$this->assertEquals( count( $posts ), 1 );
		$this->assertEquals( $posts[0]->ID, $p );
	}
}
