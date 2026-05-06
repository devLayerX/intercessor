<?php
/**
 * BerlinDB loader — bundled with Intercessor.
 *
 * Loads all BerlinDB core class files in dependency order and guards against
 * double-loading when a site already has BerlinDB available from another plugin.
 *
 * Load order matches class inheritance:
 * Column → Schema → Row → Table → Query
 *
 * @package Intercessor
 * @since   1.0.0
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

// Guard: do not load bundled classes if BerlinDB is already available site-wide.
if ( class_exists( 'BerlinDB\\Database\\Table', false ) ) {
	return;
}

$intercessor_berlindb_src = __DIR__ . '/core/src/';

/**
 * Ordered list of BerlinDB core class files to load.
 *
 * Files are required in dependency order so that parent classes exist
 * before any child class is parsed.
 *
 * @var string[]
 */
$intercessor_berlindb_files = array(
	'Column.php',
	'Schema.php',
	'Row.php',
	'Table.php',
	'Query.php',
);

foreach ( $intercessor_berlindb_files as $intercessor_berlindb_file ) {
	$path = $intercessor_berlindb_src . $intercessor_berlindb_file;

	if ( ! file_exists( $path ) ) {
		wp_die(
			sprintf(
				/* translators: %s: absolute path to the missing file */
				esc_html__( 'Intercessor: Required BerlinDB file missing: %s', 'intercessor' ),
				esc_html( $path )
			)
		);
	}

	require_once $path;
}
