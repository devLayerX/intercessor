<?php
/**
 * Recount all Requester stats
 *
 * This class handles batch processing of recounting all requester stats
 *
 * @package     Intercessor
 * @subpackage  Admin/Tools/Recount_Requester_Stats
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools;

use Intercessor\Admin\Tools\Export\Batch;
use Intercessor\Requester;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Recount_Requeter_Stats Class
 *
 * @since 0.9.5
 */
class Recount_Requester_Stats extends Batch {

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
	public $per_step = 5;

	/**
	 * Get the Export Data
	 *
	 * @since 0.9.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$args = array(
			'limit'   => $this->per_step,
			'offset'  => $this->per_step * ( $this->step - 1 ),
			'orderby' => 'id',
			'order'   => 'DESC',
		);

		$requesters = \intercessor_get_items( 'requester', $args );

		if ( $requesters ) {

			$allowed_prayer_status = apply_filters(
				'intercessor_recount_requester_prayer_statuses',
				array(
					'pending',
					'active',
					'personal',
				)
			);

			foreach ( $requesters as $requester ) {

				$attached_prayer_ids = explode( ',', $requester->prayer_ids );

				$attached_args = array(
					'post__in' => $attached_prayer_ids,
					'number'   => -1,
					'status'   => $allowed_prayer_status,
				);

				$attached_prayers = intercessor_get_items(
					'prayer',
					$attached_args
				);

				$unattached_args = array(
					'post__not_in' => $attached_prayer_ids,
					'number'       => -1,
					'status'       => $allowed_prayer_status,
					'meta_query'   => array(
						array(
							'key'     => '_intercessor_prayer_user_email',
							'value'   => $requester->email,
							'compare' => '=',
						)
					),
				);

				$unattached_prayers = intercessor_get_prayers( $unattached_args );

				$prayers = array_merge( $attached_prayers, $unattached_prayers );

				$prayer_value = 0.00;
				$prayer_count = 0;
				$prayer_ids   = [];

				if ( $prayers ) {

					foreach ( $prayers as $prayer ) {

						$should_process_prayer = 'active' === $prayer->status || 'personal' == $prayer->status ? true : false;
						$should_process_prayer = apply_filters( 'intercessor_requester_recount_should_process_prayer', $should_process_prayer, $prayer );

						if ( true === $should_process_prayer ) {

							if ( apply_filters( 'intercessor_requester_recount_sholud_increase_count', true, $prayer ) ) {
								$prayer_count++;
							}
						}

						$prayer_ids[] = $prayer->ID;
					}
				}

				$prayer_ids = implode( ',', $prayer_ids );

				$requester_update_data = array(
					'prayer_count' => $prayer_count,
					'prayer_value' => $prayer_value,
					'prayer_ids'    => $prayer_ids,
				);

				$requester_instance = new Requester( $requester->id );
				$requester_instance->update( $requester_update_data );

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

		$args = [
			'number'  => -1,
			'orderby' => 'id',
			'order'   => 'DESC',
		];

		$requesters = intercessor_get_items( 'requester', $args );
		$total     = count( $requesters );

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
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) { }

	/**
	 * Process a step
	 *
	 * @since 0.9.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die(
				esc_html__( 'You do not have permission to export data.', 'intercessor' ),
				esc_html__( 'Error', 'intercessor' ),
				[ 'response' => 403 ]
			);
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$this->done    = true;
			$this->message = __( 'Requester stats successfully recounted.', 'intercessor' );
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

}
