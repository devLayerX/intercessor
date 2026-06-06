/**
 * Package the Intercessor plugin into a WordPress.org-ready zip.
 *
 * Usage:  node scripts/package-plugin.js
 *    or:  npm run package        (builds first, then zips)
 *    or:  npm run zip            (zips without rebuilding)
 *
 * Output: dist/intercessor-{version}.zip
 *
 * @package Intercessor
 * @since   1.0.0
 */

const { ZipArchive } = require( 'archiver' );
const fs             = require( 'fs' );
const path           = require( 'path' );

// ---------------------------------------------------------------------------
// 1. Read the plugin version from the main file header.
// ---------------------------------------------------------------------------
const mainFile     = fs.readFileSync( 'intercessor.php', 'utf8' );
const versionMatch = mainFile.match( /^\s*\*?\s*Version:\s*(\S+)/m );
const version      = versionMatch ? versionMatch[ 1 ] : '0.0.0';

// ---------------------------------------------------------------------------
// 2. Prepare the output path.
// ---------------------------------------------------------------------------
const distDir    = path.resolve( 'dist' );
const outputName = `intercessor-${ version }.zip`;
const outputPath = path.join( distDir, outputName );

fs.mkdirSync( distDir, { recursive: true } );

if ( fs.existsSync( outputPath ) ) {
	fs.unlinkSync( outputPath );
}

// ---------------------------------------------------------------------------
// 3. Create the zip archive.
// ---------------------------------------------------------------------------
const output  = fs.createWriteStream( outputPath );
const archive = new ZipArchive( { zlib: { level: 9 } } );

archive.on( 'warning', ( err ) => {
	if ( err.code !== 'ENOENT' ) {
		throw err;
	}
	console.warn( '⚠️  ', err.message );
} );

archive.pipe( output );

// ---------------------------------------------------------------------------
// 4. Add plugin files, excluding anything that should not ship.
//
//    Everything matched by the glob is placed under an "intercessor/" prefix
//    inside the zip, which is the structure WordPress.org expects.
//
//    To customise what ships, edit the ignore list below.
// ---------------------------------------------------------------------------
archive.glob(
	'**/*',
	{
		cwd:    process.cwd(),
		dot:    false,
		ignore: [
			// ── Build tooling & config ──────────────────────────────
			'node_modules/**',
			'package.json',
			'package-lock.json',
			'webpack.config.js',
			'composer.json',
			'composer.lock',
			'phpcs.xml',
			'phpunit.xml',
			'phpunit.xml.dist',

			// ── CI / repo meta ─────────────────────────────────────
			'.github/**',
			'.gitignore',
			'.npmrc',
			'README.md',

			// ── Tests ──────────────────────────────────────────────
			'tests/**',

			// ── This script & its output ───────────────────────────
			'scripts/**',
			'dist/**',

			// ── Stray files ────────────────────────────────────────
			'*.zip',
			'.DS_Store',
			'**/.DS_Store',
		],
	},
	{ prefix: 'intercessor' }
);

archive.finalize().then( () => {
	const kb = ( fs.statSync( outputPath ).size / 1024 ).toFixed( 0 );
	console.log( `\n✅  ${ outputName }  (${ kb } KB)` );
	console.log( `    ${ outputPath }\n` );
} ).catch( ( err ) => {
	console.error( '❌  Packaging failed:', err.message );
	process.exit( 1 );
} );
