<?php
/**
 * Extension Interface
 * 
 * @package a-z-listing
 */

declare(strict_types=1);

namespace A_Z_Listing;

interface Extension {
    public static function instance(): Extension;
    public function activate( String $file = '', String $plugin = '' ): Extension;
    public function initialize();
}
