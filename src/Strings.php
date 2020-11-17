<?php
/**
 * String functions
 *
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * String functions
 */
class Strings {
	/**
	 * Split a multibyte string into an array. (see https://php.net/manual/en/function.mb-split.php#121330)
	 *
	 * @since 1.0.0
	 * @since 2.0.0 moved from Query class to standalone function
	 * @param string $string multi-byte string.
	 * @return array<int,string> individual multi-byte characters from the string
	 */
	public static function mb_string_to_array( string $string ): array {
		if ( extension_loaded( 'mbstring' ) || class_exists( '\\Symfony\\Polyfill\\Mbstring\\Mbstring' ) ) {
			return array_map(
				/**
				 * Closure to extract a multibyte character from a string
				 *
				 * @return false|string
				 */
				function ( int $i ) use ( $string ) {
					return mb_substr( $string, $i, 1 );
				},
				range( 0, mb_strlen( $string ) - 1 )
			);
		} else {
			return explode( '', $string );
		}
	}

	/**
	 * Perform a multibyte substring operation if mbstring is loaded, else use substr.
	 *
	 * @since 2.1.0
	 * @param string $string The string to extract the substring from.
	 * @param int    $start Start the substring at this character number (starts at zero).
	 * @param int    $length Number of characters to include in the substring.
	 * @return string Substring of $string starting at $start with length $length characters.
	 */
	public static function maybe_mb_substr( string $string, int $start, int $length ): string {
		if ( extension_loaded( 'mbstring' ) || class_exists( '\\Symfony\\Polyfill\\Mbstring\\Mbstring' ) ) {
			return mb_substr( $string, $start, $length, 'UTF-8' );
		} else {
			return substr( $string, $start, $length );
		}
	}

	/**
	 * Perform a multibyte split operation if mbstring is loaded, else use explode.
	 *
	 * @param string $separator The character to mark each field.
	 * @param [type] $value     The multibyte string to explode.
	 * @return array<string> The fields extracted from $value by exploding.
	 */
	public static function maybe_mb_split( string $separator, $value = null ): array {
		$result = array();
		if ( is_string( $value ) ) {
			if ( extension_loaded( 'mbstring' ) || class_exists( '\\Symfony\\Polyfill\\Mbstring\\Mbstring' ) ) {
				$result = mb_split( $separator, $value );
			} else {
				$result = explode( $separator, $value );
			}
		} elseif ( is_array( $value ) ) {
			$result = $value;
		}
		$result = array_map( 'trim', $result );
		$result = array_filter( $result );
		return $result;
	}
}
