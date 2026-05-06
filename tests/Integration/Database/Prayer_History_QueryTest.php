<?php
/**
 * Integration tests for Intercessor\Database\Query\Prayer_History_Query.
 *
 * @package Intercessor\Tests\Integration\Database
 */

declare(strict_types=1);

namespace Intercessor\Tests\Integration\Database;

use Intercessor\Database\Query\Prayer_History_Query;
use Intercessor\Database\Query\Prayer_Request_Query;
use Intercessor\Database\Query\Requester_Query;
use Intercessor\Database\Row\Prayer_History;
use WP_UnitTestCase;

class Prayer_History_QueryTest extends WP_UnitTestCase {

	/** @var Prayer_History_Query */
	private Prayer_History_Query $history_query;

	/** @var Prayer_Request_Query */
	private Prayer_Request_Query $prayer_query;

	/** @var int */
	private int $request_id;

	protected function setUp(): void {
		parent::setUp();

		$this->history_query = new Prayer_History_Query();
		$this->prayer_query  = new Prayer_Request_Query();

		$rq           = new Requester_Query();
		$requester_id = (int) $rq->add_item( [ 'name' => 'History Test User', 'email' => 'historytest@example.com' ] );

		$this->request_id = (int) $this->prayer_query->add_item( [
			'requester_id' => $requester_id,
			'subject'      => 'History test request',
			'content'      => 'Content.',
			'status'       => 'pending',
			'is_public'    => 1,
		] );
	}

	// -------------------------------------------------------------------------
	// History is written by update_status()
	// -------------------------------------------------------------------------

	public function test_get_for_request_returns_empty_before_any_status_change(): void {
		$history = $this->history_query->get_for_request( $this->request_id );
		$this->assertSame( [], $history );
	}

	public function test_update_status_writes_history_entry(): void {
		$this->prayer_query->update_status( $this->request_id, 'approved' );

		$history = $this->history_query->get_for_request( $this->request_id );

		$this->assertCount( 1, $history );
		$this->assertInstanceOf( Prayer_History::class, $history[0] );
	}

	public function test_history_records_correct_old_and_new_status(): void {
		$this->prayer_query->update_status( $this->request_id, 'approved' );

		$entry = $this->history_query->get_for_request( $this->request_id )[0];

		$this->assertSame( 'pending',  $entry->old_status );
		$this->assertSame( 'approved', $entry->new_status );
	}

	public function test_multiple_status_changes_produce_multiple_entries(): void {
		$this->prayer_query->update_status( $this->request_id, 'approved' );
		$this->prayer_query->update_status( $this->request_id, 'archived' );

		$history = $this->history_query->get_for_request( $this->request_id );

		$this->assertCount( 2, $history );
	}

	public function test_history_is_ordered_chronologically(): void {
		$this->prayer_query->update_status( $this->request_id, 'approved' );
		$this->prayer_query->update_status( $this->request_id, 'archived' );

		$history = $this->history_query->get_for_request( $this->request_id );

		// First entry should be the earliest transition.
		$this->assertSame( 'approved', $history[0]->new_status );
		$this->assertSame( 'archived', $history[1]->new_status );
	}

	public function test_history_stores_moderator_note(): void {
		$this->prayer_query->update_status( $this->request_id, 'rejected', 'Spam detected.' );

		$entry = $this->history_query->get_for_request( $this->request_id )[0];

		$this->assertSame( 'Spam detected.', $entry->note );
	}

	// -------------------------------------------------------------------------
	// delete_all_for_request()
	// -------------------------------------------------------------------------

	public function test_delete_all_for_request_removes_all_history(): void {
		$this->prayer_query->update_status( $this->request_id, 'approved' );
		$this->prayer_query->update_status( $this->request_id, 'archived' );

		$this->history_query->delete_all_for_request( $this->request_id );

		$this->assertSame( [], $this->history_query->get_for_request( $this->request_id ) );
	}
}
