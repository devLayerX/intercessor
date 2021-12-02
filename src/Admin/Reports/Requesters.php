<?php


namespace Intercessor\Admin\Reports;


class Requesters extends Base {

	/**
	 * Retrieve referral data
	 *
	 * @since 0.9.5
	 */
	public function get_data() {

		$dates = intercessor_get_report_dates();

		$start = $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'] . ' 00:00:00';
		$end   = $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'] . ' 23:59:59';
		$date  = array(
			'start' => $start,
			'end'   => $end
		);

		// Setup arguments to retrieve requesters by.
		$requester_args = [
			'orderby' => 'date_created',
			'order'   => 'ASC',
			'number'  => - 1,
			'date'    => $date,
		];

		// Retrieve requesters.
		$requesters       = intercessor_get_items( 'requester', $requester_args );
		$requester_data   = array();
		$requester_data[] = array( strtotime( $start ) * 1000 );
		$requester_data[] = array( strtotime( $end ) * 1000 );

		if ( $requesters ) {

			foreach ( $requesters as $requester ) {

				if ( 'today' == $dates['range'] || 'yesterday' == $dates['range'] ) {

					$point = strtotime( $requester->date_created ) * 1000;

					$requester_data[ $point ] = array( $point, 1 );

				} else {

					$time      = date( 'Y-n-d', strtotime( $requester->date_created ) );
					$timestamp = strtotime( $time ) * 1000;

					if ( array_key_exists( $time, $requester_data ) && isset( $requester_data[ $time ][1] ) ) {

						$count = $requester_data[ $time ][1] += 1;

						$requester_data[ $time ] = array( $timestamp, $count );

					} else {

						$requester_data[ $time ] = array( $timestamp, 1 );

					}


				}


			}

		}

		$data = array(
			esc_html__( 'Prayer Requesters', 'intercessor' ) => $requester_data
		);

		return $data;

	}
}
