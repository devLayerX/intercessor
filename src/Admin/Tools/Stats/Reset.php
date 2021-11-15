<?php
/**
 * Recount store earnings and stats
 *
 * This class handles batch processing of resetting store and download sales and earnings stats
 *
 * @subpackage  Admin/Tools/IPR_Tools_Reset_Stats
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * IPR_Tools_Reset_Stats Class
 *
 * @since 0.9.5
 */
class IPR_Tools_Reset_Stats extends IPR_Batch_Export {

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
		global $wpdb;

		$items = $this->get_stored_data( 'intercessor_temp_reset_ids' );

		if ( ! is_array( $items ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $items, $offset, $this->per_step );

		if ( $step_items ) {

			$step_ids = array(
				'customers' => array(),
				'downloads' => array(),
				'other'     => array(),
			);

			foreach ( $step_items as $item ) {

				switch( $item['type'] ) {
					case 'customer':
						$step_ids['customers'][] = $item['id'];
						break;
					case 'download':
						$step_ids['downloads'][] = $item['id'];
						break;
					default:
						$item_type = apply_filters( 'intercessor_reset_item_type', 'other', $item );
						$step_ids[ $item_type ][] = $item['id'];
						break;
				}

			}

			$sql = array();

			foreach ( $step_ids as $type => $ids ) {

				if ( empty( $ids ) ) {
					continue;
				}

				$ids = implode( ',', $ids );

				switch( $type ) {
					case 'customers':
						$table_name = $wpdb->prefix . 'intercessor_customers';
						$sql[] = "DELETE FROM $table_name WHERE id IN ($ids)";
						break;
					case 'downloads':
						$sql[] = "UPDATE $wpdb->postmeta SET meta_value = 0 WHERE meta_key = '_intercessor_download_sales' AND post_id IN ($ids)";
						$sql[] = "UPDATE $wpdb->postmeta SET meta_value = 0.00 WHERE meta_key = '_intercessor_download_earnings' AND post_id IN ($ids)";
						break;
					case 'other':
						$sql[] = "DELETE FROM $wpdb->posts WHERE id IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->postmeta WHERE post_id IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->comments WHERE comment_post_ID IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)";
						break;
				}

				if ( ! in_array( $type, array( 'customers', 'downloads', 'other' ) ) ) {
					// Allows other types of custom post types to filter on their own post_type
					// and add items to the query list, for the IDs found in their post type.
					$sql = apply_filters( 'intercessor_reset_add_queries_' . $type, $sql, $ids );
				}

			}

			if ( ! empty( $sql ) ) {
				foreach ( $sql as $query ) {
					$wpdb->query( $query );
				}
			}

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

		$items = $this->get_stored_data( 'intercessor_temp_reset_ids', false );
		$total = count( $items );

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
			update_option( 'intercessor_earnings_total', 0 );
			delete_transient( 'intercessor_earnings_total' );
			delete_transient( 'intercessor_estimated_monthly_stats' . true );
			delete_transient( 'intercessor_estimated_monthly_stats' . false );
			$this->delete_data( 'intercessor_temp_reset_ids' );

			// Reset the sequential order numbers
			if ( intercessor_get_option( 'enable_sequential' ) ) {
				delete_option( 'intercessor_last_prayer_number' );
			}

			$this->done    = true;
			$this->message = __( 'Customers, earnings, sales, discounts and logs successfully reset.', 'intercessor' );
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

		if ( $this->step == 1 ) {
			$this->delete_data( 'intercessor_temp_reset_ids' );
		}

		$items = get_option( 'intercessor_temp_reset_ids', false );

		if ( false === $items ) {
			$items = array();

			$intercessor_types_for_reset = array( 'download', 'intercessor_log', 'intercessor_prayer', 'intercessor_discount' );
			$intercessor_types_for_reset = apply_filters( 'intercessor_reset_store_post_types', $intercessor_types_for_reset );

			$args = apply_filters( 'intercessor_tools_reset_stats_total_args', array(
				'post_type'      => $intercessor_types_for_reset,
				'post_status'    => 'any',
				'posts_per_page' => -1,
			) );

			$posts = get_posts( $args );
			foreach ( $posts as $post ) {
				$items[] = array(
					'id'   => (int) $post->ID,
					'type' => $post->post_type,
				);
			}

			$customer_args = array( 'number' => -1 );
			$customers     = intercessor_get_customers( $customer_args );
			foreach ( $customers as $customer ) {
				$items[] = array(
					'id'   => (int) $customer->id,
					'type' => 'customer',
				);
			}

			// Allow filtering of items to remove with an unassociative array for each item
			// The array contains the unique ID of the item, and a 'type' for you to use in the execution of the get_data method
			$items = apply_filters( 'intercessor_reset_store_items', $items );

			$this->store_data( 'intercessor_temp_reset_ids', $items );
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
