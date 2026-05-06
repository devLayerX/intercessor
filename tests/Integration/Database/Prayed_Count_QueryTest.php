<?php
/**
 * Integration tests for Intercessor\Database\Query\Prayed_Count_Query.
 *
 * @package Intercessor\Tests\Integration\Database
 */

declare(strict_types=1);

namespace Intercessor\Tests\Integration\Database;

use Intercessor\Database\Query\Prayed_Count_Query;
use Intercessor\Database\Query\Prayer_Request_Query;
use Intercessor\Database\Query\Requester_Query;
use WP_UnitTestCase;

class Prayed_Count_QueryTest extends WP_UnitTestCase {

	/** @var Prayed_Count_Query */
	private Prayed_Count_Query $query;

	/** @var int */
	private int $request_id;

	protected function setUp(): void {
		parent::setUp();

		$this->query = new Prayed_Count_Query();

		$rq           = new Requester_Query();
		$requester_id = (int) $rq->add_item( [ 'name' => 'Prayed Test User', 'email' => 'prayedtest@example.com' ] );

		$prq              = new Prayer_Request_Query();
		$this->request_id = (int) $prq->add_item( [
			'requester_id' => $requester_id,
			'subject'      => 'Prayed count test request',
			'content'      => 'Content.',
			'status'       => 'approved',
			'is_public'    => 1,
		] );
	}

	// -------------------------------------------------------------------------
	// record_prayer() — first interaction inserts
	// -------------------------------------------------------------------------

	public function test_record_prayer_returns_true_for_new_user(): void {
		$result = $this->query->record_prayer( $this->request_id, 5 );
		$this->assertTrue( $result );
	}

	public function test_record_prayer_returns_true_for_anonymous(): void {
		$result = $this->query->record_prayer( $this->request_id, 0, 'fp_abc123' );
		$this->assertTrue( $result );
	}

	// -------------------------------------------------------------------------
	// get_total_for_request()
	// -------------------------------------------------------------------------

	public function test_get_total_returns_zero_before_any_prayers(): void {
		$this->assertSame( 0, $this->query->get_total_for_request( $this->request_id ) );
	}

	public function test_get_total_returns_one_after_first_prayer(): void {
		$this->query->record_prayer( $this->request_id, 10 );
		$this->assertSame( 1, $this->query->get_total_for_request( $this->request_id ) );
	}

	public function test_get_total_increments_on_repeat_prayer_from_same_user(): void {
		$this->query->record_prayer( $this->request_id, 10 );
		$this->query->record_prayer( $this->request_id, 10 );

		// Same user → count incremented on existing row; total = 2.
		$this->assertSame( 2, $this->query->get_total_for_request( $this->request_id ) );
	}

	public function test_get_total_sums_across_multiple_users(): void {
		$this->query->record_prayer( $this->request_id, 1 );
		$this->query->record_prayer( $this->request_id, 2 );
		$this->query->record_prayer( $this->request_id, 3 );

		$this->assertSame( 3, $this->query->get_total_for_request( $this->request_id ) );
	}

	// -------------------------------------------------------------------------
	// find_by_actor()
	// -------------------------------------------------------------------------

	public function test_find_by_actor_returns_null_before_any_prayer(): void {
		$result = $this->query->find_by_actor( $this->request_id, 99, '' );
		$this->assertNull( $result );
	}

	public function test_find_by_actor_returns_row_after_prayer(): void {
		$this->query->record_prayer( $this->request_id, 7 );
		$row = $this->query->find_by_actor( $this->request_id, 7, '' );

		$this->assertNotNull( $row );
		$this->assertSame( 7, $row->user_id );
	}

	// -------------------------------------------------------------------------
	// delete_all_for_request()
	// -------------------------------------------------------------------------

	public function test_delete_all_for_request_resets_total_to_zero(): void {
		$this->query->record_prayer( $this->request_id, 1 );
		$this->query->record_prayer( $this->request_id, 2 );

		$this->query->delete_all_for_request( $this->request_id );

		$this->assertSame( 0, $this->query->get_total_for_request( $this->request_id ) );
	}
}
