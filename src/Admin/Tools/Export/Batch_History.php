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

// Exit if accessed directly.
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
			'id'           => esc_html__( 'ID', 'intercessor' ),
			'prayer_id'    => esc_html__( 'Prayer ID', 'intercessor' ),
			'prayed_for'   => esc_html__( 'Prayed Counts', 'intercessor' ),
			'date_created' => esc_html__( 'Date Created', 'intercessor' ),
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
		// Set up variables.
		$data = [];

		// Default arguments.
		$args = [
			'number' => 30,
			'offset' => ( $this->step * 30 ) - 30,
			'order'   => 'ASC',
			'orderby' => 'date',
		];

		// Get start and end dates.
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_created'] = [
				[
					'after'     => date( 'Y-m-d 00:00:00', strtotime( $this->start ) ),
					'before'    => date( 'Y-m-d 23:59:59', strtotime( $this->end ) ),
					'inclusive' => true,
				],
			];
		}

		// Get prayed counts from database.
		$prayed_for = intercessor_get_items( 'prayed', $args );

		foreach ( $prayed_for as $prayed ) {
			// Set up data to export.
			$data[] = [
				'id'           => $prayed->id,
				'prayer_id'    => $prayed->prayer_id,
				'prayed_for'   => $prayed->prayed_for,
				'date_created' => $prayed->date_created,
			];
		}

		$data = apply_filters( 'intercessor_export_get_data', $data );
		$data = apply_filters( 'intercessor_export_get_data_' . $this->export_type, $data );

		return ! empty( $data )
			? $data
			: [];
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
		// Set up arguments for database query.
		$args = [
			'fields' => 'ids',
			'count'  => true,
		];

		// Add date query to arguments if specified.
		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = [
				[
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true,
				]
			];
		}

		// Get total prayed counts and percentage.
		$total      = \intercessor_count_items( 'prayed', $args );
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		// Return percentage value.
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

	}
}
