<?php
/**
 * Unit tests for Intercessor\Database\Row\Prayer_History.
 *
 * @package Intercessor\Tests\Unit\Database\Row
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Database\Row;

use Intercessor\Database\Row\Prayer_History;
use PHPUnit\Framework\TestCase;

class Prayer_HistoryTest extends TestCase {

	private function make( array $props = [] ): Prayer_History {
		$defaults = [
			'id'                => 1,
			'prayer_request_id' => 5,
			'old_status'        => 'pending',
			'new_status'        => 'approved',
			'actor_user_id'     => 1,
			'note'              => '',
			'date_created'      => '2025-01-01 10:00:00',
		];

		return new Prayer_History( (object) array_merge( $defaults, $props ) );
	}

	public function test_id_cast_to_int(): void {
		$ph = $this->make( [ 'id' => '9' ] );
		$this->assertSame( 9, $ph->id );
	}

	public function test_prayer_request_id_cast_to_int(): void {
		$ph = $this->make( [ 'prayer_request_id' => '12' ] );
		$this->assertSame( 12, $ph->prayer_request_id );
	}

	public function test_actor_user_id_cast_to_int(): void {
		$ph = $this->make( [ 'actor_user_id' => '4' ] );
		$this->assertSame( 4, $ph->actor_user_id );
	}

	public function test_old_and_new_status_are_populated(): void {
		$ph = $this->make( [ 'old_status' => 'pending', 'new_status' => 'approved' ] );
		$this->assertSame( 'pending',  $ph->old_status );
		$this->assertSame( 'approved', $ph->new_status );
	}

	public function test_note_is_populated(): void {
		$ph = $this->make( [ 'note' => 'Approved after review.' ] );
		$this->assertSame( 'Approved after review.', $ph->note );
	}

	public function test_note_defaults_to_empty_string(): void {
		$ph = $this->make();
		$this->assertSame( '', $ph->note );
	}

	public function test_represents_transition(): void {
		$ph = $this->make( [ 'old_status' => 'pending', 'new_status' => 'rejected' ] );
		$this->assertNotSame( $ph->old_status, $ph->new_status );
	}
}
