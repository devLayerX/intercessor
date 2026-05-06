<?php
/**
 * Unit tests for Intercessor\Database\Row\Prayed_Count.
 *
 * @package Intercessor\Tests\Unit\Database\Row
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Database\Row;

use Intercessor\Database\Row\Prayed_Count;
use PHPUnit\Framework\TestCase;

class Prayed_CountTest extends TestCase {

	private function make( array $props = [] ): Prayed_Count {
		$defaults = [
			'id'                => 1,
			'prayer_request_id' => 5,
			'user_id'           => 0,
			'anonymous_key'     => '',
			'count'             => 1,
			'date_created'      => '2025-01-01 10:00:00',
			'date_modified'     => '2025-01-01 10:00:00',
		];

		return new Prayed_Count( (object) array_merge( $defaults, $props ) );
	}

	public function test_id_cast_to_int(): void {
		$pc = $this->make( [ 'id' => '3' ] );
		$this->assertSame( 3, $pc->id );
	}

	public function test_prayer_request_id_cast_to_int(): void {
		$pc = $this->make( [ 'prayer_request_id' => '10' ] );
		$this->assertSame( 10, $pc->prayer_request_id );
	}

	public function test_user_id_cast_to_int(): void {
		$pc = $this->make( [ 'user_id' => '7' ] );
		$this->assertSame( 7, $pc->user_id );
	}

	public function test_count_cast_to_int(): void {
		$pc = $this->make( [ 'count' => '42' ] );
		$this->assertSame( 42, $pc->count );
	}

	public function test_is_from_user_true_when_user_id_positive(): void {
		$pc = $this->make( [ 'user_id' => 3 ] );
		$this->assertTrue( $pc->is_from_user() );
	}

	public function test_is_from_user_false_when_user_id_zero(): void {
		$pc = $this->make( [ 'user_id' => 0 ] );
		$this->assertFalse( $pc->is_from_user() );
	}

	public function test_is_anonymous_true_when_user_zero_and_key_set(): void {
		$pc = $this->make( [ 'user_id' => 0, 'anonymous_key' => 'abc123' ] );
		$this->assertTrue( $pc->is_anonymous() );
	}

	public function test_is_anonymous_false_when_user_id_set(): void {
		$pc = $this->make( [ 'user_id' => 5, 'anonymous_key' => 'abc123' ] );
		$this->assertFalse( $pc->is_anonymous() );
	}

	public function test_is_anonymous_false_when_both_zero_and_empty(): void {
		$pc = $this->make( [ 'user_id' => 0, 'anonymous_key' => '' ] );
		$this->assertFalse( $pc->is_anonymous() );
	}

	public function test_is_from_user_and_is_anonymous_are_mutually_exclusive(): void {
		$user  = $this->make( [ 'user_id' => 5, 'anonymous_key' => '' ] );
		$guest = $this->make( [ 'user_id' => 0, 'anonymous_key' => 'fp123' ] );

		$this->assertTrue( $user->is_from_user() );
		$this->assertFalse( $user->is_anonymous() );

		$this->assertFalse( $guest->is_from_user() );
		$this->assertTrue( $guest->is_anonymous() );
	}

	public function test_default_count_is_one(): void {
		$pc = $this->make();
		$this->assertSame( 1, $pc->count );
	}
}
