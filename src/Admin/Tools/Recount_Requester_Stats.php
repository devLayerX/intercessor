<?php
/**
 * Recount Single Requester Statistics
 *
 * @package     IPR
 * @subpackage  Admin/Recount_Single_Requester_Stats
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools;

use Intercessor\Admin\Tools\Export\Batch;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Recount_Stats Class
 *
 * @since 0.9.5
 */
class Recount_Requester_Stats extends Batch {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var string
	 * @since 0.9.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-download batch processing to be run.
	 *
	 * @since  0.9.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step.
	 *
	 * @since  0.9.5
	 * @var integer
	 */
	public $per_step = 10;

	/**
	 * Get the Export Data
	 *
	 * @since 0.9.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return bool True if data was found, false if not.
	 */
	public function get_data() {

		$requester = new \Intercessor\Requester( $this->requester_id );
		$prayers   = $this->get_stored_data( 'intercessor_recount_requester_prayers_' . $requester->id, array() );

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $prayers, $offset, $this->per_step );

		if ( count( $step_items ) > 0 ) {
			$pending_total = (float) $this->get_stored_data( 'intercessor_stats_requester_pending_total' . $requester->id, 0 );
			$step_total    = 0;

			$found_prayer_ids = $this->get_stored_data( 'intercessor_stats_found_prayers_' . $requester->id, array() );

			foreach ( $step_items as $prayer_id ) {
				$prayer = \intercessor_get_prayer( $prayer_id );

				if ( is_null( $prayer ) || is_wp_error( $prayer ) ) {

					$missing_prayers   = $this->get_stored_data( 'intercessor_stats_missing_prayers' . $requester->id, array() );
					$missing_prayers[] = $prayer->ID;
					$this->store_data( 'intercessor_stats_missing_prayers' . $requester->id, $missing_prayers );

					continue;
				}

				$should_process_prayer = 'active' === $prayer->status || 'personal' === $prayer->status ? true : false;
				$should_process_prayer = apply_filters( 'intercessor_requester_recount_should_process_prayer', $should_process_prayer, $prayer );

				if ( true === $should_process_prayer ) {

					$found_prayer_ids[] = $prayer->id;
				}
			}

			$this->store_data( 'intercessor_stats_found_prayers_' . $requester->id, $found_prayer_ids );

			return true;
		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 0.9.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$prayers    = $this->get_stored_data( 'intercessor_recount_requester_prayers_' . $this->requester_id, array() );
		$total      = count( $prayers );
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the prayers export
	 *
	 * @since 0.9.5
	 * @param array $request The Form Data passed into the batch processing.
	 */
	public function set_properties( $request ) {
		$this->requester_id = isset( $request['requester_id'] ) ? sanitize_text_field( $request['requester_id'] ) : false;
	}

	/**
	 * Process a step
	 *
	 * @since 0.9.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die(
				esc_html__( 'You do not have permission to modify this data.', 'intercessor' ),
				esc_html__( 'Error', 'intercessor' ),
				array( 'response' => 403 )
			);
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$requester  = new \Intercessor\Requester( $this->requester_id );
			$prayer_ids = $this->get_stored_data( 'intercessor_stats_found_prayers_' . $requester->id, array() );
			$this->delete_data( 'intercessor_stats_found_prayers_' . $requester->id );

			$removed_prayers = array_unique( $this->get_stored_data( 'intercessor_stats_missing_prayers' . $requester->id, array() ) );

			// Find non-existing prayers (deleted) and total up the prayer count.
			$prayer_count = 0;
			foreach ( $prayer_ids as $key => $prayer_id ) {
				if ( in_array( $prayer_id, $removed_prayers, true ) ) {
					unset( $prayer_ids[ $key ] );
					continue;
				}

				$prayer = \intercessor_get_prayer( $prayer_id );
				if ( apply_filters( 'intercessor_requester_recount_sholud_increase_count', true, $prayer ) ) {
					$prayer_count++;
				}
			}

			$this->delete_data( 'intercessor_stats_missing_prayers' . $requester->id );

			$this->delete_data( 'intercessor_stats_requester_pending_total' . $requester->id );
			$this->delete_data( 'intercessor_recount_requester_stats_' . $requester->id );
			$this->delete_data( 'intercessor_recount_requester_prayers_' . $this->requester_id );

			$prayer_ids = implode( ',', $prayer_ids );
			$requester->update(
				array(
					'prayer_ids'   => $prayer_ids,
					'prayer_count' => $prayer_count,
				)
			);

			$this->done    = true;
			$this->message = esc_html__( 'Requester stats successfully recounted.', 'intercessor' );
			return false;
		}
	}

	public function headers() {
		\intercessor_set_time_limit();
	}

	/**
	 * Perform the export
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function export() {

		// Set headers.
		$this->headers();

		\intercessor_die();
	}

	/**
	 * Zero out the data on step one
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function pre_fetch() {
		if ( $this->step === 1 ) {
			$allowed_prayer_status = apply_filters( 'intercessor_recount_requester_prayer_statuses', \intercessor_get_prayer_status_keys() );

			// Before we start, let's zero out the requester's data.
			$requester = new \Intercessor\Requester( $this->requester_id );
			$requester->update(
				array(
					'prayer_count' => 0,
				)
			);

			$prayer_ids = intercessor_get_items(
				'prayer',
				array(
					'requester_id' => $requester->id,
					'status__in'   => $allowed_prayer_status,
					'number'       => 999,
					'fields'       => 'id',
				)
			);

			$this->store_data( 'intercessor_recount_requester_prayers_' . $requester->id, $prayer_ids );
		}
	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  0.9.5
	 * @param string $key     The option_name.
	 * @param bool   $default Default value.
	 *
	 * @return mixed       Returns the data from the database
	 */
	private function get_stored_data( $key, $default = false ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		if ( empty( $value ) ) {
			return $default;
		}

		$maybe_json = json_decode( $value, true );
		if ( ! is_null( $maybe_json ) && ! is_numeric( $value ) ) {
			$value = $maybe_json;
		}

		return $value;
	}

	/**
	 * Give a key, store the value
	 *
	 * @since  0.9.5
	 * @param string $key   The option_name.
	 * @param mixed  $value  The value to store.
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
			'%s',
			'%s',
			'%s',
		);

		$wpdb->replace( $wpdb->options, $data, $formats );
	}

	/**
	 * Delete an option
	 *
	 * @since 0.9.5
	 * @param string $key The option_name to delete.
	 * @return void
	 */
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
