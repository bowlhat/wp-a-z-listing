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
 * Adds and maintains functionality to group the alphabet letters
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
	 * @var array
	 */
	private $headings;

	/**
	 * Perform a multibyte substring operation if mbstring is loaded, else use substr.
	 *
	 * @since 2.1.0
	 * @param string $string The string to extract the substring from.
	 * @param int    $start Start the substring at this character number (starts at zero).
	 * @param int    $length Number of characters to include in the substring.
	 * 
	 * @return string Substring of $string starting at $start with length $length characters.
	 */
	public static function maybe_mb_substr( string $string, int $start, int $length ): string {
		if ( extension_loaded( 'mbstring' ) ) {
			return mb_substr( $string, $start, $length, 'UTF-8' );
		}
		return substr( $string, $start, $length );
	}

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
			function( array $carry, string $letter ) use ( $grouping, &$headings, &$i, &$j ) {
				if ( ! isset( $carry[ $j ] ) ) {
					$carry[ $j ] = $letter;
				} else {
					$carry[ $j ] = $carry[ $j ] . $letter;
				}
				$headings[ $j ][] = \A_Z_Listing\Grouping::maybe_mb_substr( $letter, 0, 1 );

				if ( $i + 1 === $grouping ) {
					$i = 0;
					$j++;
				} else {
					$i++;
				}

				return $carry;
			}
		);

		$this->headings = array_reduce(
			$headings,
			function( array $carry, string $heading ) {
				$carry[ mb_substr( $heading[0], 0, 1 ) ] = $heading;
				return $carry;
			}
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
