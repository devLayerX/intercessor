<?php
/**
 * Requesters Export Class.
 *
 * This class handles the taxed orders export in batches.
 *
 * @package     Intercessor
 * @subpackage  Admin/Reporting/Export
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools\Export;

use function intercessor_count_items;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * IPR_Batch_Orders_Export Class
 *
 * @since 0.9.5
 */
class Batch_Requesters extends Batch {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var string
	 * @since 0.9.5
	 */
	public $export_type = 'requesters';

	/**
	 * Set the CSV columns
	 *
	 * @since 0.9.5
	 *
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		return [
			'id'      	    => esc_html__( 'ID', 'intercessor' ),
			'user_id'       => esc_html__( 'User ID', 'intercessor' ),
			'name'    	    => esc_html__( 'Name', 'intercessor' ),
			'email'   	    => esc_html__( 'Email', 'intercessor' ),
			'status'   	    => esc_html__( 'Status', 'intercessor' ),
			'prayer_count'  => esc_html__( 'Number of Prayers', 'intercessor' ),
			'date_created'  => esc_html__( 'Date created', 'intercessor' ),
			'date_modified' => esc_html__( 'Date modified', 'intercessor' ),
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
		$data = [];

		// Get all requesters.
		$requesters = intercessor_get_items(
			'requester',
			[
				'number' => 30,
				'offset' => 30 * ( $this->step - 1 ),
			]
		);

		$i = 0;

		// Build requester data.
		foreach ( $requesters as $requester ) {
			$requester_id = $requester->id;
			$counts       = intercessor_get_requester_prayers( $requester_id, true );

			// Export data.
			$data[ $i ]['id']            = $requester_id;
			$data[ $i ]['user_id']       = $requester->user_id;
			$data[ $i ]['name']          = $requester->name;
			$data[ $i ]['email']         = $requester->email;
			$data[ $i ]['status']        = $requester->status;
			$data[ $i ]['prayer_count']  = $counts;
			$data[ $i ]['date_created']  = $requester->date_modified;
			$data[ $i ]['date_modified'] = $requester->date_modified;

			$i++;
		}

		$data = apply_filters( 'intercessor_export_get_data', $data );
		$data = apply_filters( 'intercessor_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 0.9.5
	 *
	 * @return int
	 */
	public function get_percentage_complete() {
		$percentage = 0;

		// Total count of Requesters.
		$args = [
			'status' => 'active',
			'fields' => 'ids',
		];
		$total = intercessor_count_items( 'requester', $args );

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the Requesters export.
	 *
	 * @since 0.9.5
	 *
	 * @param array $request The form data passed into the batch processing.
	 */
	public function set_properties( $request ) {
		$this->start = isset( $request['start'] )
			? sanitize_text_field( $request['start'] )
			: '';

		$this->end = isset( $request['end'] )
			? sanitize_text_field( $request['end'] )
			: '';
	}

	/**
	 * Get requester prayer counts.
	 *
	 * @param int $requester_id Requester ID.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @return int Number of requester's prayer requests.
	 */
	private function get_requesters_prayers( $requester_id ) {
		// Set up arguments to retrieve requester prayers.
		$args = [
			'requester_id__in' => $requester_id,
			'fields'           => 'ids',
		];

		// Return number of prayers of requester.
		return intercessor_count_items( 'prayer', $args );
	}
}
