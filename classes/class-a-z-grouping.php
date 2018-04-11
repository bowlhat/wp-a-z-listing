<?php
/**
 * Adds and maintains functionality to group the alphabet letters
 *
 * @since 2.0.0
 */
class A_Z_Grouping {
	/**
	 * Add filters to group the alphabet letters
	 *
	 * @since 2.0.0
	 * @param int $grouping The number of letters in each group
	 */
	public function __construct( $grouping ) {
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
	 * @param string $alphabet The alphabet to override
	 * @return string the new grouped alphabet
	 */
	public function alphabet_filter( $alphabet ) {
		$headings = array();
		$letters  = mb_split( ',', $alphabet );
		$letters  = array_map( 'trim', $letters );

		$i = 0;
		$j = 0;

		$grouping = $this->grouping;

		$groups = array_reduce(
			$letters, function( $carry, $letter ) use ( $grouping, &$headings, &$i, &$j ) {
				if ( ! isset( $carry[ $j ] ) ) {
					$carry[ $j ] = $letter;
				} else {
					$carry[ $j ] = $carry[ $j ] . $letter;
				}
				$headings[ $j ][] = mb_substr( $letter, 0, 1 );

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
			$headings, function( $carry, $heading ) {
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
	 * @param string $title The original title of the group
	 * @return string The new title for the group
	 */
	public function heading( $title ) {
		if ( isset( $this->headings[ $title ] ) && is_array( $this->headings[ $title ] ) ) {
			$first = $this->headings[ $title ][0];
			$last  = $this->headings[ $title ][ count( $this->headings[ $title ] ) - 1 ];
			return $first . '-' . $last;
		}

		return $title;
	}
}