// Require our dependencies
var autoprefixer = require( 'autoprefixer' );
var babel = require( 'gulp-babel' );
var bourbon = require( 'bourbon' ).includePaths;
var browserSync = require( 'browser-sync' );
var cheerio = require( 'gulp-cheerio' );
var concat = require( 'gulp-concat' );
var minifycss = require( 'gulp-uglifycss' );
var cssnano = require( 'gulp-cssnano' );
var lec = require( 'gulp-line-ending-corrector' );
var del = require( 'del' );
var eslint = require( 'gulp-eslint' );
var gulp = require( 'gulp' );
var gutil = require( 'gulp-util' );
var imagemin = require( 'gulp-imagemin' );
var mqpacker = require( 'css-mqpacker' );
var neat = require( 'bourbon-neat' ).includePaths;
var notify = require( 'gulp-notify' );
var plumber = require( 'gulp-plumber' );
var postcss = require( 'gulp-postcss' );
var reload = browserSync.reload;
var rename = require( 'gulp-rename' );
var sass = require( 'gulp-sass' );
var sassLint = require( 'gulp-sass-lint' );
var sort = require( 'gulp-sort' );
var sourcemaps = require( 'gulp-sourcemaps' );
var svgmin = require( 'gulp-svgmin' );
var svgstore = require( 'gulp-svgstore' );
var uglify = require( 'gulp-uglify' );
var wpPot = require( 'gulp-wp-pot' );
var checktextdomain = require( 'gulp-checktextdomain' );

// Set assets paths.
const paths = {
	'css': [ './*.css', '!*.min.css' ],
	'icons': 'assets/fonts/*.svg',
	'images': [ 'assets/images/*', '!assets/images/*.svg' ],
	'php': [
		'./*.php', './**/*.php', // Include all files		
		'!includes/libraries/**', // Exclude libraries/		
		'!node_modules/**', // Exclude node_modules/		
		'!tests/**', // Exclude tests/		
		'!vendor/**', // Exclude vendor/		
		'!tmp/**' // Exclude tmp/
	],
	'sass': 'assets/css/sass/*.scss',
	'concat_scripts': [ 'assets/js/vendor/*.js', '!assets/js/vendor/*.min.js' ],
	'scripts': [ 'assets/js/*.js', 'assets/js/*.js', '!assets/scripts/*.min.js' ]
};

const browsers = [

	'last 2 version',
	'> 1%',
	'ie >= 11',
	'last 1 Android versions',
	'last 1 ChromeAndroid versions',
	'last 2 Chrome versions',
	'last 2 Firefox versions',
	'last 2 Safari versions',
	'last 2 iOS versions',
	'last 2 Edge versions',
	'last 2 Opera versions'
]

/**
 * Handle errors and alert the user.
 */
function handleErrors () {
	const args = Array.prototype.slice.call( arguments );

	notify.onError( {
		'title': 'Task Failed [<%= error.message %>',
		'message': 'See console.',
		'sound': 'Beep' // See: https://github.com/mikaelbr/node-notifier#all-notification-options-with-their-defaults
	} ).apply( this, args );

	gutil.beep(); // Beep 'sosumi' again.

	// Prevent the 'watch' task from stopping.
	this.emit( 'end' );
}

/**
 * Delete style.css and style.min.css before we minify and optimize
 */
gulp.task( 'clean:styles', () =>
	del( [ 
		'intercessor.css',
		'intercessor.min.css',
		'intercessor-admin.css',
		'intercessor-admin.min.css',
		'intercessor-setup.css',
		'intercessor-setup.min.css'		
	] )
);

/**
 * Delete the svg-icons.svg before we minify, concat.
 */
gulp.task( 'clean:icons', () =>
	del( [ 'assets/images/svg-icons.svg' ] )
);

/**
 * Minify, concatenate, and clean SVG icons.
 *
 * https://www.npmjs.com/package/gulp-svgmin
 * https://www.npmjs.com/package/gulp-svgstore
 * https://www.npmjs.com/package/gulp-cheerio
 */
