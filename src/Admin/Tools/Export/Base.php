<?php
/**
 * Base export class.
 *
 * This is the base class for all export methods. Each data export type (Requesters, prayers, etc) extend this class.
 *
 * @package     Intercessor
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools\Export;

use function intercessor_die;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base Class
 *
 * @since 0.9.5
 */
class Base {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 0.9.5
	 */
	public $export_type = 'default';

	/**
	 * Can we export?
	 *
	 * @since 0.9.5
	 *
	 * @return bool True if exporting is allowed, false otherwise.
	 */
	public function can_export() {
		return (bool) apply_filters( 'intercessor_export_capability', current_user_can( 'export_prayer_reports' ) );
	}

	/**
	 * Set the export headers.
	 *
	 * @since 0.9.5
	 * @since 0.9.5 Add BOM to the CSV export.
	 */
	public function headers() {
		\intercessor_set_time_limit();

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="intercessor-export-' . $this->export_type . '-' . date( 'm-d-Y' ) . '.csv"' );
		header( 'Expires: 0' );

		/**
		 * We need to append a BOM to the export so that Microsoft Excel knows
		 * that the file is in Unicode.
		 */
		echo "\xEF\xBB\xBF";
	}

	/**
	 * Set the CSV columns.
	 *
	 * @since 0.9.5
	 *
	 * @return array $cols CSV columns.
	 */
	public function csv_cols() {
		return [
			'id'   => esc_html__( 'ID',   'intercessor' ),
			'date' => esc_html__( 'Date', 'intercessor' ),
		];
	}

	/**
	 * Retrieve the CSV columns.
	 *
	 * @since 0.9.5
	 *
	 * @return array $cols Array of the columns.
	 */
	public function get_csv_cols() {
		$cols = $this->csv_cols();

		/**
		 * Filters the available CSV export columns for this export.
		 *
		 * This dynamic filter is appended with the export type string, for example:
		 *
		 *     `intercessor_export_csv_cols_requesters`
		 *
		 * @param string $cols The export columns available.
		 */
		return apply_filters( 'intercessor_export_csv_cols_' . $this->export_type, $cols );
	}

	/**
	 * Output the CSV columns.
	 *
	 * @since 0.9.5
	 */
	public function csv_cols_out() {
		$cols = $this->get_csv_cols();
		$i    = 1;

		// Output each column.
		foreach ( $cols as $col_id => $column ) {
			echo '"' . addslashes( $column ) . '"';

			echo count( $cols ) === $i
				? ''
				: ',';

			$i++;
		}
		echo "\r\n";
	}

	/**
	 * Get the data being exported.
	 *
	 * @since 0.9.5
	 *
	 * @return array $data Data for export.
	 */
	public function get_data() {

		// Just a sample data array.
		$data = array(
			0 => [
				'id'   => '',
				'data' => date( 'F j, Y' ),
			],
			1 => [
				'id'   => '',
				'data' => date( 'F j, Y' ),
			],
		);

		/**
		 * Filters the data for this export.
		 *
		 * @param array $data The data to be exported.
		 */
		$data = apply_filters( 'intercessor_export_get_data', $data );

		/**
		 * Filters the data for this export with the export type.
		 *
		 * @param string $export_type The type of data to be exported.
		 * @param array  $data        The data to be exported.
		 */
		$data = apply_filters( 'intercessor_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Output the CSV rows.
	 *
	 * @since 0.9.5
	 */
	public function csv_rows_out() {
		$data = $this->get_data();

		$cols = $this->get_csv_cols();

		// Output each row.
		foreach ( $data as $row ) {
			$i = 1;
			foreach ( $row as $col_id => $column ) {
				// Ensure the column is valid.
				if ( array_key_exists( $col_id, $cols ) ) {
					echo '"' . $column . '"';
					echo $i == count( $cols ) + 1 ? '' : ',';
				}

				$i++;
			}
			echo "\r\n";
		}
	}

	/**
	 * Perform the export process.
	 *
	 * @since 0.9.5
	 *
	 *
	 * @uses Base::can_export()
	 * @uses Base::headers()
	 * @uses Base::csv_cols_out()
	 * @uses Base::csv_rows_out()
	 */
	public function process() {

		// Bail if user is unauthorized.
		if ( ! $this->can_export() ) {
			wp_die(
				esc_html__( 'You do not have permission to export Intercessor data.', 'intercessor' ),
				esc_html__( 'Error', 'intercessor' ),
				[ 'response' => 403 ]
			);
		}

		// Set headers.
		$this->headers();

		// Output CSV columns (headers).
		$this->csv_cols_out();

		// Output CSV rows.
		$this->csv_rows_out();

		/**
		 * Fires at the end of an export.
		 *
		 * The dynamic portion of the hook name, `$this->export_type`, refers to
		 * the export type set by the extending sub-class.
		 *
		 * @since 0.9.5
		 *
		 * @param string $this Base instance.
		 */
		do_action( "intercessor_export_{$this->export_type}_end", $this );

		intercessor_die();
	}
}
