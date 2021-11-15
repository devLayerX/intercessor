<?php
/**
 * Intercessor Prayer Reports
 *
 * @package     Intercessor
 * @subpackage  Reports
 * @copyright   Copyright (c) 2021, Victor Aigbeghian
 * @license     https://opensource.org/licenses/GPL-3.0.0 GNU Public License
 * @since       1.0.0
 */

namespace Intercessor;

use DateTime;
use DateTimeZone;
use Intercessor\Database\Queries\Prayer;
use Intercessor\Database\Queries\Prayed_Counts;

use function intercessor_get_option;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Reports {

	/**
	 * The start date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as January 15, 2019),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public $start_date;

	/**
	 * The end date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as January 15, 2019),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * The end date is optional
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public $end_date;

    /**
     * Get things going.
     *
     * @access public
     * @since 1.0.0
     */
    public function __construct() {
    }

	/**
	 * Retrieve prayer request stats
	 *
	 * @param int          $prayer_id  The prayer to retrieve stats for. If false, gets stats for all prayers
	 * @param string|bool  $start_date The starting date for which we'd like to filter our prayer request stats. If false, we'll use the default start date of `this_month`
	 * @param string|bool  $end_date   The end date for which we'd like to filter our prayer request stats. If false, we'll use the default end date of `this_month`
	 * @param string|array $status     The prayer request status(es) to count. Only valid when retrieving global stats
	 *
	 * @return float|int   Total amount of prayer based on the passed arguments.
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function get_prayer_requests( int $prayer_id = 0, $start_date = false, $end_date = false, $status = 'active' ) {
        // Configure start date.
		if ( empty( $start_date ) ) {
			$start_date = 'this month';
		}

        if ( ! $prayer_id ) {
            if ( is_array( $status ) ) {
                $args = [
                    'count'              => true,
                    'status__in'         => [ $status ],
                    'date_created_query' => [
                        'after'  => $start_date,
                        'before' => $end_date,
                    ],
                ];
            } else {
                $args = [
                    'count'              => true,
                    'status'             => $status,
                    'date_created_query' => [
                        'after'  => $start_date,
                        'before' => $end_date,
                    ],
                ];
            }
        } else {
            $args = [
                'id'                 => $prayer_id,
                'count'              => true,
                'status'             => $status,
                'date_created_query' => [
                    'after'  => $start_date,
                    'before' => $end_date,
                ],
            ];
        }

        $counts = new Prayer( $args );

        // Return the prayer counts.
        return absint( $counts->found_items );
	}

	/**
	 * Retrieve prayer request stats
	 *
	 * @return false|void   Total amount of prayer based on the passed arguments.
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function get_prayed_for() {
	}
}
