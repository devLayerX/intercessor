/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

    grunt.loadNpmTasks('gruntify-eslint');
    require('load-grunt-tasks')(grunt);
    var pkg = grunt.file.readJSON('package.json');
    var compactBannerTemplate = '/** ' + '<%= pkg.name %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %> | <%= pkg.author.url %> | Copyright (c) <%= grunt.template.today("yyyy") %>; | Licensed GPLv3+' + ' **/\n';
	var sass = require( 'node-sass' );

   // Project configuration
    grunt.initConfig({
        pkg: pkg,
        watch: {
            styles: {
                files: [
                    'assets/**/*.css',
                    'assets/**/*.scss'
                ],
                tasks: ['styles'],
                options: {
                    spawn: false,
                    livereload: true,
                    debounceDelay: 500
                }
            },
            scripts: {
                files: ['assets/**/*.js'],
                tasks: ['scripts'],
                options: {
                    spawn: false,
                    livereload: true,
                    debounceDelay: 500
                }
            },
            php: {
                files: [
                    '**/*.php',
                    '!vendor/**.*.php'
                ],
                tasks: ['php'],
                options: {
                    spawn: false,
                    debounceDelay: 500
                }
            }
        },
        // Generate POT files.
        makepot: {
            options: {
                type: 'wp-plugin',
                domainPath: '/languages/',
                potHeaders: {
                    'report-msgid-bugs-to': 'https://github.com/victoraigbeghian/intercessor/issues',
                    'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
                }
            },
            dist: {
                options: {
                    potFilename: 'intercessor.pot',
                    exclude: [
                        'vendor/.*',
                        'tests/.*',
                        'tmp/.*'
                    ]
                }
            }
        },
        // Add textdomain.
        addtextdomain: {
            dist: {
                options: { textdomain: pkg.name },
                target: { files: { src: ['**/*.php'] } }
            }
        },
        // Check textdomain errors.
        checktextdomain: {
            options: {
                text_domain: 'intercessor',
                keywords: [
                    '__:1,2d',
                    '_e:1,2d',
                    '_x:1,2c,3d',
                    'esc_html__:1,2d',
                    'esc_html_e:1,2d',
                    'esc_html_x:1,2c,3d',
                    'esc_attr__:1,2d',
                    'esc_attr_e:1,2d',
                    'esc_attr_x:1,2c,3d',
                    '_ex:1,2c,3d',
                    '_n:1,2,4d',
                    '_nx:1,2,4c,5d',
                    '_n_noop:1,2,3d',
                    '_nx_noop:1,2,3c,4d'
                ]
            },
            files: {
                src: [
                    '**/*.php', // Include all files
                    '!node_modules/**', // Exclude node_modules/
                    '!tests/**', // Exclude tests/
                    '!vendor/**', // Exclude vendor/
                    '!tmp/**'    // Exclude tmp/
                ],
                expand: true
            }
        },
        replace: {
            version_php: {
                src: [
                    '**/*.php',
                    '!vendor/**'
                ],
                overwrite: true,
                replacements: [
                    {
                        from: /Version:(\s*?)[a-zA-Z0-9\.\-\+]+$/m,
                        to: 'Version:$1' + pkg.version
                    },
                    {
                        from: /@version(\s*?)[a-zA-Z0-9\.\-\+]+$/m,
                        to: '@version$1' + pkg.version
                    },
                    {
                        from: /@since(.*?)NEXT/gm,
                        to: '@since$1' + pkg.version
                    },
                    {
                        from: /VERSION(\s*?)=(\s*?['"])[a-zA-Z0-9\.\-\+]+/gm,
                        to: 'VERSION$1=$2' + pkg.version
                    }
                ]
            },
            version_readme: {
                src: 'README.md',
                overwrite: true,
                replacements: [{
                        from: /^\*\*Stable tag:\*\*(\s*?)[a-zA-Z0-9.-]+(\s*?)$/im,
                        to: '**Stable tag:**$1<%= pkg.version %>$2'
                    }]
            },
            readme_txt: {
                src: 'README.md',
                dest: 'release/' + pkg.version + '/readme.txt',
                replacements: [
                    {
                        from: /^# (.*?)( #+)?$/gm,
                        to: '=== $1 ==='
                    },
                    {
                        from: /^## (.*?)( #+)?$/gm,
                        to: '== $1 =='
                    },
                    {
                        from: /^### (.*?)( #+)?$/gm,
                        to: '= $1 ='
                    },
                    {
                        from: /^\*\*(.*?):\*\*/gm,
                        to: '$1:'
                    }
                ]
            }
        },
        copy: {
            release: {
                src: [
                    '**',
                    '!assets/js/components/**',
                    '!assets/css/sass/**',
                    '!assets/repo/**',
                    '!bin/**',
                    '!release/**',
                    '!tests/**',
                    '!node_modules/**',
                    '!**/*.md',
                    '!.travis.yml',
                    '!.bowerrc',
                    '!.gitignore',
                    '!bower.json',
                    '!Dockunit.json',
                    '!Gruntfile.js',
                    '!package.json',
                    '!phpunit.xml'
                ],
                dest: 'release/' + pkg.version + '/'
            },
            svn: {
                cwd: 'release/<%= pkg.version %>/',
                expand: true,
                src: '**',
                dest: 'release/svn/'
            }
        },
        compress: {
            dist: {
                options: {
                    mode: 'zip',
                    archive: './release/<%= pkg.name %>.<%= pkg.version %>.zip'
                },
                expand: true,
                cwd: 'release/<%= pkg.version %>',
                src: ['**/*'],
                dest: '<%= pkg.name %>'
            }
        },
        wp_deploy: {
            dist: {
                options: {
                    plugin_slug: '<%= pkg.name %>',
                    build_dir: 'release/svn/',
                    assets_dir: 'assets/repo/'
                }
            }
        },
        clean: {
            release: [
                'release/<%= pkg.version %>/',
                'release/svn/'
            ]
        },
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'Gruntfile.js',
				'assets/js/admin/*.js',
				'!assets/js/admin/*.min.js',
				'assets/js/frontend/*.js',
				'!assets/js/frontend/*.min.js'
			]
		},
        eslint: {
            src: [
                'assets/js/**/*.js',
                '!**/*.min.js'
            ]
        },
        uglify: {
			options: { banner: compactBannerTemplate },
			main_js: {
				files: {
					'assets/js/frontend/intercessor.min.js': ['assets/js/frontend/intercessor.js'  ],
					'assets/js/admin/intercessor-admin.min.js': [ 'assets/js/admin/intercessor-admin.js' ]
				}
			}
        },
        sass: {
            options: {
				implementation: sass,
				sourceMap: 'none'
			},
            dist: {
                files: {
					'assets/css/intercessor.css': 'assets/sass/intercessor.scss',
					'assets/css/intercessor-admin.css': 'assets/sass/intercessor-admin.scss',
					'assets/css/intercessor-setup.css': 'assets/sass/intercessor-setup.scss',
					'assets/css/recent-prayers.css': 'assets/sass/recent-prayers.scss',
					'assets/css/date-picker.css': 'assets/sass/date-picker.scss'
				}
            }
        },
        cssmin: {
			dist: {
				files: {
					'assets/css/date-picker.min.css': 'assets/css/date-picker.css',
					'assets/css/intercessor.min.css': 'assets/css/intercessor.css',
					'assets/css/intercessor-admin.min.css': 'assets/css/intercessor-admin.css',
					'assets/css/intercessor-setup.min.css': 'assets/css/intercessor-setup.css',
					'assets/css/recent-prayers.min.css': 'assets/css/recent-prayers.css'
				}
			}
		},
		// Autoprefixer.
		postcss: {
			options: {
				map: {
					inline: false, // save all sourcemaps as separate files...
					annotation: 'dist/css/maps/' // ...to the specified directory
				},

				processors: [
					require('autoprefixer')({browsers: 'last 2 versions'}), // add vendor prefixes
					require('cssnano')() // minify the result
				]
			},
			dist: {
			  src: 'assets/css/*.css'
			}
		}

    });

	// Load NPM tasks to be used here.
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( '@lodder/grunt-postcss' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-checktextdomain' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );


    grunt.registerTask('default', [
        'styles',
        'scripts',
        'i18n'
    ]);

    grunt.registerTask('scripts', [
        'jshint',
        'uglify'
    ]);

    grunt.registerTask('styles', [
        'sass',
		'postcss',
        'cssmin'
    ]);

    grunt.registerTask('i18n', [
        'addtextdomain',
        'makepot',
        'checktextdomain'
    ]);

    grunt.registerTask('version', [
        'default',
        'replace:version_php',
        'replace:version_readme'
    ]);

    grunt.registerTask('release', [
        'clean:release',
        'replace:readme_txt',
        'copy',
        'compress',
        'wp_deploy'
    ]);

    grunt.util.linefeed = '\n';
};
