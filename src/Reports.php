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

use Intercessor\Database\Queries\Prayer;
use Intercessor\Database\Queries\Prayed_Counts;
use Intercessor\Database\Tables\Prayers;

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
	 * @param int|bool $prayer_id The prayer ID.
	 * @param string   $start     The start date.
	 * @param string   $end       The end date to query.
	 *
	 * @return false|void   Total amount of prayer based on the passed arguments.
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function get_prayed_for( int $prayer_id, string $start, string $end ) {
		// Set up array of variables.
		$args = [
			'count'              => true,
			'date_created_query' => [
				'after'  => $start,
				'before' => $end,
			],
		];

		// Get counts for a specific prayer ID.
		if ( ! empty( $prayer_id ) ) {
			$args['prayer_id'] = $prayer_id;
		}

		// Get prayed for counts.
		$counts = new Prayed_Counts( $args );

		// Return found items.
		return absint( $counts->found_items );
	}

	public function get_most_prayed_for( int $number, string $start, string $end, bool $show_titles ) {
		// Set up variables.
		$args = [
			'count'              => true,
			'date_created_query' => [
				'after'  => $start,
				'before' => $end,
			],
			'groupby'            => 'prayer_id',
			'offset'             => $number,
		];

		// Query for prayed counts.
		$query = new Prayed_Counts( $args );
	}

	/**
	 * Get requester count with date range.
	 *
	 * @param int         $number     Number of requesters to retrieve.
	 * @param string|bool $start_date Start date.
	 * @param string|bool $end_date   End date.
	 *
	 * @return int Number of requesters found.
	 * @since 1.0.0
	 * @access public
	 */
	public function get_requesters( int $number,  bool $start_date = false, bool $end_date = false ) {
		$args = [
			'number'             => $number,
			'date_created_query' => [
				'after'  => $start_date,
				'before' => $end_date,
			],
		];

		// Retrieve number of requesters.
		$requesters = new \Intercessor\Database\Queries\Requester( $args );

		return absint( $requesters->found_items );
	}

	/**
	 * Get most prayed for requests.
	 *
	 * @param int $number The number of prayers to retrieve from database.
	 *
	 * @return array|object|null
	 * @since 1.1.0
	 * @access public
	 */
	public function get_most_prayed_requests( int $number = 3 ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT prayer_id as prayed_id, max(prayed_for) as prayed_for
				 FROM $wpdb->ipr_prayed_counts
				 WHERE prayed_for > 0
				 GROUP BY prayed_for+0
				 DESC LIMIT %d;",$number
			)
		);
	}

	/**
	 * Retrieve prayers from the database
	 *
	 * @param array $args  Query arguments.
	 * @param bool  $count Prayer count.
	 *
	 * @since  0.9.5
	 * @access public
	 *
	 * @return array $prayers Array of `Intercessor\Prayer` objects.
	 */
	public function get_prayers( array $args = [] ) {
		global $wpdb;

		$defaults = [
			'number'    => 20,
			'offset'    => 0,
			'search'    => '',
			'exclude'   => [],
			'include'   => [],
			'orderby'   => 'id',
			'order'     => 'DESC',
			'prayer_in' => '',
			'display'   => '',
		];

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		// Search.
		if ( isset( $args['search'] ) && ! empty( $args['search'] ) ) {
			$args['search'] = $wpdb->esc_like( $args['search'] );
		}

		$where = $this->parse_where( $args );

		// Exclude specific prayers.
		if ( ! empty( $args['exclude'] ) ) {
			$where .= empty( $where ) ? 'WHERE ' : 'AND ';

			if ( is_array( $args['exclude'] ) ) {
				$exclude = implode( ',', array_map( 'intval', $args['exclude'] ) );
			} else {
				$exclude = intval( $args['exclude'] );
			}

			$where .= "`id` NOT IN( {$exclude} )";
		}

		// Include specific prayers.
		if ( ! empty( $args['include'] ) ) {
			$where .= empty( $where ) ? 'WHERE ' : 'AND ';

			if ( is_array( $args['include'] ) ) {
				$include = implode( ',', array_map( 'intval', $args['include'] ) );
			} else {
				$include = intval( $args['include'] );
			}

			$where .= "`id` IN( {$include} )";
		}

		// Specify the period of display.
		if ( ! empty( $args['display'] ) ) {
			$display = date( 'Y-m-d H:i:s', strtotime( $args['display'] ) );
			$where  .= " AND `date_active` >= '{$display}'";
		}

		// Set up columns.
		$columns = [
			'id'           => '%d',
			'user_id'      => '%d',
			'requester_id' => '%d',
			'email'        => '%s',
			'title'        => '%s',
			'message'      => '%s',
			'status'       => '%s',
			'prayer_key'   => '%s',
			'share'        => '%s',
			'notify'       => '%d',
			'date_created' => '%s',
			'date_active'  => '%s',
			'end_date'     => '%s',
		];

		// Orderby.
		$args['orderby'] = ! array_key_exists( $args['orderby'], $columns ) ? 'id' : $args['orderby'];
		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		// Set up variables.
		$key          = md5( 'intercessor_prayers_' . wp_json_encode( $args ) );
		$cache_group  = 'prayers';
		$last_changed = $this->get_last_changed();
		$cache_key    = "{$key}:{$last_changed}";
		$prayers      = wp_cache_get( $cache_key, $cache_group );

		// Get available prayers.
		if ( false === $prayers ) {
			$prayers = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT *
					FROM {$this->get_db()->ipr_prayers} {$where}
					ORDER BY {$args['orderby']} {$args['order']} 
					LIMIT %d, %d;",
					absint( $args['offset'] ),
					absint( $args['number'] )
				)
			);

			wp_cache_add( $cache_key, $prayers, $cache_group, 3600 );
		}

		return $prayers;
	}

	/**
	 * Parse `WHERE` clause for the SQL query.
	 *
	 * @param array $args Arguments.
	 *
	 * @since  0.9.5
	 * @access private
	 *
	 * @return string `WHERE` clause for the SQL query.
	 */
	private function parse_where( $args ) {
		$where = '';

		// Specific title.
		if ( ! empty( $args['title'] ) ) {

			if ( is_array( $args['title'] ) ) {
				$title = implode( "','", array_map( 'sanitize_text_field', $args['title'] ) );
			} else {
				$title = sanitize_text_field( $args['title'] );
			}

			$where .= " AND `title` IN( '{$title}' ) ";

		}

		// Specific statuses.
		if ( ! empty( $args['status'] ) ) {

			if ( is_array( $args['status'] ) ) {
				$statuses = implode( "','", array_map( 'sanitize_text_field', $args['status'] ) );
			} else {
				$statuses = sanitize_text_field( $args['status'] );
			}

			$where .= " AND `status` IN( '{$statuses}' ) ";

		}

		// Specific prayer ID.
		if ( ! empty( $args['prayer_in'] ) ) {
			$prayer_ids_in = implode( ',', wp_parse_id_list( $args['prayer_in'] ) );

			$where .= " AND `id` IN( '{$prayer_ids_in}' ) ";
		}

		// Created for a specific date or in a date range.
		if ( ! empty( $args['date_created'] ) ) {

			if ( is_array( $args['date_created'] ) ) {

				if ( ! empty( $args['date_created']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date_created']['start'] ) );

					$where .= " AND `date_created` >= '{$start}'";

				}

				if ( ! empty( $args['date_created']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date_created']['end'] ) );

					$where .= " AND `date_created` <= '{$end}'";

				}
			} else {

				$year  = date( 'Y', strtotime( $args['date_created'] ) );
				$month = date( 'm', strtotime( $args['date_created'] ) );
				$day   = date( 'd', strtotime( $args['date_created'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}
		}

		// Specific date_active date.
		if ( ! empty( $args['date_active'] ) ) {

			if ( is_array( $args['date_active'] ) ) {

				if ( ! empty( $args['date_active']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date_active']['start'] ) );

					$where .= " AND `date_active` >= '{$start}'";

				}

				if ( ! empty( $args['date_active']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date_active']['end'] ) );

					$where .= " AND `date_active` <= '{$end}'";

				}
			} else {

				$year  = date( 'Y', strtotime( $args['date_active'] ) );
				$month = date( 'm', strtotime( $args['date_active'] ) );
				$day   = date( 'd', strtotime( $args['date_active'] ) );

				$where .= " AND $year = YEAR ( date_active ) AND $month = MONTH ( date_active ) AND $day = DAY ( date_active )";
			}
		}

		// Specific end_date date.
		if ( ! empty( $args['end_date'] ) ) {

			if ( is_array( $args['end_date'] ) ) {

				if ( ! empty( $args['end_date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['end_date']['start'] ) );

					$where .= " AND `end_date` >= '{$start}'";

				}

				if ( ! empty( $args['end_date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['end_date']['end'] ) );

					$where .= " AND `end_date` <= '{$end}'";

				}
			} else {

				$year  = date( 'Y', strtotime( $args['end_date'] ) );
				$month = date( 'm', strtotime( $args['end_date'] ) );
				$day   = date( 'd', strtotime( $args['end_date'] ) );

				$where .= " AND $year = YEAR ( end_date ) AND $month = MONTH ( end_date ) AND $day = DAY ( end_date )";
			}
		}

		if ( ! empty( $where ) ) {
			$where = ' WHERE 1=1 ' . $where;
		}

		return $where;
	}

	/**
	 * Retrieves the value of the last_changed cache key for prayers.
	 *
	 * @since  0.9.5
	 * @access public
	 *
	 * @return string Value of the last_changed cache key for prayers.
	 */
	public function get_last_changed(): string {
		$cache_group = 'prayers';

		if ( function_exists( 'wp_cache_get_last_changed' ) ) {
			return wp_cache_get_last_changed( $cache_group );
		}

		$last_changed = wp_cache_get( 'last_changed', $cache_group );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $cache_group );
		}

		return $last_changed;
	}

	/**
	 * Return the global database interface.
	 *
	 * @since  1.1.0
	 * @access protected
	 * @static
	 *
	 * @return \wpdb|\stdClass
	 */
	protected static function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new \stdClass();
	}
}
