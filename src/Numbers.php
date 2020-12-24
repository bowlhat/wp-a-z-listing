<?php
/**
 * A-Z Listing numbers functionality
 *
 * @package  a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds and maintains filters providing number functionality to the alphabet
 *
 * @since 2.0.0
 */
class Numbers {
	/**
	 * Where to place the numbers.
	 *
	 * @var string
	 */
	protected $position = 'hide';

	/**
	 * Whether to group the numbers in a single entry.
	 *
	 * @var boolean
	 */
	protected $group = false;

	/**
	 * Add filters to append or prepend numbers to the alphabet with optional grouping
	 *
	 * @since 2.0.0
	 * @param string $position Can be either "before" or "after" indicating where to place the numbers respective to the alphabet.
	 * @param bool   $group Whether to group the numbers into a single heading or individually.
	 */
	public function __construct( string $position = 'hide', bool $group = false ) {
		if ( 'before' === $position || 'after' === $position ) {
			$this->position = $position;
			$this->group    = a_z_listing_is_truthy( $group );
			add_filter( 'a-z-listing-alphabet', array( $this, 'add_to_alphabet' ) );
			add_filter( 'the-a-z-letter-title', array( $this, 'title' ) );
		}
	}

	/**
	 * Remove the numbers filters we added previously
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function teardown() {
		remove_filter( 'a-z-listing-alphabet', array( $this, 'add_to_alphabet' ) );
		remove_filter( 'the-a-z-letter-title', array( $this, 'title' ) );
	}

	/**
	 * Add numbers to the alphabet
	 *
	 * @since 2.0.0
	 * @param string $alphabet The alphabet to add numbers into.
	 * @return string The alphabet with numbers either prepended or appended
	 */
	public function add_to_alphabet( string $alphabet ): string {
		if ( 'hide' === $this->position ) {
			return $alphabet;
		}

		if ( true === $this->group ) {
			$numbers = '0123456789';
		} else {
			$numbers = '0,1,2,3,4,5,6,7,8,9';
		}

		if ( 'before' === $this->position ) {
			return join( ',', array( $numbers, $alphabet ) );
		} else {
			return join( ',', array( $alphabet, $numbers ) );
		}
	}

	/**
	 * Override the group title for grouped numbers
	 *
	 * @since 2.0.0
	 * @param string $letter The original title of the group.
	 * @return string The new title for the group
	 */
	public function title( string $letter ): string {
		if ( '0' === $letter && true === $this->group ) {
			return '0-9';
		}
		return $letter;
	}
}

/**
 * Sets the A-Z Listing to include numbers.
 *
 * @since 1.7.0
 * @since 1.8.0 Add $group parameter and functionality to group numbers into a single collection.
 *
 * @param string $position set to before to place the numbers first. Any other value will place them last.
 * @param bool   $group    group the numbers in a single collection rather than individually.
 * @return Numbers the numbers extension instance object
 */
function add_a_z_numbers( string $position = 'after', bool $group = false ): Numbers {
	return new Numbers( $position, $group );
}
