<?php
/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command line arguments will be applied
 * after this file is read.
 *
 * @package a-z-listing
 */

return [

	// Supported values: `'5.6'`, `'7.0'`, `'7.1'`, `'7.2'`, `'7.3'`, `'7.4'`, `null`.
	// If this is set to `null`,
	// then Phan assumes the PHP version which is closest to the minor version
	// of the php executable used to execute Phan.
	'target_php_version'              => '7.0',

	// A list of directories that should be parsed for class and
	// method information. After excluding the directories
	// defined in exclude_analysis_directory_list, the remaining
	// files will be statically analyzed for errors.
	//
	// Thus, both first-party and third-party code being used by
	// your application should be included in this list.
	'directory_list'                  => [
		'.',
		'../wordpress',
	],

	'exclude_file_regex'              => '@^./((?:.*/)?tests?/|bin/|obj/|node_modules/|scripts/|templates/|\..+/)@',

	// A directory list that defines files that will be excluded
	// from static analysis, but whose class and method
	// information should be included.
	//
	// Generally, you'll want to include the directories for
	// third-party code (such as "vendor/") in this list.
	//
	// n.b.: If you'd like to parse but not analyze 3rd
	// party code, directories containing that code
	// should be added to the `directory_list` as
	// to `exclude_analysis_directory_list`.
	'exclude_analysis_directory_list' => [
		'vendor/',
		'../wordpress',
	],

	// A list of plugin files to execute.
	// See https://github.com/phan/phan/tree/master/.phan/plugins for even more.
	// Pass these in as relative paths.
	// Base names without extensions such as 'AlwaysReturnPlugin'
	// can be used to refer to a plugin that is bundled with Phan.
	'plugins'                         => [
		// checks if a function, closure or method unconditionally returns.

		// can also be written as 'vendor/phan/phan/.phan/plugins/AlwaysReturnPlugin.php'.
		'AlwaysReturnPlugin',
		// Checks for syntactically unreachable statements in
		// the global scope or function bodies.
		'UnreachableCodePlugin',
		'DollarDollarPlugin',
		'DuplicateArrayKeyPlugin',
		'PregRegexCheckerPlugin',
		'PrintfCheckerPlugin',
		'UseReturnValuePlugin',
		'NonBoolBranchPlugin',
		'HasPHPDocPlugin',
		'InvalidVariableIssetPlugin',
		'NotFullyQualifiedUsagePlugin',
		'NumericalComparisonPlugin',
		'UnknownElementTypePlugin',
		'DuplicateExpressionPlugin',
		'WhitespacePlugin',
		'SuspiciousParamOrderPlugin',
		'PossiblyStaticMethodPlugin',
		// 'PHPDocToRealTypesPlugin',
		'PHPDocRedundantPlugin',
		'PreferNamespaceUsePlugin',
		'StrictComparisonPlugin',
		'EmptyMethodAndFunctionPlugin',
	],

	'plugin_config'                   => [],
];