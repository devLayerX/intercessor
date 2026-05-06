<?php
/**
 * Unit tests for Intercessor\Database\Row\Prayer_Note.
 *
 * @package Intercessor\Tests\Unit\Database\Row
 */

declare(strict_types=1);

namespace Intercessor\Tests\Unit\Database\Row;

use Intercessor\Database\Row\Prayer_Note;
use PHPUnit\Framework\TestCase;

class Prayer_NoteTest extends TestCase {

	private function make( array $props = [] ): Prayer_Note {
		$defaults = [
			'id'                => 1,
			'prayer_request_id' => 10,
			'author_user_id'    => 2,
			'content'           => 'Keep praying.',
			'is_private'        => 1,
			'date_created'      => '2025-01-01 10:00:00',
			'date_modified'     => '2025-01-01 10:00:00',
		];

		return new Prayer_Note( (object) array_merge( $defaults, $props ) );
	}

	public function test_id_cast_to_int(): void {
		$note = $this->make( [ 'id' => '5' ] );
		$this->assertSame( 5, $note->id );
	}

	public function test_prayer_request_id_cast_to_int(): void {
		$note = $this->make( [ 'prayer_request_id' => '10' ] );
		$this->assertSame( 10, $note->prayer_request_id );
	}

	public function test_author_user_id_cast_to_int(): void {
		$note = $this->make( [ 'author_user_id' => '3' ] );
		$this->assertSame( 3, $note->author_user_id );
	}

	public function test_is_private_cast_to_int(): void {
		$note = $this->make( [ 'is_private' => '1' ] );
		$this->assertSame( 1, $note->is_private );
	}

	public function test_is_private_returns_true_when_one(): void {
		$note = $this->make( [ 'is_private' => 1 ] );
		$this->assertTrue( $note->is_private() );
	}

	public function test_is_private_returns_false_when_zero(): void {
		$note = $this->make( [ 'is_private' => 0 ] );
		$this->assertFalse( $note->is_private() );
	}

	public function test_default_is_private_is_one(): void {
		$note = $this->make();
		$this->assertSame( 1, $note->is_private );
	}

	public function test_content_is_populated(): void {
		$note = $this->make( [ 'content' => 'Test note content.' ] );
		$this->assertSame( 'Test note content.', $note->content );
	}
}
