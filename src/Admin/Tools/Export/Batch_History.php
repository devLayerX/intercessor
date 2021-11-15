<?php
/**
 * Prayer Requests and Counts Export Class.
 *
 * This class handles prayers and counts export on a day-by-day basis.
 *
 * @package    IPR
 * @subpackage Admin/Tools/Export
 * @copyright  Copyright (c) 2019, Victor Aigbeghian
 * @license    http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since      0.9.5
 */

namespace Intercessor\Admin\Tools\Export;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * IPR_Batch_Prayer_Requests_Export Class
 *
 * @since 0.9.5
 */
class Batch_History extends Batch {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 0.9.5
	 * @var string
	 */
	public $export_type = 'prayers_and_counts';

	/**
	 * Prayer ID.
	 *
	 * @since 0.9.5
	 * @access protected
	 * @var int
	 */
	protected $prayer_id = 0;

	/**
	 * Requester ID.
	 *
	 * @since 0.9.5
	 * @access protected
	 * @var int
	 */
	protected $requester_id = 0;

	/**
	 * Set the CSV columns.
	 *
	 * @since 0.9.5
	 *
	 * @return array $cols CSV columns.
	 */
	public function csv_cols() {
		return [
			'date'    => esc_html__( 'Date', 'intercessor' ),
			'prayers' => esc_html__( 'Prayers', 'intercessor' ),
			'counts'  => esc_html__( 'Counts', 'intercessor' ),
		];
	}

	/**
	 * Get the export data.
	 *
	 * @since 0.9.5
	 *
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {
		$db            = intercessor()->prayers->get_db();
		$prayers_table = intercessor()->prayers->table_name;
		$counts_table  = intercessor()->prayer_meta->table_name;
		$data          = array();

		// Default arguments.
		$args = [
			'number' => 30,
			'offset' => ( $this->step * 30 ) - 30,
		];

		$status         = "AND {$prayers_table}.status IN ( '" . implode( "', '", $db->_escape( array( 'active', 'pending', 'personal' ) ) ) . "' )";
		$date_query_sql = '';

		// Requester ID.
		$requester_id = ! empty( $this->requester_id )
			? $db->prepare( "AND {$prayers_table}.requester_id = %d", $this->requester_id )
			: '';

		// Prayer ID.
		$prayer_id = ! empty( $this->prayer_id )
			? $db->prepare( "AND {$counts_table}.prayer_id = %d", $this->prayer_id )
			: '';

		// Generate date query SQL if dates have been set.
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {

			// Fetch GMT offset.
			$offset = get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;

			$date_query_sql = 'AND ';

			if ( ! empty( $this->start ) ) {
				$this->start = date( 'Y-m-d 00:00:00', strtotime( $this->start ) );

				$this->start = 0 < $offset
					? intercessor()->utils->date( $this->start )->subSeconds( $offset )->format( 'mysql' )
					: intercessor()->utils->date( $this->start )->addSeconds( $offset )->format( 'mysql' );

				$date_query_sql .= $db->prepare( 'date_created >= %s', $this->start );
			}

			// Join dates with `AND` if start and end date set.
			if ( ! empty( $this->start ) && ! empty( $this->end ) ) {
				$this->end = date( 'Y-m-d 23:59:59', strtotime( $this->end ) );

				$this->end = 0 < $offset
					? intercessor()->utils->date( $this->end )->addSeconds( $offset )->format( 'mysql' )
					: intercessor()->utils->date( $this->end )->subSeconds( $offset )->format( 'mysql' );

				$date_query_sql .= ' AND ';
			}

			if ( ! empty( $this->end ) ) {
				$date_query_sql .= $db->prepare( 'date_created <= %s', $this->end );
			}
		}

		// Look in prayers table if a prayer ID was not passed.
		if ( 0 === $this->prayer_id ) {
			$sql = "
				SELECT COUNT(id) AS prayers, SUM(total) AS counts, date_created
				FROM {$prayers_table}
				WHERE 1=1 {$status} {$requester_id} {$date_query_sql}
				GROUP BY YEAR(date_created), MONTH(date_created), DAY(date_created)
	            ORDER BY YEAR(date_created), MONTH(date_created), DAY(date_created) ASC
	            LIMIT {$args['offset']}, {$args['number']}
			";

		// Join prayers and prayer counts table.
		} else {
			$sql = "
				SELECT SUM({$counts_table}.quantity) AS prayers, SUM({$counts_table}.total) AS counts, {$prayers_table}.date_created
				FROM {$prayers_table}
				INNER JOIN {$counts_table} ON {$prayers_table}.id = {$counts_table}.order_id
				WHERE 1=1 {$status} {$prayer_id} {$date_query_sql}
				GROUP BY YEAR({$prayers_table}.date_created), MONTH({$prayers_table}.date_created), DAY({$prayers_table}.date_created)
	            ORDER BY YEAR({$prayers_table}.date_created), MONTH({$prayers_table}.date_created), DAY({$prayers_table}.date_created) ASC
	            LIMIT {$args['offset']}, {$args['number']}
			";
		}

		$results = $db->get_results( $sql );

		foreach ( $results as $result ) {

			// Localize the returned time.
			$d = intercessor()->utils->date( $result->date_created, null, true )->format( 'date' );

			$prayers = isset( $result->prayers )
				? absint( $result->prayers )
				: 0;

			$counts = isset( $result->counts )
				? intercessor_format_count( $result->counts )
				: floatval( 0 );

			$data[] = array(
				'date'     => $d,
				'prayers'    => $prayers,
				'counts' => $counts,
			);
		}

		$data = apply_filters( 'intercessor_export_get_data', $data );
		$data = apply_filters( 'intercessor_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 0.9.5
	 * @since 0.9.5 Updated to use new query methods.
	 *
	 * @return int
	 */
	public function get_percentage_complete() {
		$args = array(
			'fields' => 'ids',
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);
		}

		$total = intercessor_count_prayers( $args );
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the prayers and counts export.
	 *
	 * @since 0.9.5
	 *
	 * @param array $request Form data passed to the batch processor.
	 */
	public function set_properties( $request ) {
		$this->start = isset( $request['start'] )
			? sanitize_text_field( $request['start'] )
			: '';

		$this->end = isset( $request['end'] )
			? sanitize_text_field( $request['end'] )
			: '';

		$this->prayer_id = isset( $request['prayer_id'] )
			? absint( $request['prayer_id'] )
			: 0;

		$this->requester_id = isset( $request['requester_id'] )
			? absint( $request['requester_id'] )
			: 0;
	}
}
