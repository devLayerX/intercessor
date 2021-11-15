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
	public $export_type = 'Requesters';

	/**
	 * Set the CSV columns
	 *
	 * @since 0.9.5
	 *
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'id'      	 => esc_html__( 'ID', 'intercessor' ),
			'name'    	 => esc_html__( 'Name', 'intercessor' ),
			'email'   	 => esc_html__( 'Email', 'intercessor' ),
			'prayers' 	 => esc_html__( 'Number of Prayers', 'intercessor' ),
			'prayer_ids' => esc_html__( 'Prayer ID', 'intercessor' ),
		);

		return $cols;
	}

	/**
	 * Get the export data.
	 *
	 * @since 0.9.5
	 *
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {
		$data = array();

		$requesters = intercessor_get_items(
			'requester',
			[
				'number' => 30,
				'offset' => 30 * ( $this->step - 1 ),
			]
		);

			$i = 0;

			foreach ( $requesters as $requester ) {
				$data[ $i ]['id']         = $requester->id;
				$data[ $i ]['name']       = $requester->name;
				$data[ $i ]['email']      = $requester->email;
				$data[ $i ]['prayers']    = $requester->prayer_count;
				$data[ $i ]['prayer_ids'] = $requester->prayer_ids;

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
		$total = intercessor_count_items( 'requester', false );

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
}
