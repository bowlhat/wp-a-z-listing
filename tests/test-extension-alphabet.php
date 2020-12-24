<?php

declare(strict_types=1);

class AZ_Listing_Alphabet_Extension_Tests extends WP_UnitTestCase {
    public $defaultAlphabet = 'AÁÀÄÂaáàäâ,Bb,CÇcç,Dd,EÉÈËÊeéèëê,Ff,Gg,Hh,IÍÌÏÎiíìïî,Jj,Kk,Ll,Mm,Nn,OÓÒÖÔoóòöô,Pp,Qq,Rr,Ssß,Tt,UÚÙÜÛuúùüû,Vv,Ww,Xx,Yy,Zz';

    public function test_alphabet_is_blank() {
        $expected = $this->defaultAlphabet;
        $actual   = apply_filters( 'a-z-listing-alphabet', $this->defaultAlphabet );

        $this->assertEquals( $expected, $actual );
    }

    public function test_alphabet() {
        $display = 'posts';
        $key = 'alphabet';
        $value = 'abc123';
        $attributes = array( $key => $value );

        apply_filters( "a_z_listing_shortcode_query_for_attribute__{$key}", array(), $display, $key, $value, $attributes );
        $expected = $value;
        $actual   = apply_filters( 'a-z-listing-alphabet', $this->defaultAlphabet );

        $this->assertEquals( $expected, $actual );
    }
}
