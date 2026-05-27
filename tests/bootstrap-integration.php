<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;
/**
 * Bootstrap for integration tests (requires a real WordPress + test DB).
 *
 * Integration tests run against the WordPress test suite provided by the
 * wordpress-develop repository. Set WP_TESTS_DIR to the path of that
 * directory before running, or define it in a local .env file loaded by
 * your CI environment.
 *
 * Typical local setup:
 *   export WP_TESTS_DIR=/tmp/wordpress-tests-lib
 *   export WP_TESTS_DB_NAME=intercessor_tests
 *   export WP_TESTS_DB_USER=root
 *   export WP_TESTS_DB_PASS=
 *   export WP_TESTS_DB_HOST=localhost
 *   vendor/bin/phpunit --testsuite integration
 *
 * @package Intercessor\Tests
 */

$intercessor_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib';

if ( ! file_exists( $intercessor_tests_dir . '/includes/functions.php' ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI bootstrap runs before WordPress escaping helpers are loaded.
	echo "\nERROR: WordPress test library not found at: {$intercessor_tests_dir}\n";
	echo "Set the WP_TESTS_DIR environment variable to the wordpress-develop tests path.\n\n";
	exit( 1 );
}

// Give tests access to WordPress functions.
require_once $intercessor_tests_dir . '/includes/functions.php';

// Load the plugin before the WP test environment initialises.
tests_add_filter( 'muplugins_loaded', static function (): void {
	// Load BerlinDB and plugin autoloader.
	require_once dirname( __DIR__ ) . '/src/includes/berlindb/load.php';
	require_once dirname( __DIR__ ) . '/src/Util/Autoloader.php';

	if ( ! defined( 'INTERCESSOR_DIR' ) ) {
		define( 'INTERCESSOR_DIR', dirname( __DIR__ ) . '/' );
	}
	if ( ! defined( 'INTERCESSOR_VERSION' ) ) {
		define( 'INTERCESSOR_VERSION', '1.0.0' );
	}
	if ( ! defined( 'INTERCESSOR_BASENAME' ) ) {
		define( 'INTERCESSOR_BASENAME', 'intercessor/intercessor.php' );
	}

	$autoloader = new Intercessor\Util\Autoloader();
	$autoloader->register();

	// Install plugin tables into the test database.
	Intercessor\Database\Table_Registry::register();
	Intercessor\Database\Table_Registry::install();
} );

require $intercessor_tests_dir . '/includes/bootstrap.php';
