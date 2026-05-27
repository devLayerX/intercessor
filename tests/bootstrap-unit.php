<?php
/**
 * Bootstrap for unit tests (no WordPress, no database).
 *
 * Loads WordPress function stubs and the plugin's PSR-4 autoloader so that
 * pure PHP classes can be tested in isolation without a running WordPress site.
 *
 * @package Intercessor\Tests
 */

declare(strict_types=1);

// Load WordPress function stubs first so they are available before any
// plugin class is autoloaded and potentially calls a WP function at require-time.
require_once __DIR__ . '/stubs/wordpress-stubs.php';

// Load BerlinDB classes (Row base class is needed by Row unit tests).
require_once __DIR__ . '/../src/includes/berlindb/load.php';

// Register the plugin's PSR-4 autoloader.
require_once __DIR__ . '/../src/Util/Autoloader.php';

// Define the constants the autoloader and some classes rely on.
if ( ! defined( 'INTERCESSOR_DIR' ) ) {
	define( 'INTERCESSOR_DIR', dirname( __DIR__ ) . '/' );
}
if ( ! defined( 'INTERCESSOR_VERSION' ) ) {
	define( 'INTERCESSOR_VERSION', '1.0.0' );
}
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wp/' );
}

$intercessor_autoloader = new Intercessor\Util\Autoloader();
$intercessor_autoloader->register();
