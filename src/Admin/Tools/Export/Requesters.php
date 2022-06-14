<?php

/**
 * Requesters Export Class
 *
 * This class handles requester export
 *
 * @package     Intercessor
 * @subpackage  Admin/Tools/Export
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Admin\Tools\Export;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Requesters Export Class
 *
 * @since 1.0.0
 */
class Requesters extends Base
{
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $export_type = 'requesters';

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
	public function csv_cols()
	{
		return [
			'id'            => esc_html__('ID', 'intercessor'),
			'user_id'       => esc_html__('User ID', 'intercessor'),
			'name'          => esc_html__('Name', 'intercessor'),
			'email'         => esc_html__('Email', 'intercessor'),
			'status'        => esc_html__('Status', 'intercessor'),
			'date_created'  => esc_html__('Date created', 'intercessor'),
			'date_modified' => esc_html__('Date modified', 'intercessor'),
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
	public function get_data()
	{
		// Set up variables.
		$data = [];
		$args = [
			'status' => $this->status,
			'date'   => ! empty( $this->date ) ? $this->date : '',
			'number' => -1,
		];

		// Set up dates range to query.
		if ( ! empty( $this->date ) ) {
			$args['date_created_query'] = [
				'before' => $this->date['start'],
				'after'  => $this->date['end'],
			];
		}

		// Get available requesters from database.
		$requesters = \intercessor_get_items('requester', $args);

		// Set up requester export data if requesters found.
		if ( $requesters ) {

			foreach ( $requesters as $requester ) {

				// Build requester data.
				$data[] = [
					'id'            => $requester->id,
					'user_id'       => $requester->user_id,
					'name'          => $requester->name,
					'email'         => $requester->email,
					'status'        => $requester->status,
					'prayer_count'  => $requester->prayer_count,
					'date_created'  => $requester->date_created,
					'date_modified' => $requester->date_modified,
				];
			}
		}

		// Filter data for export.
		$data = apply_filters('intercessor_export_get_data', $data);
		$data = apply_filters('intercessor_export_get_data_' . $this->export_type, $data);

		// Return export data.
		return $data;
	}
}
