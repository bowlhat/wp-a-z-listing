<?php
/**
 * Alphabet class
 * 
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Alphabet {
	/**
	 * All available characters in a single string for translation support
	 *
	 * @var \A_Z_Listing\Alphabet
	 */
	private $alphabet;

	/**
	 * The index label to use for posts which are not matched by any known letter, from the $alphabet, such as numerics
	 *
	 * @var string
	 */
	private $unknown_letters;

	/**
	 * All available characters which may be used as an index
	 *
	 * @var array
	 */
	private $alphabet_chars;

 	/**
	 * Build a translated alphabet
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		/* translators: List the aphabet of your language in the order that your language prefers. list as groups of identical meanings but different characters together, e.g. in English we group A with a because they are both the same letter but different character-code. Each character group should be followed by a comma separating it from the next group. Any amount of characters per group are acceptible, and there is no requirement for all the groups to contain the same amount of characters as all the others. Be careful with the character you place first in each group as that will be used as the identifier for the group which gets displayed on the page, e.g. in English a group definition of "Aa" will indicate that we display all the posts in the group, i.e. whose titles begin with either "A" or "a", listed under a heading of "A" (the first character in the definition). */
		$alphabet = __( 'AÁÀÄÂaáàäâ,Bb,CÇcç,Dd,EÉÈËÊeéèëê,Ff,Gg,Hh,IÍÌÏÎiíìïî,Jj,Kk,Ll,Mm,Nn,OÓÒÖÔoóòöô,Pp,Qq,Rr,Ssß,Tt,UÚÙÜÛuúùüû,Vv,Ww,Xx,Yy,Zz', 'a-z-listing' );
		/* translators: This should be a single character to denote "all entries that didn't fit under one of the alphabet character groups defined". This is used in English to categorise posts whose title begins with a numeric (0 through to 9), or some other character that is not a standard English alphabet letter. */
		$others = __( '#', 'a-z-listing' );

		/**
		 * Filters the alphabet. The string should contain groups of similar or identical characters separated by commas. The first character in each group is the one used for the group title.
		 *
		 * @param string $alphabet The $alphabet
		 */
		$alphabet = apply_filters( 'a_z_listing_alphabet', $alphabet );
		/**
		 * Filters the alphabet. The string should contain groups of similar or identical characters separated by commas. The first character in each group is the one used for the group title.
		 *
		 * @since 1.7.1
		 * @param string $alphabet The $alphabet.
		 */
		$alphabet = apply_filters( 'a-z-listing-alphabet', $alphabet );

		/**
		 * Specifies the character used for all non-alphabetic titles, such as numeric titles in the default setup for English. Defaults to '#' unless overridden by a language pack.
		 *
		 * @param string $non_alpha_char The character for non-alphabetic post titles.
		 */
		$others = apply_filters( 'a_z_listing_non_alpha_char', $others );
		/**
		 * Specifies the character used for all non-alphabetic titles, such as numeric titles in the default setup for English. Defaults to '#' unless overridden by a language pack.
		 *
		 * @since 1.7.1
		 * @param string $non_alpha_char The character for non-alphabetic post titles.
		 */
		$others = apply_filters( 'a-z-listing-non-alpha-char', $others );

		$alphabet_groups = explode( ',', $alphabet );
		$letters         = array_reduce(
			$alphabet_groups,
			function( $return, $group ) {
				$group                 = \A_Z_Listing\mb_string_to_array( $group );
				$group_index_character = $group[0];
				$group                 = array_reduce(
					$group,
					function( $group, $character ) use ( $group_index_character ) {
						$group[ $character ] = $group_index_character;
						return $group;
					}
				);
				if ( ! is_array( $return ) ) {
					return $group;
				}
				return array_merge( $return, $group );
			}
		);

        $this->unknown          = $others;
        $this->alphabet         = array_values( array_unique( $letters ) );
        $this->indexed_alphabet = $letters;
    }

    /**
     * Get an index for an alphabet letter
     * 
     * @since 4.0.0
     * @param string The letter.
     * @return array The index for the letter.
     */
    public function get_letter( string $index ): array {
        if ( in_array( $index, array_keys( $this->indexed_alphabet ), true ) ) {
            $index = $this->indexed_alphabet[ $index ];
        } else {
            $index = $this->unknown;
        }
    }

    /**
     * Get the character indicating an unknown alphabet letter
     * 
     * @since 4.0.0
     * @return string The character.
     */
    public function unknown_letter(): string {
        return $this->unknown;
    }

    /**
     * Get the alphabet characters.
     * 
     * @since 4.0.0
     * @param bool Whether to include the 'unknown' character in the array.
     * @return array The characters.
     */
    public function chars( bool $include_unknown = false ) {
        $local_alphabet = $this->alphabet;
        if ( $include_unknown ) {
            array_push( $local_alphabet, $this->unknown_letter );
        }
        return $local_alphabet;
    }

    /**
     * Count the alphabet
     * 
     * @since 4.0.0
     * @param bool Wether to include the 'unknown' character when counting the alphabet.
     * @return int The number of letters in the alphabet.
     */
    public function count( bool $include_unknown = false ) {
        return count( $this->chars( $include_unknown ) );
    }

    /**
     * Perform an action for every alphabet letter.
     * 
     * @since 4.0.0
     * @param bool $include_unknown Whether to include the 'unknown' character in the loop.
     * @param callable $callback The function to run on every loop iteration.
     */
    public function loop( bool $include_unknown = false, callable $callback = null ) {
        array_walk( $this->chars( $include_unknown ), $callback, $this->count( $include_unknown ) );
    }
}