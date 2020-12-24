<?php
/**
 * A-Z Listing Alphabet grouping system
 *
 * @package  a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A-Z Listing Alphabet grouping system class
 *
 * @since 2.0.0
 */
class Grouping {
	/**
	 * The configured grouping count
	 *
	 * @since 2.0.0
	 * @var int
	 */
	private $grouping;

	/**
	 * The populated headings for the listing
	 *
	 * @since 2.0.0
	 * @var array<string,array>
	 */
	private $headings;

	/**
	 * Add filters to group the alphabet letters
	 *
	 * @since 2.0.0
	 * @param int $grouping The number of letters in each group.
	 */
	public function __construct( int $grouping ) {
		$this->grouping = $grouping;

		if ( 1 < $grouping ) {
			add_filter( 'a-z-listing-alphabet', array( $this, 'alphabet_filter' ), 2 );
			add_filter( 'the-a-z-letter-title', array( $this, 'heading' ), 5 );
		}
	}

	/**
	 * Remove the filters grouping the alphabet letters
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function teardown() {
		remove_filter( 'a-z-listing-alphabet', array( $this, 'alphabet_filter' ), 2 );
		remove_filter( 'the-a-z-letter-title', array( $this, 'heading' ), 5 );
	}

	/**
	 * Override the alphabet with grouped letters
	 *
	 * @since 2.0.0
	 * @param string $alphabet The alphabet to override.
	 * @return string the new grouped alphabet.
	 */
	public function alphabet_filter( string $alphabet ): string {
		$headings = array();
		$letters  = explode( ',', $alphabet );
		$letters  = array_map( 'trim', $letters );

		$i = 0;
		$j = 0;

		$grouping = $this->grouping;

		$groups = array_reduce(
			$letters,
			/**
			 * Closure to reduce the groups array and populate the headings array
			 *
			 * @param array<int,string> $carry
			 * @param string $letter
			 * @return array<int,string>
			 */
			function( array $carry, string $letter ) use ( $grouping, &$headings, &$i, &$j ) {
				if ( isset( $carry[ $j ] ) ) {
					$carry[ $j ] = $carry[ $j ] . $letter;
				} else {
					$carry[ $j ] = $letter;
				}
				$headings[ $j ][] = Strings::maybe_mb_substr( $letter, 0, 1 );

				if ( $i + 1 === $grouping ) {
					$i = 0;
					$j++;
				} else {
					$i++;
				}

				return $carry;
			},
			array()
		);

		$this->headings = array_reduce(
			$headings,
			/**
			 * Closure to reduce the headings array
			 *
			 * @param array<string,string> $carry
			 * @param string $heading
			 * @return array<string,string>
			 */
			function( array $carry, array $heading ): array {
				$carry[ Strings::maybe_mb_substr( $heading[0], 0, 1 ) ] = $heading;
				return $carry;
			},
			array()
		);

		return join( ',', $groups );
	}

	/**
	 * Override the title of each group
	 *
	 * @since 2.0.0
	 * @param string $title The original title of the group.
	 * @return string The new title for the group.
	 */
	public function heading( string $title ): string {
		if ( isset( $this->headings[ $title ] ) && is_array( $this->headings[ $title ] ) ) {
			$first = $this->headings[ $title ][0];
			$last  = $this->headings[ $title ][ count( $this->headings[ $title ] ) - 1 ];
			return $first . '-' . $last;
		}

		return $title;
	}
}
