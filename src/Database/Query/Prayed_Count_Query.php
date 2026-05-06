<?php
/**
 * Query class for the prayed_counts table.
 *
 * @package Intercessor
 * @since   1.0.0
 */
declare(strict_types=1);

namespace Intercessor\Database\Query;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


use BerlinDB\Database\Query;
use Intercessor\Database\Row\Prayed_Count;
use Intercessor\Database\Schema\Prayed_Counts_Schema;
use Intercessor\Loader;

/**
 * Provides CRUD and domain-specific query methods for the prayed_counts table.
 *
 * The primary entry point for recording prayer interactions is recordPrayer(),
 * which performs an upsert (insert on first interaction, increment on repeats).
 * Aggregate and per-request retrieval methods support dashboard displays and
 * CSV export.
 *
 * @since   1.0.0
 * @package Intercessor
 *
 * @method Prayed_Count|false get_item( int $id )
 * @method PrayedCount[]     get_items( array $args = [] )
 * @method int|false         add_item( array $data )
 * @method bool              update_item( int $id, array $data )
 * @method bool              delete_item( int $id )
 * @method int               count_items( array $args = [] )
 */
final class Prayed_Count_Query extends Query {

	/**
	 * Shared prefix for all Intercessor table names.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected string $prefix = 'intercessor';

	/**
	 * Table name segment (appended to prefix + $wpdb->prefix).
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected string $table_name = 'prayed_counts';

	/**
	 * Short SQL alias used in query fragments.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected string $table_alias = 'pc';

	/**
	 * Fully-qualified Schema subclass name.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected string $table_schema = Prayed_Counts_Schema::class;

	/**
	 * Singular item label used for cache keys and hook names.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected string $item_name = 'prayed_count';

	/**
	 * Plural item label used for cache keys and hook names.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected string $item_name_plural = 'prayed_counts';

	/**
	 * Fully-qualified Row subclass instantiated for each result.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected string $item_shape = Prayed_Count::class;

	/** @var \wpdb WordPress database object, for direct queries. */
	public $db;

	// -------------------------------------------------------------------------
	// Domain-specific methods
	// -------------------------------------------------------------------------

	/**
	 * Record or increment a "prayed for" interaction for a specific actor.
	 *
	 * Performs an upsert: when a row already exists for the actor+request
	 * pair, its count is incremented by 1. When no row exists, a new row
	 * with count = 1 is inserted. Actor identity is determined by $user_id
	 * for authenticated users or $anonymous_key for guests.
	 *
	 * @since  1.0.0
	 * @param  int    $prayer_id Primary key of the prayer request being prayed for.
	 * @param  int    $user_id          WordPress user ID; pass 0 for guest visitors.
	 * @param  string $anonymous_key    Hashed guest fingerprint; empty for logged-in users.
	 * @return bool                    True on success (insert or update), false on failure.
	 */
	public function record_prayer( int $prayer_id, int $user_id = 0, string $anonymous_key = '' ): bool {
		$existing = $this->find_by_actor( $prayer_id, $user_id, $anonymous_key );

		if ( $existing !== null ) {
			return $this->update_item( $existing->id, array( 'count' => $existing->count + 1 ) );
		}

		$newId = $this->add_item(
			array(
				'prayer_request_id' => $prayer_id,
				'user_id'           => $user_id,
				'anonymous_key'     => $anonymous_key,
				'count'             => 1,
			)
		);

		return $newId !== false;
	}

	/**
	 * Return the aggregate total of all prayers recorded for a single request.
	 *
	 * Executes a SUM(count) query spanning all actors (both authenticated users
	 * and anonymous visitors) and returns 0 when no rows exist.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $prayer_id Primary key of the prayer request.
	 * 
	 * @return int            Summed prayer interaction count.
	 */
	public function get_total_for_request( int $prayer_id ): int {
		$this->db = Loader::instance()->get_db();

		$table = esc_sql( $this->fq_table_name );

		// Use COALESCE to return 0 when no rows exist for the request.
		return (int) $this->db->get_var(
			$this->db->prepare(
				"SELECT COALESCE(SUM(`count`), 0)
				FROM {$table}
				WHERE prayer_request_id = %d",
				$prayer_id
			)
		);
	}

