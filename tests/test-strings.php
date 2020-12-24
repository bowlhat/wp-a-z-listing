<?php

declare(strict_types=1);

class AZ_String_Tests extends WP_UnitTestCase {
    public function test_mb_string_to_array() {
        $input = "é€á";
        $expected = ["\xc3\xa9","\xe2\x82\xac","\xc3\xa1"];
        $this->assertSame( $expected, \A_Z_Listing\Strings::mb_string_to_array( $input ) );
    }

    public function test_maybe_mb_substr() {
        $input = "é€á";
        $expected = "\xc3\xa1";
        $this->assertSame( $expected, \A_Z_Listing\Strings::maybe_mb_substr( $input, 2, 1 ) );
    }
}