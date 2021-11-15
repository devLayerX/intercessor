<?php
/**
 * Recount all Intercessor statistics.
 *
 * @package     Intercessor
 * @subpackage  Admin/Tools/IPR_Tools_Recount_All_Stats
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */
 
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * IPR_Tools_Recount_All_Stats Class
 *
 * @since 0.9.5
 */
class IPR_Tools_Recount_All_Stats extends IPR_Batch_Export {

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
	public $per_step = 30;

	/**
	 * Get the Export Data
	 *
	 * @since 0.9.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $intercessor_logs, $wpdb;

		$totals            = $this->get_stored_data( 'intercessor_temp_recount_all_stats'  );
		$prayer_items      = $this->get_stored_data( 'intercessor_temp_prayer_items'      );
		$processed_prayers = $this->get_stored_data( 'intercessor_temp_processed_prayers' );
		$accepted_statuses = apply_filters( 'intercessor_recount_accepted_statuses', array( 'active', 'personal', 'archived' ) );

		if ( false === $totals ) {
			$totals = array();
		}

		if ( false === $prayer_items ) {
			$prayer_items = array();
		}

		if ( false === $processed_prayers ) {
			$processed_prayers = array();
		}

		$all_downloads = $this->get_stored_data( 'intercessor_temp_download_ids' );

		$args = apply_filters( 'intercessor_recount_download_stats_args', array(
			'post_parent__in' => $all_downloads,
			'post_type'       => 'intercessor_log',
			'posts_per_page'  => $this->per_step,
			'post_status'     => 'publish',
			'paged'           => $this->step,
			'log_type'        => 'sale',
			'fields'          => 'ids',
		) );

		$log_ids = $intercessor_logs->get_connected_logs( $args, 'sale' );

		if ( $log_ids ) {
			$log_ids     = implode( ',', $log_ids );
			$prayer_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_intercessor_log_prayer_id' AND post_id IN ($log_ids)" );
			unset( $log_ids );

			$prayer_ids = implode( ',', $prayer_ids );
			$prayers = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $prayer_ids . ")" );
			unset( $prayer_ids );

			foreach ( $prayers as $prayer ) {

				// Prevent prayers that have all ready been retrieved from a previous sales log from counting again.
				if ( in_array( $prayer->ID, $processed_prayers ) ) {
					continue;
				}

				if ( ! in_array( $prayer->post_status, $accepted_statuses ) ) {
					$processed_prayers[] = $prayer->ID;
					continue;
				}

				$items = $prayer_items[ $prayer->ID ];

				foreach ( $items as $item ) {
					$download_id = $item['id'];

					if ( ! in_array( $download_id, $all_downloads ) ) {
						continue;
					}

					if ( ! array_key_exists( $download_id, $totals ) ) {
						$totals[ $download_id ] = array(
							'sales'    => (int) 0,
							'earnings' => (float) 0,
						);
					}

					$amount = $item['price'];
					if ( ! empty( $item['fees'] ) ) {
						foreach ( $item['fees'] as $fee ) {
							// Only let negative fees affect earnings
							if ( $fee['amount'] > 0  ) {
								continue;
							}

							$amount += $fee['amount'];
						}
					}

					$totals[ $download_id ]['sales']++;
					$totals[ $download_id ]['earnings'] += $amount;

				}

				$processed_prayers[] = $prayer->ID;
			}

			$this->store_data( 'intercessor_temp_processed_prayers', $processed_prayers );
			$this->store_data( 'intercessor_temp_recount_all_stats', $totals );

			return true;
		}

		foreach ( $totals as $key => $stats ) {
			update_post_meta( $key, '_intercessor_download_sales'   , $stats['sales'] );
			update_post_meta( $key, '_intercessor_download_earnings', $stats['earnings'] );
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

		$total = $this->get_stored_data( 'intercessor_recount_all_total', false );

		if ( false === $total ) {
			$this->pre_fetch();
			$total = $this->get_stored_data( 'intercessor_recount_all_total', 0 );
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
	public function set_properties( $request ) {
		$this->download_id = isset( $request['download_id'] ) ? sanitize_text_field( $request['download_id'] ) : false;
	}

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
			$this->delete_data( 'intercessor_recount_all_total' );
			$this->delete_data( 'intercessor_temp_recount_all_stats' );
			$this->delete_data( 'intercessor_temp_prayer_items' );
			$this->delete_data( 'intercessor_temp_download_ids' );
			$this->delete_data( 'intercessor_temp_processed_prayers' );
			$this->done    = true;
			$this->message = __( 'Earnings and sales stats successfully recounted.', 'intercessor' );
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

	public function pre_fetch() {
		global $intercessor_logs, $wpdb;

		if ( $this->step == 1 ) {
			$this->delete_data( 'intercessor_temp_recount_all_total' );
			$this->delete_data( 'intercessor_temp_recount_all_stats' );
			$this->delete_data( 'intercessor_temp_prayer_items' );
			$this->delete_data( 'intercessor_temp_processed_prayers' );
		}

		$accepted_statuses = apply_filters( 'intercessor_recount_accepted_statuses', array( 'publish', 'revoked' ) );
		$total             = $this->get_stored_data( 'intercessor_temp_recount_all_total' );

		if ( false === $total ) {
			$total         = 0;
			$prayer_items = $this->get_stored_data( 'intercessor_temp_prayer_items' );

			if ( false === $prayer_items ) {
				$prayer_items = array();
				$this->store_data( 'intercessor_temp_prayer_items', $prayer_items );
			}

			$all_downloads = $this->get_stored_data( 'intercessor_temp_download_ids' );

			if ( false === $all_downloads ) {
				$args = array(
					'post_status'    => 'any',
					'post_type'      => 'download',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				);

				$all_downloads = get_posts( $args );
				$this->store_data( 'intercessor_temp_download_ids', $all_downloads );

				if ( $this->step == 1 ) {
					foreach ( $all_downloads as $download ) {
						update_post_meta( $download, '_intercessor_download_sales'   , 0 );
						update_post_meta( $download, '_intercessor_download_earnings', 0 );
					}
				}
			}

			$args  = apply_filters( 'intercessor_recount_download_stats_total_args', array(
				'post_parent__in' => $all_downloads,
				'post_type'       => 'intercessor_log',
				'post_status'     => 'publish',
				'log_type'        => 'sale',
				'fields'          => 'ids',
				'nopaging'        => true,
			) );

			$all_logs = $intercessor_logs->get_connected_logs( $args, 'sale' );

			if ( $all_logs ) {
				$log_ids     = implode( ',', $all_logs );
				$prayer_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_intercessor_log_prayer_id' AND post_id IN ($log_ids)" );
				unset( $log_ids );

				$prayer_ids = implode( ',', $prayer_ids );
				$prayers = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $prayer_ids . ")" );
				unset( $prayer_ids );

				foreach ( $prayers as $prayer ) {
					if ( ! in_array( $prayer->post_status, $accepted_statuses ) ) {
						continue;
					}

					if ( ! array_key_exists( $prayer->ID, $prayer_items ) ) {

						$items = intercessor_get_prayer_meta_cart_details( $prayer->ID );
						$prayer_items[ $prayer->ID ] = $items;

					}

				}

				$total = count( $all_logs );
			}

			$this->store_data( 'intercessor_temp_prayer_items', $prayer_items );
			$this->store_data( 'intercessor_recount_all_total' , $total );
		}

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
