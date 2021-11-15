<?php
/**
 * Intercessor Reports Graph.
 *
 * @package     Intercessor
 * @subpackage  Reports/Graph
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-0.9.5.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Reports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Prayers Report
 *
 * @package Intercessor\Admin\Reports
 *
 * @since 0.9.5
 */

class Prayers extends Base {

	/**
	 * Get things started
	 *
	 * @param array $_data Array of data.
	 *
	 * @since 0.9.5
	 */
	public function __construct( $_data = array() ) {

		if ( empty( $_data ) ) {
			$this->data = $this->get_data();
		}

		parent::__construct();

		// Generate unique ID.
		$this->id = md5( wp_rand() );

		// Setup default options.
		$this->options = array(
			'y_mode'          => null,
			'y_decimals'      => 0,
			'x_decimals'      => 0,
			'y_position'      => 'right',
			'time_format'     => '%d/%b',
			'ticksize_unit'   => 'day',
			'ticksize_num'    => 1,
			'multiple_y_axes' => false,
			'bgcolor'         => '#f9f9f9',
			'bordercolor'     => '#ccc',
			'color'           => '#bbb',
			'borderwidth'     => 2,
			'bars'            => false,
			'lines'           => true,
			'points'          => true,
			'prayer_id'       => false,
		);

	}

	/**
	 * Retrieve prayer data
	 *
	 * @since 0.9.5
	 */
	public function get_data() {

		$active   = array();
		$archived = array();
		$personal = array();
		$pending  = array();

		$dates = intercessor_get_report_dates();

		$start = $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'] . ' 00:00:00';
		$end   = $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'] . ' 23:59:59';
		$date  = array(
			'start' => $start,
			'end'   => $end,
		);

		$args = array(
			'orderby'   => 'date',
			'order'     => 'ASC',
			'date'      => $date,
			'number'    => -1,
			'prayer_id' => $this->get( 'prayer_id' ),
		);

		$prayers = intercessor_get_prayers( $args );

		$pending[] = array( strtotime( $start ) * 1000 );
		$pending[] = array( strtotime( $end ) * 1000 );

		if ( $prayers ) {
			foreach ( $prayers as $prayer ) {

				switch ( $prayer->status ) {

					case 'active':
						$active[] = array( strtotime( $prayer->date_created ) * 1000 );

						break;

					case 'archived':
						$archived[] = array( strtotime( $prayer->date_created ) * 1000 );

						break;

					case 'personal':
						$personal[] = array( strtotime( $prayer->date_created ) * 1000 );

						break;

					case 'pending':
						$pending[] = array( strtotime( $prayer->date_created ) * 1000 );

						break;

					default :
						break;

				}
			}
		}

		$data = array(
			esc_html__( 'Active Prayer Requests', 'intercessor' )  => $active,
			esc_html__( 'Private Prayer Requests', 'intercessor' ) => $personal,
			esc_html__( 'Pending  Prayer Requests', 'intercessor' ) => $pending,
			esc_html__( 'Archived Prayer Requests', 'intercessor' ) => $archived,
		);

		return $data;

	}

}
