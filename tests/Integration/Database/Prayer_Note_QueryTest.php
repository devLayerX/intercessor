<?php
/**
 * Integration tests for Intercessor\Database\Query\Prayer_Note_Query.
 *
 * @package Intercessor\Tests\Integration\Database
 */

declare(strict_types=1);

namespace Intercessor\Tests\Integration\Database;

use Intercessor\Database\Query\Prayer_Note_Query;
use Intercessor\Database\Query\Prayer_Request_Query;
use Intercessor\Database\Query\Requester_Query;
use Intercessor\Database\Row\Prayer_Note;
use WP_UnitTestCase;

class Prayer_Note_QueryTest extends WP_UnitTestCase {

	/** @var Prayer_Note_Query */
	private Prayer_Note_Query $query;

	/** @var int */
	private int $request_id;

	protected function setUp(): void {
		parent::setUp();

		$this->query = new Prayer_Note_Query();

		$rq           = new Requester_Query();
		$requester_id = (int) $rq->add_item( [ 'name' => 'Note Test User', 'email' => 'notetest@example.com' ] );

		$prq              = new Prayer_Request_Query();
		$this->request_id = (int) $prq->add_item( [
			'requester_id' => $requester_id,
			'subject'      => 'Note test request',
			'content'      => 'Content.',
			'status'       => 'pending',
			'is_public'    => 1,
		] );
	}

	// -------------------------------------------------------------------------
	// add_note()
	// -------------------------------------------------------------------------

	public function test_add_note_returns_positive_id(): void {
		$id = $this->query->add_note( $this->request_id, 'First note.', true );
		$this->assertGreaterThan( 0, $id );
	}

	public function test_add_note_creates_private_note_by_default(): void {
		$id   = $this->query->add_note( $this->request_id, 'Private note.', true );
		$note = $this->query->get_item( $id );

		$this->assertInstanceOf( Prayer_Note::class, $note );
		$this->assertTrue( $note->is_private() );
	}

	public function test_add_note_can_create_shared_note(): void {
		$id   = $this->query->add_note( $this->request_id, 'Shared note.', false );
		$note = $this->query->get_item( $id );

		$this->assertFalse( $note->is_private() );
	}

	public function test_add_note_stores_content(): void {
		$id   = $this->query->add_note( $this->request_id, 'Specific note content.', true );
		$note = $this->query->get_item( $id );

		$this->assertSame( 'Specific note content.', $note->content );
	}

	// -------------------------------------------------------------------------
	// get_for_request()
	// -------------------------------------------------------------------------

	public function test_get_for_request_returns_all_notes(): void {
		$this->query->add_note( $this->request_id, 'Note A.', true );
		$this->query->add_note( $this->request_id, 'Note B.', false );

		$notes = $this->query->get_for_request( $this->request_id );

		$this->assertCount( 2, $notes );
		foreach ( $notes as $note ) {
			$this->assertInstanceOf( Prayer_Note::class, $note );
			$this->assertSame( $this->request_id, $note->prayer_request_id );
		}
	}

	public function test_get_for_request_returns_empty_when_no_notes(): void {
		$notes = $this->query->get_for_request( $this->request_id );
		$this->assertSame( [], $notes );
	}

	// -------------------------------------------------------------------------
	// get_private_for_request()
	// -------------------------------------------------------------------------

	public function test_get_private_for_request_returns_only_private(): void {
		$this->query->add_note( $this->request_id, 'Private.',  true );
		$this->query->add_note( $this->request_id, 'Shared.',   false );

		$private = $this->query->get_private_for_request( $this->request_id );

		$this->assertCount( 1, $private );
		$this->assertTrue( $private[0]->is_private() );
	}

	// -------------------------------------------------------------------------
	// delete_item()
	// -------------------------------------------------------------------------

	public function test_delete_item_removes_note(): void {
		$id = $this->query->add_note( $this->request_id, 'To delete.', true );
		$this->query->delete_item( $id );

		$this->assertFalse( $this->query->get_item( $id ) );
	}

	// -------------------------------------------------------------------------
	// delete_all_for_request()
	// -------------------------------------------------------------------------

	public function test_delete_all_for_request_removes_all_notes(): void {
		$this->query->add_note( $this->request_id, 'Note 1.', true );
		$this->query->add_note( $this->request_id, 'Note 2.', true );

		$this->query->delete_all_for_request( $this->request_id );

		$notes = $this->query->get_for_request( $this->request_id );
		$this->assertSame( [], $notes );
	}
}
