<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    // The prefix configuration. If a non null value will be used, a random prefix will be generated.
    'prefix' => 'A_Z_Listing',

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        Finder::create()->files()->in('src'),
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->exclude([
                'doc',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
                'composer',
            ])
            ->in('vendor'),
        Finder::create()->append([
            'composer.json',
        ]),
    ],

    // Whitelists a list of files. Unlike the other whitelist related features, this one is about completely leaving
    // a file untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    'files-whitelist' => [
        // 'src/a-whitelisted-file.php',
    ],

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper#patchers
    'patchers' => [
        function (string $filePath, string $prefix, string $contents): string {
            // $contents = str_replace( "'Composer\\\\Autoload\\\\ClassLoader'", "'$prefix\\\\Composer\\\\Autoload\\\\ClassLoader'", $contents );
            return $contents;
        },
        function (string $filePath, string $prefix, string $contents): string {
            $contents = str_replace( '\A_Z_Listing\WP_Post', '\WP_Post', $contents );
            $contents = str_replace( '\A_Z_Listing\WP_Term', '\WP_Term', $contents );
            $contents = str_replace( '\A_Z_Listing\WP_Query', '\WP_Query', $contents );
            $contents = str_replace( '\A_Z_Listing\WP_Error', '\WP_Error', $contents );
            return $contents;
        },
        function (string $filePath, string $prefix, string $contents): string {
            $contents = str_replace( 'A_Z_Listing\mb_str_split', 'mb_str_split', $contents );
            $contents = str_replace( 'return \A_Z_Listing\mb_str_split(...func_get_args())', 'return p::mb_str_split($string, $split_length, $encoding)', $contents );
            $contents = str_replace( '\Symfony\Polyfill\Mbstring\Mbstring::', 'p::', $contents );
            $contents = str_replace( "'\\\\Symfony\\\\Polyfill\\\\Mbstring\\\\Mbstring'", "'\\\\$prefix\\\\Symfony\\\\Polyfill\\\\Mbstring\\\\Mbstring'", $contents );
            $contents = str_replace( '\A_Z_Listing\apcu_fetch', '\apcu_fetch', $contents );
            return $contents;
        },
    ],

    // PHP-Scoper's goal is to make sure that all code for a project lies in a distinct PHP namespace. However, you
    // may want to share a common API between the bundled code of your PHAR and the consumer code. For example if
    // you have a PHPUnit PHAR with isolated code, you still want the PHAR to be able to understand the
    // PHPUnit\Framework\TestCase class.
    //
    // A way to achieve this is by specifying a list of classes to not prefix with the following configuration key. Note
    // that this does not work with functions or constants neither with classes belonging to the global namespace.
    //
    // Fore more see https://github.com/humbug/php-scoper#whitelist
    'whitelist' => [
        // 'PHPUnit\Framework\TestCase',   // A specific class
        // 'PHPUnit\Framework\*',          // The whole namespace
        // '*',                            // Everything
        'Symfony\Polyfill\*',
    ],

    // If `true` then the user defined constants belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-constants' => true,

    // If `true` then the user defined classes belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-classes' => true,

    // If `true` then the user defined functions belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-functions' => true,
];
