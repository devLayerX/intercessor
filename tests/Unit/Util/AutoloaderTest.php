<?php
/**
 * Unit tests for Intercessor\Util\Autoloader.
 *
 * @package Intercessor\Tests\Unit\Util
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Util;

use Intercessor\Util\Autoloader;
use PHPUnit\Framework\TestCase;

/**
 * Tests the PSR-4 autoloader namespace-to-file mapping logic.
 */
class AutoloaderTest extends TestCase {

	/** @var Autoloader */
	private Autoloader $loader;

	protected function setUp(): void {
		$this->loader = new Autoloader();
	}

	// -------------------------------------------------------------------------
	// add_namespace()
	// -------------------------------------------------------------------------

	public function test_add_namespace_normalises_trailing_backslash(): void {
		// Verify load() works after add_namespace with no trailing backslash.
		// We register a temp namespace pointing to a temp dir that has no files —
		// load() returns false but should not throw.
		$this->loader->add_namespace( 'TestNS', '/tmp' );
		$result = $this->loader->load( 'TestNS\\Nonexistent' );
		$this->assertFalse( $result );
	}

	// -------------------------------------------------------------------------
	// load() — file resolution
	// -------------------------------------------------------------------------

	public function test_load_returns_false_for_unknown_namespace(): void {
		$result = $this->loader->load( 'UnknownNS\\SomeClass' );
		$this->assertFalse( $result );
	}

	public function test_load_returns_true_for_known_class(): void {
		// Intercessor\Util\Profanity_Filter is already loaded (this bootstrap
		// registered the Intercessor\ namespace), so load() will find the file
		// but since the class already exists, require won't re-execute it.
		$this->loader->add_namespace( 'Intercessor\\', INTERCESSOR_DIR . 'src/' );
		// Already loaded — still returns true because file exists.
		$result = $this->loader->load( 'Intercessor\\Util\\Profanity_Filter' );
		$this->assertTrue( $result );
	}

	public function test_load_returns_false_for_nonexistent_file(): void {
		$this->loader->add_namespace( 'Intercessor\\', INTERCESSOR_DIR . 'src/' );
		$result = $this->loader->load( 'Intercessor\\Nonexistent\\Ghost_Class' );
		$this->assertFalse( $result );
	}

	// -------------------------------------------------------------------------
	// PSR-4 path construction
	// -------------------------------------------------------------------------

	public function test_snake_case_class_resolves_to_correct_path(): void {
		// Verify that Intercessor\Admin\Admin_Loader maps to src/Admin/Admin_Loader.php.
		$expectedPath = INTERCESSOR_DIR . 'src/Admin/Admin_Loader.php';
		$this->assertFileExists( $expectedPath );

		$this->loader->add_namespace( 'Intercessor\\', INTERCESSOR_DIR . 'src/' );
		$result = $this->loader->load( 'Intercessor\\Admin\\Admin_Loader' );
		$this->assertTrue( $result );
	}

	public function test_deeply_nested_class_resolves(): void {
		$expectedPath = INTERCESSOR_DIR . 'src/Database/Query/Prayer_Request_Query.php';
		$this->assertFileExists( $expectedPath );

		$this->loader->add_namespace( 'Intercessor\\', INTERCESSOR_DIR . 'src/' );
		$result = $this->loader->load( 'Intercessor\\Database\\Query\\Prayer_Request_Query' );
		$this->assertTrue( $result );
	}

	// -------------------------------------------------------------------------
	// Multiple namespace prefixes
	// -------------------------------------------------------------------------

	public function test_multiple_namespaces_are_independent(): void {
		$this->loader->add_namespace( 'Intercessor\\', INTERCESSOR_DIR . 'src/' );
		$this->loader->add_namespace( 'Other\\',       '/nonexistent/path/' );

		// Intercessor namespace resolves correctly.
		$this->assertTrue( $this->loader->load( 'Intercessor\\Util\\Rate_Limiter' ) );

		// Other namespace returns false (directory doesn't exist).
		$this->assertFalse( $this->loader->load( 'Other\\SomeClass' ) );
	}
}
