<?php
/**
 * Prayers Export Class
 *
 * This class handles prayer export in batches
 *
 * @package     Intercessor
 * @subpackage  Admin/Reporting/Export
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools\Export;

use Intercessor\Requester;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Batch_Prayers export Class
 *
 * @since 0.9.5
 */
class Batch_Prayers extends Batch {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var string
	 * @since 0.9.5
	 */
	public $export_type = 'prayers';

	/**
	 * Set the CSV columns
	 *
	 * @since 0.9.5
	 *
	 * @return array $columns All the columns
	 */
	public function csv_cols() {
		return [
			'id'           => esc_html__( 'ID', 'intercessor' ),
			'requester_id' => esc_html__( 'Requester ID', 'intercessor' ),
			'first_name'   => esc_html__( 'First Name', 'intercessor' ),
			'last_name'    => esc_html__( 'Last Name', 'intercessor' ),
			'email'        => esc_html__( 'Email', 'intercessor' ),
			'title'        => esc_html__( 'Title', 'intercessor' ),
			'message'      => esc_html__( 'Message', 'intercessor' ),
			'status'       => esc_html__( 'Status', 'intercessor' ),
			'prayer_key'   => esc_html__( 'Prayer Key', 'intercessor' ),
			'share'        => esc_html__( 'Prayer Share', 'intercessor' ),
			'notify'       => esc_html__( 'Notify', 'intercessor' ),
			'date_created' => esc_html__( 'Date Created', 'intercessor' ),
			'date_active'  => esc_html__( 'Date Active', 'intercessor' ),
			'end_date'     => esc_html__( 'End Date', 'intercessor' ),
		];
	}

	/**
	 * Get the export data.
	 *
	 * @since 0.9.5
	 * @since 0.9.5 Updated to use new query methods.
	 *
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {
		$data = [];

		$args = [
			'number'  => 30,
			'offset'  => ( $this->step * 30 ) - 30,
			'status'  => $this->status,
			'order'   => 'ASC',
			'orderby' => 'date',
		];

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_created'] = array(
				array(
					'after'     => date( 'Y-m-d 00:00:00', strtotime( $this->start ) ),
					'before'    => date( 'Y-m-d 23:59:59', strtotime( $this->end ) ),
					'inclusive' => true,
				),
			);
		}

		if ( 'any' === $args['status'] ) {
			unset( $args['status'] );
		}

		$prayers = intercessor_get_items( 'prayer', $args );

		foreach ( $prayers as $prayer ) {
			$requester  = new Requester( $prayer->email );
			$first_name = $requester->get_first_name();
			$last_name  = $requester->get_last_name();

			$data[] = [
				'id'           => $prayer->id,
				'requester_id' => $prayer->requester_id,
				'first_name'   => $first_name,
				'last_name'    => $last_name,
				'email'        => $prayer->email,
				'title'        => stripslashes( $prayer->title ),
				'message'      => stripslashes( $prayer->message ),
				'status'       => $prayer->status,
				'prayer_key'   => $prayer->prayer_key,
				'share'        => $prayer->share,
				'notify'       => $prayer->notify,
				'date_created' => $prayer->date_created,
				'start_date'   => $prayer->date_active,
				'end_date'     => $prayer->end_date,
			];
		}

		$data = apply_filters( 'intercessor_export_get_data', $data );
		$data = apply_filters( 'intercessor_export_get_data_' . $this->export_type, $data );

		return ! empty( $data )
			? $data
			: [];
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 0.9.5
	 * @since 0.9.5 Updated to use new query methods.
	 *
	 * @return int
	 */
	public function get_percentage_complete() {
		$args = [
			'fields' => 'ids',
		];

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_created'] = array(
				[
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true,
				],
			);
		}

		if ( 'any' !== $this->status ) {
			$args['status'] = $this->status;
		}

		$total      = intercessor_count_items( 'prayer', $args );
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the prayers export.
	 *
	 * @param array $request The Form Data passed into the batch processing.
	 *
	 * @since 0.9.5
	 */
	public function set_properties( $request ) {
		$this->start  = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end    = isset( $request['end'] ) ? sanitize_text_field( $request['end'] ) : '';
		$this->status = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'active';
	}
}