	/**
	 * Return per-request aggregate prayer totals, ordered by total descending.
	 *
	 * Groups all prayed_counts rows by prayer_request_id and sums the count
	 * column. Useful for dashboard leaderboards and CSV export.
	 *
	 * @since  1.0.0
	 * @param  int $limit Maximum rows to return; pass 0 for no limit.
	 * @return array|int, array{prayer_request_id: int, total: int}>
	 *     List of associative arrays, each with:
	 *     - prayer_request_id (int): the request primary key.
	 *     - total (int): summed prayer count across all actors.
	 */
	public function get_aggregated_totals( int $limit = 0 ): array {

		$table = esc_sql( $this->fq_table_name ); // Safe identifier.

		$limit = max( 0, $limit ); // Normalize.

		$sql = "
			SELECT prayer_request_id, SUM(`count`) AS total
			FROM {$table}
			GROUP BY prayer_request_id
			ORDER BY total DESC
		";

		if ( $limit > 0 ) {
			// Append LIMIT safely.
			$sql .= $this->db->prepare( ' LIMIT %d', $limit );
		}

		$rows = $this->db->get_results( $sql, ARRAY_A ); // Fetch as array (avoids object overhead).

		if ( ! $rows ) {
			return array(); // Early return.
		}

		// Normalize types in-place (no extra array allocation).
		foreach ( $rows as &$row ) {
			$row['prayer_request_id'] = (int) $row['prayer_request_id'];
			$row['total']             = (int) $row['total'];
		}
		unset( $row ); // Break reference.

		return $rows;
	}

	/**
	 * Retrieve all prayed_count rows associated with a single prayer request.
	 *
	 * Returns every actor record (user + anonymous) in chronological order.
	 * Pass the result to PrayedCountsExporter for detailed CSV output.
	 *
	 * @since  1.0.0
	 * @param  int           $prayer_id Primary key of the parent prayer request.
	 * @return Prayed_Count[]           All actor rows for the request, oldest first.
	 */
	public function get_for_request( int $prayer_id ): array {
		return $this->get_items(
			array(
				'prayer_request_id' => $prayer_id,
				'orderby'           => 'date_created',
				'order'             => 'ASC',
				'number'            => 0,
			)
		);
	}

	/**
	 * Find an existing record for a specific actor and prayer request.
	 *
	 * Used by recordPrayer() to determine whether to insert a new row or
	 * increment an existing one. When $user_id is greater than zero, the
	 * lookup is performed using the user_id column; otherwise, the
	 * anonymous_key is used.
	 *
	 * If $user_id is 0 and $anonymous_key is empty, no lookup is performed
	 * and null is returned.
	 *
	 * @since  1.0.0
	 *
	 * @param int    $prayer_id     Prayer request ID.
	 * @param int    $user_id       WordPress user ID (0 for guests).
	 * @param string $anonymous_key Hashed guest identifier.
	 *
	 * @return  Existing record, or null if not found.
	 */
	public function find_by_actor( int $prayer_id, int $user_id, string $anonymous_key ): ?Prayed_Count {
		$args = array(
			'prayer_request_id' => $prayer_id,
			'number'            => 1,
		);

		if ( $user_id > 0 ) {
			$args['user_id'] = $user_id;
		} elseif ( $anonymous_key !== '' ) {
			$args['anonymous_key'] = $anonymous_key;
		} else {
			return null; // No valid actor.
		}

		$results = $this->get_items( $args );

		if ( empty( $results ) ) {
			return null;
		}

		return $results[0];
	}

	/**
	 * Delete all prayed_count rows associated with a given prayer request.
	 *
	 * Should be called when hard-deleting a prayer request to maintain
	 * referential integrity, as the plugin does not use foreign key constraints.
	 *
	 * @since  1.0.0
	 * @param  int  $prayer_id Primary key of the prayer request being deleted.
	 * @return bool                  True on success; false on DB error.
	 */
	public function delete_all_for_request( int $prayer_id ): bool {
		$this->db = Loader::instance()->get_db();

		$result = $this->db->delete(
			$this->fq_table_name,
			array( 'prayer_request_id' => $prayer_id ),
			array( '%d' )
		);

		return $result !== false;
	}
}
