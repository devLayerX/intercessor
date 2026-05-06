/**
 * Webpack configuration for the Intercessor plugin's Gutenberg blocks.
 *
 * Standalone config — does NOT spread @wordpress/scripts/config/webpack.config
 * because that config uses a custom getEntries() scanner whose internal format
 * is incompatible with multi-block array configurations when the entry object
 * is overridden externally.
 *
 * Uses @wordpress/dependency-extraction-webpack-plugin directly so WordPress
 * core packages (wp.blocks, wp.element, etc.) are correctly externalised and
 * a .asset.php file is generated for each block.
 *
 * Each block's source lives in:  src/blocks/{name}/index.js
 *                                src/blocks/{name}/block.json
 * Each block's output goes to:   assets/js/blocks/{name}/
 *
 * CopyWebpackPlugin copies block.json alongside the compiled JS on every
 * build and watch cycle. Without this, output.clean:true would delete any
 * manually placed block.json files on each rebuild.
 *
 * Run:
 *   npm run build   — production build
 *   npm run start   — development watch mode
 *
 * @package Intercessor
 */

const path                              = require( 'path' );
const CopyWebpackPlugin                 = require( 'copy-webpack-plugin' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

const BLOCKS = [ 'prayer-form', 'prayer-list', 'prayer-history' ];

const isProduction = process.env.NODE_ENV === 'production';

module.exports = BLOCKS.map( function ( blockName ) {
	return {
		mode:    isProduction ? 'production' : 'development',
		devtool: isProduction ? false : 'source-map',

		entry: path.resolve( __dirname, 'src/blocks/' + blockName + '/index.js' ),

		output: {
			path:     path.resolve( __dirname, 'assets/js/blocks/' + blockName ),
			filename: 'index.js',
			clean:    true,
		},

		module: {
			rules: [
				{
					// Transpile JSX and modern JS via Babel.
					test:    /\.jsx?$/,
					exclude: /node_modules/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: [
								'@babel/preset-env',
								[ '@babel/preset-react', { pragma: 'wp.element.createElement' } ],
							],
						},
					},
				},
				{
					// Pass CSS/SCSS through (editor styles are handled separately).
					test: /\.(sc|sa|c)ss$/,
					use: [ 'style-loader', 'css-loader' ],
				},
			],
		},

		plugins: [
			// Copy block.json from src/blocks/{name}/ → assets/js/blocks/{name}/
			// on every build and watch cycle. Must run before clean so the file
			// is written into the fresh output directory, not the deleted one.
			new CopyWebpackPlugin( {
				patterns: [
					{
						from: path.resolve( __dirname, 'src/blocks/' + blockName + '/block.json' ),
						to:   path.resolve( __dirname, 'assets/js/blocks/' + blockName + '/block.json' ),
					},
				],
			} ),

			// Externalises all @wordpress/* imports and generates a
			// {blockName}/index.asset.php file listing script dependencies
			// and a version hash for cache-busting.
			new DependencyExtractionWebpackPlugin(),
		],

		externals: {
			// Guarantee wp.* globals are externalised even if the plugin
			// doesn't pick them up automatically for any reason.
			'@wordpress/blocks':       [ 'wp', 'blocks' ],
			'@wordpress/block-editor': [ 'wp', 'blockEditor' ],
			'@wordpress/components':   [ 'wp', 'components' ],
			'@wordpress/element':      [ 'wp', 'element' ],
			'@wordpress/i18n':         [ 'wp', 'i18n' ],
		},

		resolve: {
			extensions: [ '.js', '.jsx' ],
		},

		performance: {
			hints: false,
		},
	};
} );
