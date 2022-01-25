<?php
/**
 * Prayers Export Class
 *
 * This class handles prayer export
 *
 * @package     Intercessor
 * @subpackage  Admin/Tools/Export
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Admin\Tools\Export;

use Intercessor\Requester;

// Exit if accessed directly.
 if ( defined( 'ABSPATH' ) ) {
	exit;
 }

/**
 * IPR_Prayers_Export Class
 *
 * @since 1.0.0
 */
class Prayers extends Base {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public $export_type = 'prayers';

	/**
	 * Date
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public $date;

	/**
	 * Status
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public $status;

	/**
	 * Set the CSV columns
	 *
	 * @since 1.0.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		return [
			'id'           => esc_html__( 'ID', 'intercessor' ), // unaltered prayer ID.
			'requester_id' => esc_html__( 'Requester ID', 'intercessor' ), // requester ID.
			'user_id'      => esc_html__( 'User ID', 'intercessor' ),
			'first_name'   => esc_html__( 'First Name', 'intercessor' ),
			'last_name'    => esc_html__( 'Last Name', 'intercessor' ),
			'email'        => esc_html__( 'Email', 'intercessor' ),
			'title'        => esc_html__( 'Title', 'intercessor' ),
			'message'      => esc_html__( 'Message', 'intercessor' ),
			'status'       => esc_html__( 'Status', 'intercessor' ),
			'prayer_key'   => esc_html__( 'Prayer Key', 'intercessor' ),
			'share'        => esc_html__( 'Share Prayer', 'intercessor' ),
			'notify'       => esc_html__( 'Notify', 'intercessor' ),
			'date_created' => esc_html__( 'Date Created', 'intercessor' ),
			'date_active'  => esc_html__( 'Date Active', 'intercessor' ),
			'end_date'     => esc_html__( 'End Date', 'intercessor' ),
		];
	}

	/**
	 * Get the Export Data
	 *
	 * @since 1.0.0
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		// Set up variables.
		$data = [];
		$args = [
			'date'   => ! empty( $this->date ) ? $this->date : '',
			'number' => -1,
			'status' => $this->status,
		];

		// Set up dates range to query.
		if ( ! empty( $this->date ) ) {
			$args['date_created_query'] = [
				'before' => $this->date['start'],
				'after'  => $this->date['end'],
			];
		}

		// Get available prayers from database.
		$prayers = intercessor_get_items( 'prayer', $args );

		// Set up prayer export data if prayer requests found.
		if ( $prayers ) {

			foreach ( $prayers as $prayer ) {
				$requester  = new Requester( $prayer->email );
				$first_name = $requester->get_first_name();
				$last_name  = $requester->get_last_name();

				// Build prayer data.
				$data[] = [
					'id'           => $prayer->id,
					'requester_id' => $prayer->requester_id,
					'user_id'      => $prayer->user_id,
					'first_name'   => $first_name,
					'last_name'    => $last_name,
					'email'        => $prayer->email,
					'title'        => $prayer->title,
					'message'      => $prayer->message,
					'status'       => $prayer->status,
					'prayer_key'   => $prayer->prayer_key,
					'share'        => $prayer->share,
					'notify'       => $prayer->notify,
					'date_created' => $prayer->date_created,
					'start_date'   => $prayer->start_date,
					'end_date'     => $prayer->end_date,
				];
			}
		}

		$data = apply_filters( 'intercessor_export_get_data', $data );
		$data = apply_filters( 'intercessor_export_get_data_' . $this->export_type, $data );

		// Return export data.
		return $data;
	}
}