gulp.task( 'svg', [ 'clean:icons' ], () =>
	gulp.src( paths.icons )

		// Deal with errors.
		.pipe( plumber( {'errorHandler': handleErrors} ) )

		// Minify SVGs.
		.pipe( svgmin() )

		// Add a prefix to SVG IDs.
		.pipe( rename( {'prefix': 'icon-'} ) )

		// Combine all SVGs into a single <symbol>
		.pipe( svgstore( {'inlineSvg': true} ) )

		// Clean up the <symbol> by removing the following cruft...
		.pipe( cheerio( {
			'run': function ( $, file ) {
				$( 'svg' ).attr( 'style', 'display:none' );
				$( '[fill]' ).removeAttr( 'fill' );
				$( 'path' ).removeAttr( 'class' );
			},
			'parserOptions': {'xmlMode': true}
		} ) )

		// Save svg-icons.svg.
		.pipe( gulp.dest( 'assets/images/' ) )
		.pipe( browserSync.stream() )
);

/**
 * Optimize images.
 *
 * https://www.npmjs.com/package/gulp-imagemin
 */
gulp.task( 'imagemin', () =>
	gulp.src( paths.images )
		.pipe( plumber( {'errorHandler': handleErrors} ) )
		.pipe( imagemin( {
			'optimizationLevel': 5,
			'progressive': true,
			'interlaced': true
		} ) )
		.pipe( gulp.dest( 'assets/images' ) )
);

/**
 * Compile Sass and run stylesheet through PostCSS.
 *
 * https://www.npmjs.com/package/gulp-sass
 * https://www.npmjs.com/package/gulp-postcss
 * https://www.npmjs.com/package/gulp-autoprefixer
 * https://www.npmjs.com/package/css-mqpacker
 */
gulp.task( 'postcss', [ 'clean:styles' ], function() {
	return gulp
	gulp.src( 'assets/sass/*.scss', paths.css )

		// Deal with errors.
		.pipe( plumber( {'errorHandler': handleErrors} ) )

		// Wrap tasks in a sourcemap.
		.pipe( sourcemaps.init() )

		// Compile Sass using LibSass.
		.pipe( sass( {
			'includePaths': [].concat( bourbon, neat ),
			'errLogToConsole': true,
			'outputStyle': 'expanded', // Options: nested, expanded, compact, compressed
			'precision': 10
		} ) )

		// Parse with PostCSS plugins.
		.pipe( postcss( [
			autoprefixer( {
				'browsers': browsers
			} ),
			mqpacker( {
				'sort': true
			} )
		] ) )

		// Create sourcemap.
		.pipe( sourcemaps.write() )
		.pipe( lec() )

		// Create the stylesheets.
		.pipe( gulp.dest( 'assets/css/' ) )
		.pipe( browserSync.stream() )
		.pipe( lec() )
		
		.pipe( minifycss({ "maxLineLen": 80 }) )
		.pipe( lec() )
		.pipe( gulp.dest( 'assets/css/' ) )
		.pipe( browserSync.stream() )
});

/**
 * Concatenate and transform JavaScript.
 *
 * https://www.npmjs.com/package/gulp-concat
 * https://github.com/babel/gulp-babel
 * https://www.npmjs.com/package/gulp-sourcemaps
 */
gulp.task( 'concat', () =>
	gulp.src( paths.concat_scripts )

		// Deal with errors.
		.pipe( plumber(
			{'errorHandler': handleErrors}
		) )

		// Start a sourcemap.
		.pipe( sourcemaps.init() )
		.pipe(
			babel({
				presets: [
					[
						'@babel/preset-env', // Preset to compile your modern JS to ES5.
						{
							targets: { browsers: browsers } // Target browser list to support.
						}
					]
				]
			})
		)
		// Concatenate partials into a single script.
		.pipe( concat( 'vendor.js' ) )

		// Append the sourcemap to vendor.js.
		.pipe( sourcemaps.write() )

		// Save vendor.js.
		.pipe( gulp.dest( 'assets/js' ) )
		.pipe( browserSync.stream() )
);

/**
  * Minify compiled JavaScript.
  *
  * https://www.npmjs.com/package/gulp-uglify
  */
