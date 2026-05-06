<?php
/**
 * Integration tests for Intercessor\Database\Query\Requester_Query.
 *
 * @package Intercessor\Tests\Integration\Database
 */

declare(strict_types=1);

namespace Intercessor\Tests\Integration\Database;

use Intercessor\Database\Query\Requester_Query;
use Intercessor\Database\Row\Requester;
use WP_UnitTestCase;

class Requester_QueryTest extends WP_UnitTestCase {

	/** @var Requester_Query */
	private Requester_Query $query;

	protected function setUp(): void {
		parent::setUp();
		$this->query = new Requester_Query();
	}

	// -------------------------------------------------------------------------
	// add_item / get_item
	// -------------------------------------------------------------------------

	public function test_add_and_get_item_roundtrip(): void {
		$id   = (int) $this->query->add_item( [
			'name'  => 'Alice Smith',
			'email' => 'alice@example.com',
		] );
		$item = $this->query->get_item( $id );

		$this->assertInstanceOf( Requester::class, $item );
		$this->assertSame( 'Alice Smith',         $item->name );
		$this->assertSame( 'alice@example.com',   $item->email );
	}

	// -------------------------------------------------------------------------
	// find_or_create()
	// -------------------------------------------------------------------------

	public function test_find_or_create_creates_new_requester(): void {
		$id = $this->query->find_or_create( 'new@example.com', 'New User' );
		$this->assertGreaterThan( 0, $id );

		$item = $this->query->get_item( $id );
		$this->assertSame( 'new@example.com', $item->email );
	}

	public function test_find_or_create_returns_existing_id_for_known_email(): void {
		$first  = $this->query->find_or_create( 'same@example.com', 'First Name' );
		$second = $this->query->find_or_create( 'same@example.com', 'Different Name' );

		$this->assertSame( $first, $second );
	}

	public function test_find_or_create_does_not_create_duplicate(): void {
		$this->query->find_or_create( 'unique@example.com', 'User A' );
		$this->query->find_or_create( 'unique@example.com', 'User B' );

		$items = $this->query->get_items( [ 'email' => 'unique@example.com' ] );
		$this->assertCount( 1, $items );
	}

	// -------------------------------------------------------------------------
	// get_items()
	// -------------------------------------------------------------------------

	public function test_get_items_filters_by_email(): void {
		$this->query->add_item( [ 'name' => 'Bob',   'email' => 'bob@example.com' ] );
		$this->query->add_item( [ 'name' => 'Carol', 'email' => 'carol@example.com' ] );

		$items = $this->query->get_items( [ 'email' => 'bob@example.com' ] );
		$this->assertCount( 1, $items );
		$this->assertSame( 'bob@example.com', $items[0]->email );
	}

	// -------------------------------------------------------------------------
	// delete_item()
	// -------------------------------------------------------------------------

	public function test_delete_item_removes_requester(): void {
		$id = (int) $this->query->add_item( [
			'name'  => 'Delete Me',
			'email' => 'deleteme@example.com',
		] );
		$this->query->delete_item( $id );

		$this->assertFalse( $this->query->get_item( $id ) );
	}

	// -------------------------------------------------------------------------
	// count_items()
	// -------------------------------------------------------------------------

	public function test_count_items_increases_after_add(): void {
		$before = $this->query->count_items( [] );
		$this->query->add_item( [ 'name' => 'Counter', 'email' => 'counter@example.com' ] );
		$after = $this->query->count_items( [] );

		$this->assertSame( $before + 1, $after );
	}
}
