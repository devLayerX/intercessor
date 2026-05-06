<?php
/**
 * Unit tests for Intercessor\Admin\Settings\Repository.
 *
 * @package Intercessor\Tests\Unit\Admin\Settings
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Admin\Settings;

use Intercessor\Admin\Settings\Repository;
use PHPUnit\Framework\TestCase;

/**
 * Tests the settings Repository class.
 *
 * Repository wraps get_option/update_option. The unit test bootstrap provides
 * an in-memory implementation of both via global $__test_options, so no
 * WordPress install is needed.
 */
class RepositoryTest extends TestCase {

	/** @var Repository */
	private Repository $repo;

	protected function setUp(): void {
		// Reset in-memory options store between tests.
		global $__test_options;
		$__test_options = [];

		$this->repo = new Repository( 'intercessor_test_settings' );
	}

	// -------------------------------------------------------------------------
	// all()
	// -------------------------------------------------------------------------

	public function test_all_returns_empty_array_when_no_settings_stored(): void {
		$this->assertSame( [], $this->repo->all() );
	}

	public function test_all_returns_stored_array(): void {
		update_option( 'intercessor_test_settings', [ 'key' => 'value' ] );
		$this->assertSame( [ 'key' => 'value' ], $this->repo->all() );
	}

	// -------------------------------------------------------------------------
	// get()
	// -------------------------------------------------------------------------

	public function test_get_returns_stored_value(): void {
		update_option( 'intercessor_test_settings', [ 'auto_approve' => '1' ] );
		$this->assertSame( '1', $this->repo->get( 'auto_approve' ) );
	}

	public function test_get_returns_default_when_key_absent(): void {
		$this->assertSame( 'default_val', $this->repo->get( 'missing', 'default_val' ) );
	}

	public function test_get_returns_null_default_when_no_default_specified(): void {
		$this->assertNull( $this->repo->get( 'missing' ) );
	}

	// -------------------------------------------------------------------------
	// update()
	// -------------------------------------------------------------------------

	public function test_update_stores_value(): void {
		$this->repo->update( 'profanity_filter', '1' );
		$this->assertSame( '1', $this->repo->get( 'profanity_filter' ) );
	}

	public function test_update_overwrites_existing_value(): void {
		$this->repo->update( 'role', 'editor' );
		$this->repo->update( 'role', 'administrator' );
		$this->assertSame( 'administrator', $this->repo->get( 'role' ) );
	}

	public function test_update_null_removes_key(): void {
		$this->repo->update( 'to_remove', 'value' );
		$this->repo->update( 'to_remove', null );
		$this->assertNull( $this->repo->get( 'to_remove' ) );
	}

	public function test_update_empty_string_removes_key(): void {
		$this->repo->update( 'to_remove', 'value' );
		$this->repo->update( 'to_remove', '' );
		$this->assertNull( $this->repo->get( 'to_remove' ) );
	}

	public function test_update_does_not_affect_other_keys(): void {
		$this->repo->update( 'key_a', 'alpha' );
		$this->repo->update( 'key_b', 'beta' );
		$this->repo->update( 'key_a', 'updated' );

		$this->assertSame( 'beta', $this->repo->get( 'key_b' ) );
	}

	public function test_update_returns_true(): void {
		$result = $this->repo->update( 'any_key', 'any_value' );
		$this->assertTrue( $result );
	}

	// -------------------------------------------------------------------------
	// replace()
	// -------------------------------------------------------------------------

	public function test_replace_overwrites_all_settings(): void {
		$this->repo->update( 'old_key', 'old_value' );
		$this->repo->replace( [ 'new_key' => 'new_value' ] );

		$all = $this->repo->all();
		$this->assertArrayHasKey( 'new_key', $all );
		$this->assertArrayNotHasKey( 'old_key', $all );
	}

	public function test_replace_returns_true(): void {
		$this->assertTrue( $this->repo->replace( [ 'k' => 'v' ] ) );
	}

	// -------------------------------------------------------------------------
	// delete()
	// -------------------------------------------------------------------------

	public function test_delete_removes_key(): void {
		$this->repo->update( 'to_delete', 'value' );
		$this->repo->delete( 'to_delete' );
		$this->assertNull( $this->repo->get( 'to_delete' ) );
	}

	public function test_delete_does_not_affect_other_keys(): void {
		$this->repo->update( 'keep',   'keeper' );
		$this->repo->update( 'remove', 'gone' );
		$this->repo->delete( 'remove' );

		$this->assertSame( 'keeper', $this->repo->get( 'keep' ) );
	}

	public function test_delete_returns_true(): void {
		$this->repo->update( 'k', 'v' );
		$this->assertTrue( $this->repo->delete( 'k' ) );
	}

	// -------------------------------------------------------------------------
	// Custom option key
	// -------------------------------------------------------------------------

	public function test_uses_custom_option_key(): void {
		$repo_a = new Repository( 'plugin_a_settings' );
		$repo_b = new Repository( 'plugin_b_settings' );

		$repo_a->update( 'key', 'value_a' );
		$repo_b->update( 'key', 'value_b' );

		// Each repository is isolated to its own option.
		$this->assertSame( 'value_a', $repo_a->get( 'key' ) );
		$this->assertSame( 'value_b', $repo_b->get( 'key' ) );
	}
}
