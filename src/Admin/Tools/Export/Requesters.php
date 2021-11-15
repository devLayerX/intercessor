<?php
/**
 * Requesters Export Class
 *
 * This class handles requester export
 *
 * @package     Intercessor
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools\Export;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Requesters Export Class
 *
 * @since 0.9.5
 */
class Requesters extends Base {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 0.9.5
	 */
	public $export_type = 'Requesters';

	/**
	 * Set the export headers
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function headers() {
		intercessor_set_time_limit();

		$extra = '';

		if ( ! empty( $_POST['intercessor_export_prayer'] ) ) {
			$extra = sanitize_title( get_the_title( absint( $_POST['intercessor_export_prayer'] ) ) ) . '-';
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . apply_filters( 'intercessor_requesters_export_filename', 'intercessor-export-' . $extra . $this->export_type . '-' . date( 'm-d-Y' ) ) . '.csv"' );
		header( 'Expires: 0' );
	}

	/**
	 * Set the CSV columns
	 *
	 * @since 0.9.5
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		if ( ! empty( $_POST['intercessor_export_prayer'] ) ) {
			$cols = array(
				'first_name' => esc_html__( 'First Name',   'intercessor' ),
				'last_name'  => esc_html__( 'Last Name',   'intercessor' ),
				'email'      => esc_html__( 'Email', 'intercessor' ),
				'date'       => esc_html__( 'Date Created', 'intercessor' )
			);
		} else {

			$cols = array();

			if( 'emails' != $_POST['intercessor_export_option'] ) {
				$cols['name'] = esc_html__( 'Name',   'intercessor' );
			}

			$cols['email'] = esc_html__( 'Email',   'intercessor' );

			if( 'full' == $_POST['intercessor_export_option'] ) {
				$cols['prayers'] = esc_html__( 'All Prayers',   'intercessor' );
			}

		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 0.9.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @global object $intercessor_logs IPR Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();


		$data = apply_filters( 'intercessor_export_get_data', $data );
		$data = apply_filters( 'intercessor_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
