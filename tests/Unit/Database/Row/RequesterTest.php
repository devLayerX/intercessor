<?php
/**
 * Unit tests for Intercessor\Database\Row\Requester.
 *
 * @package Intercessor\Tests\Unit\Database\Row
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Database\Row;

use Intercessor\Database\Row\Requester;
use PHPUnit\Framework\TestCase;

class RequesterTest extends TestCase {

	private function make( array $props = [] ): Requester {
		$defaults = [
			'id'            => 1,
			'wp_user_id'    => 0,
			'name'          => 'Jane Doe',
			'email'         => 'jane@example.com',
			'status'        => 'active',
			'date_created'  => '2025-01-01 10:00:00',
			'date_modified' => '2025-01-01 10:00:00',
		];

		return new Requester( (object) array_merge( $defaults, $props ) );
	}

	public function test_id_cast_to_int(): void {
		$req = $this->make( [ 'id' => '99' ] );
		$this->assertSame( 99, $req->id );
	}

	public function test_wp_user_id_cast_to_int(): void {
		$req = $this->make( [ 'wp_user_id' => '5' ] );
		$this->assertSame( 5, $req->wp_user_id );
	}

	public function test_is_linked_to_user_returns_true_when_wp_user_id_positive(): void {
		$req = $this->make( [ 'wp_user_id' => 3 ] );
		$this->assertTrue( $req->is_linked_to_user() );
	}

	public function test_is_linked_to_user_returns_false_for_guest(): void {
		$req = $this->make( [ 'wp_user_id' => 0 ] );
		$this->assertFalse( $req->is_linked_to_user() );
	}

	public function test_get_display_name_returns_name_when_set(): void {
		$req = $this->make( [ 'name' => 'Victor' ] );
		$this->assertSame( 'Victor', $req->get_display_name() );
	}

	public function test_get_display_name_returns_anonymous_when_name_empty(): void {
		$req = $this->make( [ 'name' => '' ] );
		$this->assertSame( 'Anonymous', $req->get_display_name() );
	}

	public function test_email_is_populated(): void {
		$req = $this->make( [ 'email' => 'test@example.com' ] );
		$this->assertSame( 'test@example.com', $req->email );
	}

	public function test_default_wp_user_id_is_zero(): void {
		$req = $this->make();
		$this->assertSame( 0, $req->wp_user_id );
	}
}
