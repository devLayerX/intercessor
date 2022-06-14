<?php

/**
 * Prayed Counts Export Class
 *
 * This class handles prayed export
 *
 * @package     Intercessor
 * @subpackage  Admin/Tools/Export
 * @copyright   Copyright (c) 2022, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Admin\Tools\Export;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Prayed Counts Export Class
 *
 * @since 1.0.0
 */
class Prayed_Counts extends Base
{
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $export_type = 'prayed';

	/**
	 * Date
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public $date;

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
			'prayer_id'     => esc_html__('Prayer ID', 'intercessor'),
			'prayed_for'    => esc_html__('Prayed For', 'intercessor'),
			'date_created'  => esc_html__('Date created', 'intercessor'),
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

		// Get available prayed counts from database.
		$prayed_for = \intercessor_get_items('prayed', $args);

		// Set up prayed export data if prayed found.
		if ( $prayed_for ) {

			foreach ( $prayed_for as $prayed ) {

				// Build prayed data.
				$data[] = [
					'id'           => $prayed->id,
					'prayer_id'    => $prayed->prayer_id,
					'prayed_for'   => $prayed->prayed_for,
					'date_created' => $prayed->date_created,
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
