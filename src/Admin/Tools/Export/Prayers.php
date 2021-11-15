<?php
/**
 * Prayers Export Class
 *
 * This class handles prayer export
 *
 * @package     Intercessor
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools\Export;

// Exit if accessed directly
use Intercessor\Requester;

defined( 'ABSPATH' ) || exit;

/**
 * IPR_Prayers_Export Class
 *
 * @since 0.9.5
 */
class Prayers extends Base {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 0.9.5
	 */
	public $export_type = 'prayers';

	/**
	 * Set the export headers
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function headers() {
		intercessor_set_time_limit();

		$month = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' );
		$year  = isset( $_POST['year']  ) ? absint( $_POST['year']  ) : date( 'Y' );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . apply_filters( 'intercessor_prayers_export_filename', 'intercessor-export-' . $this->export_type . '-' . $month . '-' . $year ) . '.csv"' );
		header( 'Expires: 0' );
	}

	/**
	 * Set the CSV columns
	 *
	 * @since 0.9.5
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$columns = array(
			'id'           => esc_html__( 'ID', 'intercessor' ), // unaltered prayer ID
			'requester_id' => esc_html__( 'Requester ID', 'intercessor' ), // requester ID
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
		);

		return $columns;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 0.9.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		$args = array(
			'offset' => 0,
			'number' => -1,
			'status' => isset( $_POST['intercessor_prayers_export_status'] ) ? $_POST['intercessor_prayers_export_status'] : 'any',
			'month'  => isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' ),
			'year'   => isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' )
		);

		$prayers = intercessor()->prayers->get_prayers( $args );

		if ( $prayers ) {

			foreach ( $prayers as $prayer ) {
				$requester  = new Requester( $prayer->email );
				$first_name = $requester->get_first_name();
				$last_name  = $requester->get_last_name();

				$data[] = array(
					'id'           => $prayer->id,
					'requester_id' => $prayer->requester_id,
					'user_id'	   => $prayer->user_id,
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
				);
			}
		}


		$data = apply_filters( 'intercessor_export_get_data', $data );
		$data = apply_filters( 'intercessor_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
