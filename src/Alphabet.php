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

/**
 * A-Z Listing Alphabet handler class
 *
 * @since 4.0.0
 */
class Alphabet {
	/**
	 * The index label to use for posts which are not matched by any known letter,
	 * from the $alphabet, such as numerics
	 *
	 * @since 4.0.0
	 * @var string
	 */
	private $unknown_letter;

	/**
	 * All available characters of the alphabet in order. For example, with the
	 * default 26-letter European alphabet indexed `0` through to `25`, where `0`
	 * is `A` and `25` is `Z`. The values in this array are used as the keys for
	 * the `$keyed_alphabet` associative-array.
	 *
	 * @since 4.0.0
	 * @var array<int,string>
	 * @see $keyed_alphabet
	 */
	private $alphabet_keys;

	/**
	 * All available letters in groups of similarity. For example, by default `A`,
	 * `Á`, `À`, `Ä`, `Â`, `a`, `á`, `à`, `ä`, and `â` are all in a single group
	 * whose key is `A`. This is because each of those examples are derived from
	 * the latin letter named `alpha`. The keys used in this associative-array are
	 * stored as values in the `$alphabet_keys` array.
	 *
	 * @since 4.0.0
	 * @var array<string,string>
	 * @see $alphabet_keys
	 */
	private $keyed_alphabet;

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
		 * Filters the alphabet. The string should contain groups of similar or
		 * identical characters separated by commas. The first character in each
		 * group is the one used for the group title.
		 *
		 * @since 1.7.1
		 * @param string $alphabet The $alphabet
		 */
		$alphabet = apply_filters( 'a_z_listing_alphabet', $alphabet );
		/**
		 * Filters the alphabet. The string should contain groups of similar or
		 * identical characters separated by commas. The first character in each
		 * group is the one used for the group title.
		 *
		 * @since 1.7.1
		 * @param string $alphabet The $alphabet.
		 */
		$alphabet = apply_filters( 'a-z-listing-alphabet', $alphabet );

		/**
		 * Specifies the character used for all non-alphabetic titles, such as
		 * numeric titles in the default setup for English. Defaults to '#' unless
		 * overridden by a language pack.
		 *
		 * @since 1.7.1
		 * @param string $non_alpha_char The character for non-alphabetic post titles.
		 */
		$others = apply_filters( 'a_z_listing_non_alpha_char', $others );
		/**
		 * Specifies the character used for all non-alphabetic titles, such as numeric
		 * titles in the default setup for English. Defaults to '#' unless overridden
		 * by a language pack.
		 *
		 * @since 1.7.1
		 * @param string $non_alpha_char The character for non-alphabetic post titles.
		 */
		$others = apply_filters( 'a-z-listing-non-alpha-char', $others );

		$alphabet_groups = explode( ',', $alphabet );
		$letters         = array_reduce(
			$alphabet_groups,
			/**
			 * Closure to extract the alphabet groups
			 *
			 * @param array<string,string> $carry
			 * @param string $group_as_string
			 * @return array<string,string>
			 */
			function( array $carry, string $group_as_string ): array {
				$group_as_array        = Strings::mb_string_to_array( $group_as_string );
				$group_index_character = $group_as_array[0];
				$group_as_array        = array_reduce(
					$group_as_array,
					/**
					 * Closure to extract the letters from each alphabet group
					 *
					 * @param array<string,string> $group_carry
					 * @param string $character
					 * @return array<string,string>
					 */
					function( array $group_carry, string $character ) use ( $group_index_character ) {
						$group_carry[ $character ] = $group_index_character;
						return $group_carry;
					},
					array()
				);
				if ( ! is_array( $carry ) ) {
					return $group_as_array;
				}
				return array_merge( $carry, $group_as_array );
			},
			array()
		);

		$this->unknown_letter = $others;
		$this->alphabet_keys  = array_values( array_unique( $letters ) );
		$this->keyed_alphabet = $letters;
	}

	/**
	 * Get a letter for a key
	 *
	 * @since 4.0.0
	 * @param string $key The key to look up.
	 * @return string The letter.
	 */
	public function get_letter_for_key( string $key ): string {
		if ( in_array( $key, array_keys( $this->keyed_alphabet ), true ) ) {
			return $this->keyed_alphabet[ $key ];
		} else {
			return $this->unknown_letter;
		}
	}

	/**
	 * Get a key for a letter offset
	 *
	 * @since 4.0.0
	 * @param int $offset The offset from the start of the alphabet (0-based).
	 * @return string The alphabet group key.
	 */
	public function get_key_for_offset( int $offset ): string {
		return $this->alphabet_keys[ $offset ];
	}

	/**
	 * Get the character indicating an unknown alphabet letter
	 *
	 * @since 4.0.0
	 * @return string The character.
	 */
	public function get_unknown_letter(): string {
		return $this->unknown_letter;
	}

	/**
	 * Get the alphabet characters.
	 *
	 * @since 4.0.0
	 * @param bool $include_unknown Whether to include the 'unknown' character in the array.
	 * @return array<int,string> The characters.
	 */
	public function chars( bool $include_unknown = false ): array {
		$letters = $this->alphabet_keys;
		if ( $include_unknown ) {
			array_push( $letters, $this->unknown_letter );
		}
		return $letters;
	}

	/**
	 * Count the alphabet
	 *
	 * @since 4.0.0
	 * @param bool $include_unknown Wether to include the 'unknown' character when counting the alphabet.
	 * @return int The number of letters in the alphabet.
	 */
	public function count( bool $include_unknown = false ): int {
		return count( $this->chars( $include_unknown ) );
	}

	/**
	 * Perform an action for every alphabet letter.
	 *
	 * @since 4.0.0
	 * @param callable $callback The function to run on every loop iteration.
	 * @param bool     $include_unknown Whether to include the 'unknown' character in the loop.
	 * @return void
	 */
	public function loop( callable $callback, bool $include_unknown = false ) {
		$alphabet = $this->chars( $include_unknown );
		array_walk( $alphabet, $callback, $this->count( $include_unknown ) );
	}
}