gulp.task( 'uglify', [ 'concat' ], () =>
	gulp.src( paths.scripts )
		.pipe( rename( {'suffix': '.min'} ) )
		.pipe( plumber( {'errorHandler': handleErrors} ) )
		.pipe( uglify( {
			'mangle': false
		} ) )
		.pipe( gulp.dest( 'assets/js' ) )
);

/**
 * Delete the theme's .pot before we create a new one.
 */
gulp.task( 'clean:pot', () =>
	del( [ 'languages/intercessor.pot' ] )
);

/**
 * Scan the theme and create a POT file.
 *
 * https://www.npmjs.com/package/gulp-wp-pot
 */
gulp.task( 'wp-pot', [ 'clean:pot' ], () =>
	gulp.src( paths.php )
	.pipe( plumber( {'errorHandler': handleErrors} ) )
	.pipe( sort() )
	.pipe( wpPot( {
		'domain': 'intercessor',
		'destFile': 'intercessor.pot',
		'package': 'Intercessor',
		'bugReport': 'https://github.com/victoraigbeghian/intercessor',
		'lastTranslator': 'Victor Aigbeghian <mail@intercessor.com>',
		'team': 'Team <email@address>'
	} ) )
	.pipe( gulp.dest( 'languages/' ) )
);

gulp.task( 'checktextdomain', () => {
    return gulp
    .src( paths.php )
    .pipe( checktextdomain ( {
        text_domain: 'intercessor', //Specify allowed domain(s)
        keywords: [ //List keyword specifications
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
        ],
    }));
});

/**
 * Sass linting.
 *
 * https://www.npmjs.com/package/sass-lint
 */
gulp.task( 'sass:lint', () =>
	gulp.src( [
		'assets/sass/**/*.scss',
		'!assets/sass/_fonts.scss',
		'!assets/sass/_mixins.scss',
		'!assets/sass/_variables.scss',
		'!node_modules/**'
	] )
		.pipe( sassLint() )
		.pipe( sassLint.format() )
		.pipe( sassLint.failOnError() )
);

/**
 * JavaScript linting.
 *
 * https://www.npmjs.com/package/gulp-eslint
 */
gulp.task( 'js:lint', () =>
	gulp.src( [
		'assets/js/vendor/*.js',
		'assets/js/*.js',
		'!assets/js/vendor.js',
		'!assets/js/*.min.js',
		'!Gruntfile.js',
		'!Gulpfile.js',
		'!node_modules/**'
	] )
		.pipe( eslint() )
		.pipe( eslint.format() )
		.pipe( eslint.failAfterError() )
);

/**
 * Process tasks and reload browsers on file changes.
 *
 * https://www.npmjs.com/package/browser-sync
 */
gulp.task( 'watch', function () {

	// Kick off BrowserSync.
	browserSync( {
		'open': false,             // Open project in a new tab?
		'injectChanges': true,     // Auto inject changes instead of full reload.
		'proxy': 'testing.dev',    // Use http://_s.com:3000 to use BrowserSync.
		'watchOptions': {
			'debounceDelay': 1000  // Wait 1 second before injecting.
		}
	} );

	// Run tasks when files change.
	gulp.watch( paths.icons, [ 'icons' ] );
	gulp.watch( paths.sass, [ 'styles' ] );
	gulp.watch( paths.scripts, [ 'scripts' ] );
	gulp.watch( paths.concat_scripts, [ 'scripts' ] );
	gulp.watch( paths.sprites, [ 'sprites' ] );
	gulp.watch( paths.php, [ 'markup' ] );
} );

/**
 * Create individual tasks.
 */
gulp.task( 'markup', browserSync.reload );
gulp.task( 'i18n', [ 'wp-pot', 'checktextdomain' ] );
gulp.task( 'icons', [ 'svg' ] );
gulp.task( 'scripts', [ 'uglify' ] );
gulp.task( 'styles', [ 'cssnano' ] );
gulp.task( 'sprites', [ 'spritesmith' ] );
gulp.task( 'lint', [ 'sass:lint', 'js:lint' ] );
gulp.task( 'default', [ 'sprites', 'i18n', 'icons', 'styles', 'scripts', 'imagemin'] );