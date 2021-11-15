<?php
/**
 * Recount store prayer requests
 *
 * This class handles batch processing of recounting prayer requests
 *
 * @package     Intercessor
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */
 
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * IPR_Tools_Recount_Prayers Class
 *
 * @since 0.9.5
 */
class IPR_Tools_Recount_Prayers extends IPR_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 0.9.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-download batch processing to be run.
	 * @since  0.9.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  0.9.5
	 * @var integer
	 */
	public $per_step = 100;

	/**
	 * Get the Export Data
	 *
	 * @since 0.9.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		if ( $this->step == 1 ) {
			$this->delete_data( 'intercessor_temp_recount_prayers' );
		}

		$total = get_option( 'intercessor_temp_recount_prayers', false );

		if ( false === $total ) {
			$total = (float) 0;
			$this->store_data( 'intercessor_temp_recount_prayers', $total );
		}

		$accepted_statuses  = apply_filters( 'intercessor_recount_accepted_statuses', array( 'active', 'personal', 'archived' ) );

		$args = apply_filters( 'intercessor_recount_prayers_args', array(
			'number'  => $this->per_step,
			'page'    => $this->step,
			'status'  => $accepted_statuses,
			'fields'  => 'ids',
			'orderby' => 'date',
			'order'   => 'DESC',
		) );

		$prayers = intercessor_get_prayers( $args );

		if ( ! empty( $prayers ) ) {

			$total = count( $prayers );

			$this->store_data( 'intercessor_temp_recount_prayers', $total );

			return true;

		}

		update_option( 'intercessor_prayers_total', $total );
		set_transient( 'intercessor_prayers_total', $total, 86400 );

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 0.9.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$total = $this->get_stored_data( 'intercessor_recount_prayers_total' );

		if ( false === $total ) {
			$args = apply_filters( 'intercessor_recount_prayers_total_args', array() );

			$counts = intercessor_count_prayers( $args );
			$total  = absint( $counts->active ) + absint( $counts->personal ) + absint( $counts->archived );
			$total  = apply_filters( 'intercessor_recount_main_prayers_total', $total );

			$this->store_data( 'intercessor_recount_prayers_total', $total );
		}

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the prayers export
	 *
	 * @since 0.9.5
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {}

	/**
	 * Process a step
	 *
	 * @since 0.9.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'intercessor' ), __( 'Error', 'intercessor' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			delete_transient( 'intercessor_stats_prayers' );
			delete_transient( 'intercessor_stats_prayer_counts' );

			$this->delete_data( 'intercessor_recount_prayers_total' );
			$this->delete_data( 'intercessor_temp_recount_prayers' );
			$this->done    = true;
			$this->message = __( 'Store prayers successfully recounted.', 'intercessor' );
			return false;
		}
	}

	public function headers() {
		intercessor_set_time_limit();
	}

	/**
	 * Perform the export
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		intercessor_die();
	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  0.9.5
	 * @param  string $key The option_name
	 * @return mixed       Returns the data from the database
	 */
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		if ( empty( $value ) ) {
			return false;
		}

		$maybe_json = json_decode( $value );
		if ( ! is_null( $maybe_json ) ) {
			$value = json_decode( $value, true );
		}

		return $value;
	}

	/**
	 * Give a key, store the value
	 *
	 * @since  0.9.5
	 * @param  string $key   The option_name
	 * @param  mixed  $value  The value to store
	 * @return void
	 */
	private function store_data( $key, $value ) {
		global $wpdb;

		$value = is_array( $value ) ? wp_json_encode( $value ) : esc_attr( $value );

		$data = array(
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		);

		$formats = array(
			'%s', '%s', '%s',
		);

		$wpdb->replace( $wpdb->options, $data, $formats );
	}

	/**
	 * Delete an option
	 *
	 * @since  0.9.5
	 * @param  string $key The option_name to delete
	 * @return void
	 */
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
