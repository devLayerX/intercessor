<?php
/**
 * Unit tests for Intercessor\Database\Row\Prayer_Request.
 *
 * @package Intercessor\Tests\Unit\Database\Row
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Database\Row;

use Intercessor\Database\Row\Prayer_Request;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Prayer_Request row value object.
 *
 * Prayer_Request is a simple data object — it can be constructed directly
 * from a stdClass without any database or WordPress involvement.
 */
class Prayer_RequestTest extends TestCase {

	/**
	 * Build a Prayer_Request from an array of property overrides.
	 */
	private function make( array $props = [] ): Prayer_Request {
		$defaults = [
			'id'             => 1,
			'requester_id'   => 1,
			'subject'        => 'Please pray for me',
			'content'        => 'I need strength.',
			'status'         => 'pending',
			'is_anonymous'   => 0,
			'is_public'      => 1,
			'moderator_note' => '',
			'date_created'   => '2025-01-01 10:00:00',
			'date_modified'  => '2025-01-01 10:00:00',
		];

		return new Prayer_Request( (object) array_merge( $defaults, $props ) );
	}

	// -------------------------------------------------------------------------
	// Type casting
	// -------------------------------------------------------------------------

	public function test_id_is_cast_to_int(): void {
		$req = $this->make( [ 'id' => '42' ] );
		$this->assertSame( 42, $req->id );
	}

	public function test_requester_id_is_cast_to_int(): void {
		$req = $this->make( [ 'requester_id' => '7' ] );
		$this->assertSame( 7, $req->requester_id );
	}

	public function test_is_anonymous_is_cast_to_int(): void {
		$req = $this->make( [ 'is_anonymous' => '1' ] );
		$this->assertSame( 1, $req->is_anonymous );
	}

	public function test_is_public_is_cast_to_int(): void {
		$req = $this->make( [ 'is_public' => '0' ] );
		$this->assertSame( 0, $req->is_public );
	}

	// -------------------------------------------------------------------------
	// Status helpers
	// -------------------------------------------------------------------------

	public function test_is_pending_returns_true_when_pending(): void {
		$req = $this->make( [ 'status' => 'pending' ] );
		$this->assertTrue( $req->is_pending() );
	}

	public function test_is_pending_returns_false_when_not_pending(): void {
		$req = $this->make( [ 'status' => 'approved' ] );
		$this->assertFalse( $req->is_pending() );
	}

	public function test_is_approved_returns_true_when_approved(): void {
		$req = $this->make( [ 'status' => 'approved' ] );
		$this->assertTrue( $req->is_approved() );
	}

	public function test_is_approved_returns_false_when_rejected(): void {
		$req = $this->make( [ 'status' => 'rejected' ] );
		$this->assertFalse( $req->is_approved() );
	}

	public function test_is_private_status_returns_true_when_private(): void {
		$req = $this->make( [ 'status' => 'private' ] );
		$this->assertTrue( $req->is_private_status() );
	}

	public function test_is_private_status_returns_false_when_approved(): void {
		$req = $this->make( [ 'status' => 'approved' ] );
		$this->assertFalse( $req->is_private_status() );
	}

	/**
	 * @dataProvider all_statuses_provider
	 */
	public function test_only_one_status_method_true_at_a_time( string $status, string $trueMethod ): void {
		$req     = $this->make( [ 'status' => $status ] );
		$methods = [ 'is_pending', 'is_approved', 'is_private_status' ];

		foreach ( $methods as $method ) {
			if ( $method === $trueMethod ) {
				$this->assertTrue( $req->$method(), "{$method} should be true for status '{$status}'" );
			} else {
				$this->assertFalse( $req->$method(), "{$method} should be false for status '{$status}'" );
			}
		}
	}

	public static function all_statuses_provider(): array {
		return [
			'pending'  => [ 'pending',  'is_pending' ],
			'approved' => [ 'approved', 'is_approved' ],
			'private'  => [ 'private',  'is_private_status' ],
		];
	}

	// -------------------------------------------------------------------------
	// Visibility helpers
	// -------------------------------------------------------------------------

	public function test_is_anonymous_returns_true_when_flag_is_one(): void {
		$req = $this->make( [ 'is_anonymous' => 1 ] );
		$this->assertTrue( $req->is_anonymous() );
	}

	public function test_is_anonymous_returns_false_when_flag_is_zero(): void {
		$req = $this->make( [ 'is_anonymous' => 0 ] );
		$this->assertFalse( $req->is_anonymous() );
	}

	public function test_is_public_returns_true_when_flag_is_one(): void {
		$req = $this->make( [ 'is_public' => 1 ] );
		$this->assertTrue( $req->is_public() );
	}

	public function test_is_public_returns_false_when_flag_is_zero(): void {
		$req = $this->make( [ 'is_public' => 0 ] );
		$this->assertFalse( $req->is_public() );
	}

	// -------------------------------------------------------------------------
	// Default values
	// -------------------------------------------------------------------------

	public function test_default_status_is_pending(): void {
		$req = $this->make();
		$this->assertSame( 'pending', $req->status );
	}

	public function test_default_is_public_is_one(): void {
		$req = $this->make();
		$this->assertSame( 1, $req->is_public );
	}

	public function test_default_is_anonymous_is_zero(): void {
		$req = $this->make();
		$this->assertSame( 0, $req->is_anonymous );
	}
}
