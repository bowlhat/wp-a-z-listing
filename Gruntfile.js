module.exports = function( grunt ) {

	'use strict';

	const sass = require( 'node-sass' );

	// Project configuration
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		addtextdomain: {
			options: {
				textdomain: 'a-z-listing',
			},
			update_all_domains: {
				options: {
					updateDomains: true
				},
				src: [ '*.php', '**/*.php', '!\.git/**/*', '!bin/**/*', '!node_modules/**/*', '!tests/**/*', '!build/**/*' ]
			}
		},

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				},
				options: {
					screenshot_url: 'assets/{screenshot}.png',
					pre_convert: function( readme ) {
						readme = readme.replace( new RegExp('^`$[\n\r]+([^`]*)[\n\r]+^`$','gm'), function( codeblock, codeblockContents ) {
							const blockStartEnd = '```';
							let lines = codeblockContents.split('\n');
							if ( String( lines[0] ).startsWith('<?php') ) {
								return `${blockStartEnd}php\n${lines.join('\n')}\n${blockStartEnd}`;
							}
						});
						return readme;
					},
					post_convert: function( readme ) {
						readme = readme.replace( /^## Description ##$/, function( title ) {
							return `${title}\n\n[![Build Status](https://travis-ci.org/bowlhat/wp-a-z-listing.svg?branch=master)](https://travis-ci.org/bowlhat/wp-a-z-listing)\n\n`;
						});
						readme = readme.replace( /^\*\*([^*\s][^*]*)\*\*$/gm, function( a, b ) {
							return `#### ${b} ####`;
						});
						readme = readme.replace( /^\*([^*\s][^*]*)\*$/gm, function( a, b ) {
							return `##### ${b} #####`;
						});
						return readme;
					}
				}
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					exclude: [ '\.git/*', 'bin/*', 'node_modules/*', 'tests/*' ],
					mainFile: 'a-z-listing.php',
					potFilename: 'a-z-listing.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: true
				}
			}
		},

		// babel: {
		// 	options: {
		// 		sourceMap: true,
		// 		plugins: [['transform-react-jsx', {"pragma": "wp.element.createElement"}]],
		// 		presets: ['env', 'react']
		// 	},
		// 	jsx: {
		// 		files: [{
		// 			expand: true,
		// 			src: ['**/*.jsx'],
		// 			ext: '.js'
		// 		}]
		// 	}
		// },

		sass: {
			options: {
				sourceMap: true,
				implementation: sass
			},
			dist: {
				files: {
					'css/a-z-listing-default.css': 'css/a-z-listing-default.scss',
					'css/a-z-listing-customize.css': 'css/a-z-listing-customize.scss'
				}
			}
		},
	} );

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	// grunt.loadNpmTasks( 'grunt-babel' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.registerTask( 'default', ['build'] );
	grunt.registerTask( 'build', [ 'i18n','readme',
		// 'babel',
		'sass' ] );
	grunt.registerTask( 'i18n', ['addtextdomain', 'makepot'] );
	grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );

	grunt.util.linefeed = '\n';

};
