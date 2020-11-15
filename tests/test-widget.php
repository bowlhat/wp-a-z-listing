<?php

declare(strict_types=1);

// Load a-z-listing-specific test extension
require_once 'html-assertions.php';

class AZ_Widget_Tests extends WP_UnitTestCase {
	use HtmlAssertions;
	
	public function test_widget() {
		$p = $this->factory->post->create(
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
		$p  = $this->factory->post->create(
			array(
				'post_title' => 'Index Page',
				'post_type'  => 'page',
			)
		);
		$p2 = $this->factory->post->create(
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
		$p  = $this->factory->post->create(
			array(
				'post_title' => 'Index Page',
				'post_type'  => 'page',
			)
		);
		$p2 = $this->factory->post->create(
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
		$p  = $this->factory->post->create(
			array(
				'post_title' => 'Index Page',
				'post_type'  => 'page',
			)
		);
		$p2 = $this->factory->post->create(
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
}
