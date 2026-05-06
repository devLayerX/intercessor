<?php
/**
 * Prayer request statistics aggregator.
 *
 * @package Intercessor
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Intercessor\Reports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\Loader;
use Intercessor\Database\Queries\Date;

/**
 * Provides query methods for prayer request data.
 *
 * This aggregator wraps the timezone-aware
 * Date class to produce counts, breakdowns, and time-series data for the
 * Reports page. All queries use direct $wpdb prepared statements so the Date
 * class's WHERE clauses are composable without going through BerlinDB's
 * buildWhere() (which does not support date sub-clauses).
 *
 * Usage:
 *   $stats = new Prayer_Request_Stats();
 *
 *   // Count by status + period.
 *   $count = $stats->get_count( 'approved', 'today' );
 *
 *   // Full status breakdown for a period.
 *   $breakdown = $stats->get_status_breakdown( 'month' );
 *
 *   // Daily submission series for a month.
 *   $series = $stats->get_daily_series( 'month' );
 *
 *   // Top requesters by count.
 *   $top = $stats->get_top_requesters( 5, 'year' );
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class Prayer_Request_Stats {

	/** @var array All valid prayer request statuses. */
	public const ALL_STATUSES = array( 'pending', 'approved', 'rejected', 'archived', 'private' );

	/** @var string Fully-qualified table name (without prefix, prefix added in __construct). */
	private string $table;

	/** @var \wpdb WordPress database object, for direct queries. */
	private $db;

	/**
	 * Initialise the stats object with the resolved table name.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		$this->db = Loader::instance()->get_db();
		$this->table = $this->db->prefix . 'intercessor_prayer_requests';
	}

	// ── Core count methods ────────────────────────────────────────────────────

	/**
	 * Count prayer requests by status and optional period.
	 *
	 * @since  1.0.0
	 * @param  string $status  Status value, or '*' / '' for all statuses.
	 * @param  string $period  Named period (see Date::for_period()) or 'all_time'.
	 * @return int             Matching row count.
	 */
	public function get_count( string $status = '*', string $period = 'all_time' ): int {

		$date_where = $this->date_where( $period );

		if ( $status === '*' || $status === '' ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return (int) $this->db->get_var(
				"SELECT COUNT(*) FROM {$this->table} WHERE 1=1 {$date_where}" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE status = %s {$date_where}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$status
			)
		);
	}

	/**
	 * Return counts for every status for a given period.
	 *
	 * @since  1.0.0
	 * @param  string $period  Named period or 'all_time'.
	 * @return array<string, int>  Map of status → count.
	 */
	public function get_status_breakdown( string $period = 'all_time' ): array {

		$date_where = $this->date_where( $period );

		// Group by status to get counts for each.
		$rows = $this->db->get_results(
			"SELECT status, COUNT(*) AS cnt FROM {$this->table} WHERE 1=1 {$date_where} GROUP BY status", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		$result = array_fill_keys( self::ALL_STATUSES, 0 );

		foreach ( (array) $rows as $row ) {
			if ( isset( $result[ $row['status'] ] ) ) {
				$result[ $row['status'] ] = (int) $row['cnt'];
			}
		}

		return $result;
	}

	/**
	 * Return counts of all prayers grouped by calendar day for a period.
	 *
	 * Useful for rendering a time-series chart or table of submissions per day.
	 * Days with zero submissions are included so the series has no gaps.
	 *
	 * @since  1.0.0
	 * @param  string $period  Named period ('today', 'week', 'month', 'year').
	 * @param  string $status  Status filter, or '*' for all.
	 * @return array<int, array{date: string, count: int}> Ordered by date ASC.
	 */
	public function get_daily_series( string $period = 'month', string $status = '*' ): array {
		global $wpdb;

		$cache_key = 'daily_series_' . md5( $period . '|' . $status );
		$cached    = wp_cache_get( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$table      = esc_sql( $this->table );
		$date_where = $this->date_where( $period );

		$sql = "
			SELECT DATE(date_created) AS day, COUNT(*) AS cnt
			FROM {$table}
			WHERE 1=1 {$date_where}
		";

		if ( $status !== '*' && $status !== '' ) {
			$sql .= $this->db->prepare( ' AND status = %s', $status );
		}

		$sql .= " GROUP BY day ORDER BY day ASC";

		$rows = $this->db->get_results( $sql, ARRAY_A ) ?: array();

		[ $after, $before ] = Date::period_boundaries(
			$period === 'all_time' ? 'year' : $period
		);

		$tz  = wp_timezone();
		$cur = new \DateTimeImmutable( $after, $tz );
		$end = new \DateTimeImmutable( $before, $tz );

		$map = array();
		foreach ( $rows as $row ) {
			$map[ $row['day'] ] = (int) $row['cnt'];
		}

		$series = array();

		while ( $cur <= $end ) {
			$day = $cur->format( 'Y-m-d' );

			$series[] = array(
				'date'  => $day,
				'count' => $map[ $day ] ?? 0,
			);

			$cur = $cur->modify( '+1 day' );
		}

		wp_cache_set( $cache_key, $series, '', 300 );

		return $series;
	}

	/**
	 * Return counts grouped by calendar week for a period.
	 *
	 * @since  1.0.0
	 * @param  string $period Named period.
	 * @param  string $status Status filter.
	 * @return array<int, array{week_start: string, count: int}>
	 */
	public function get_weekly_series( string $period = 'year', string $status = '*' ): array {

		$table      = esc_sql( $this->table );
		$date_where = $this->date_where( $period );

		$sow      = (int) get_option( 'start_of_week', 0 );
		$week_sql = ( new Date() )->build_mysql_week( 'date_created', $sow );

		// Strict whitelist sanitization for generated SQL.
		$week_sql = preg_replace( '/[^A-Z0-9_(),\s]/i', '', $week_sql );

		$sql = "
			SELECT {$week_sql} AS wk, YEAR(date_created) AS yr, COUNT(*) AS cnt
			FROM {$table}
			WHERE 1=1 {$date_where}
		";

		if ( $status !== '*' && $status !== '' ) {
			$sql .= $this->db->prepare( ' AND status = %s', $status );
		}

		$sql .= " GROUP BY yr, wk ORDER BY yr ASC, wk ASC";

		$rows = $this->db->get_results( $sql, ARRAY_A );

		$series = array();

		// Transform MySQL week numbers into readable labels.
		foreach ( (array) $rows as $row ) {
			$series[] = array(
				'label' => sprintf(
					/* translators: 1. Year (e.g. 2024), 2. Week number (e.g. 42). */	
					__( 'Week %1$d, %2$d', 'intercessor' ),
					$row['wk'],
					$row['yr']
				),
				'count' => (int) $row['cnt'],
			);
		}

		return $series;
	}

	/**
	 * Return the top N requesters by prayer request count.
	 *
	 * @since  1.0.0
	 * @param  int    $limit  Number of requesters to return.
	 * @param  string $period Named period or 'all_time'.
	 * @param  string $status Status filter or '*'.
	 * @return array<int, array{requester_id: int, name: string, email: string, count: int}>
	 */
	public function get_top_requesters( int $limit = 10, string $period = 'all_time', string $status = '*' ): array {
		
		// Join prayer requests with requesters to get requester details, then group by requester_id.
		$table      = esc_sql( $this->table );
		$req_table  = esc_sql( $this->db->prefix . 'intercessor_requesters' );
		$date_where = $this->date_where( $period );

		$sql = "
			SELECT pr.requester_id, r.first_name, r.last_name, r.name, r.email, COUNT(*) AS cnt
			FROM {$table} pr
			LEFT JOIN {$req_table} r ON r.id = pr.requester_id
			WHERE 1=1 {$date_where}
		";

		if ( $status !== '*' && $status !== '' ) {
			$sql .= $this->db->prepare( ' AND pr.status = %s', $status );
		}

		$sql .= " GROUP BY pr.requester_id ORDER BY cnt DESC";

		if ( $limit > 0 ) {
			$sql .= $this->db->prepare( ' LIMIT %d', $limit );
		}

		$rows = $this->db->get_results( $sql, ARRAY_A );

		// Map raw rows to typed result set with normalized names.
		return array_map( static function ( array $row ) {
			return array(
				'requester_id' => (int) $row['requester_id'],
				'name'         => trim(
					( $row['first_name'] ?? '' ) . ' ' . ( $row['last_name'] ?? '' )
				) ?: ( $row['name'] ?: __( '(unnamed)', 'intercessor' ) ),
				'email'        => $row['email'] ?? '',
				'count'        => (int) $row['cnt'],
			);
		}, (array) $rows );
	}

	/**
	 * Return summary stats for the overview report card row.
	 *
	 * @since  1.0.0
	 * @param  string $period Named period.
	 * @return array{total:int, pending:int, approved:int, rejected:int, archived:int, private:int}
	 */
	public function get_overview( string $period = 'all_time' ): array {
		$breakdown = $this->get_status_breakdown( $period );

		return array(
			'total'    => array_sum( $breakdown ),
			'pending'  => $breakdown['pending'],
			'approved' => $breakdown['approved'],
			'rejected' => $breakdown['rejected'],
			'archived' => $breakdown['archived'],
			'private'  => $breakdown['private'],
		);
	}

	// ── Internal helpers ──────────────────────────────────────────────────────

	/**
	 * Build the Date WHERE clause fragment for a named period.
	 *
	 * Returns an empty string for 'all_time' so queries are unrestricted.
	 * The returned string already includes a leading space and 'AND' keyword
	 * ready for appending to an existing WHERE clause.
	 *
	 * @since  1.0.0
	 * @param  string $period Named period or 'all_time'.
	 * @return string         Prepared SQL fragment e.g. " AND date_created >= %s".
	 */
	private function date_where( string $period ): string {
		if ( $period === 'all_time' ) {
			return '';
		}

		$date = Date::for_period( $period );
		$sql  = $date->get_sql_clauses();

		return $sql['where'] ?? '';
	}
}
