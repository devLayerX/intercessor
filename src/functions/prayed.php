<?php
/**
 * Prayed Counts Functions
 *
 * @package     Intercessor
 * @subpackage  Functions/Prayed
 * @copyright   Copyright (c) 2021, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

use Intercessor\Database\Queries\Prayed_Counts;
use Intercessor\Requester;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'intercessor_prayed_date_query' ) ) {
	/**
	 * Query prayed requests with date
	 *
	 * @return int|void
	 * @since 1.0.0
	 */
    function intercessor_prayed_date_query() {
        $query = [
            'date_created_query' => [
                'after' => 'last month', // week, day, month.
            ],
        ];

        $prayed_query = [
            'fields' => 'prayer_id',
            'count'  => true,
            'date_created_query' => [
                'after' => 'last month', // week, day, month.
            ],
        ];

    //    $prayed = new Prayed_Counts( $prayed_query );
        $prayed = new Prayed_Counts();
        if ( $prayed ) {
            foreach ( $prayed as $prayed_for ) {
                $prayer_id = $prayed_for->prayer_id;
                $total     = $prayer_id;

                return intval( $total );
            }
        }
    }
}

if ( ! function_exists( 'intercessor_count_prayed' ) ) {
    /**
     * Count prayed requests
     *
     * @param array $args Arguments to parse.
     */
    function intercessor_count_prayed( array $args = [] ) : int {
        // Setup defaults.
        $defaults = wp_parse_args(
            $args,
            [
                'count'              => true,
                'date_created_query' => [
                    'after'          => 'last month', // week, day, month.
                ],
            ]
        );

        $prayed = new Prayed_Counts( $defaults );

        //return the counts.
        return absint( $prayed->found_items );
	}
}

if ( ! function_exists( 'intercessor_count_prayed_for' ) ) {
    /**
     * Count prayed for with arguments.
     *
     * @since 1.0.0
     * @return void
     */
    function intercessor_count_prayed_for() {
        $notify = intercessor_get_option( 'notify_period', 'weekly' );

        if ( 'daily' === $notify ) {
            $value = 'yesterday';
        } elseif ( 'monthly' === $notify ) {
            $value = 'last month';
        } else {
            $value = 'last week';
        }

        // Setup arguments.
        $args = [
            'date_created_query' => [
                'after' => $value, // week, day, month.
            ],
        ];

        // Retrieve prayers prayed for within specified period.
        $prayed_for = intercessor_get_items( 'prayed', $args );

        // Proceed only if prayed requests are available.
        if ( $prayed_for ) {
            foreach ( $prayed_for as $prayed ) {
                $prayed_id = $prayed->prayer_id;

                // Bail if no prayer ID.
               if ( ! $prayed_id ) {
                    return;
                }

                // Get prayer request.
                $prayer   = intercessor_get_item_by( 'prayer', 'id', $prayed_id );
                $to_email = $prayer->email;

                // Group all prayed requests and count.
                $count_args = [
                    'id__in'             => $prayed_id,
                    'count'              => true,
                    'groupby'            => 'prayed_id',
                    'date_created_query' => [
                        'after' => $value, // week, day, month.
                    ],
                ];

                // Get prayed requests and counts.
                $no_prayed = intercessor_get_items( 'prayed', $count_args );
                $counts    = intercessor_count_items( 'prayed', $count_args );

                // Bail if not prayed for.
                if ( ! $counts || $no_prayed ) {
                    return;
                }

                // Email prayed for notification if requested during prayer submission.
                if ( intercessor_get_prayer_notify( $prayed_id ) ) {
                    intercessor_email_prayed_notification( $prayed_id, $counts, $to_email, $prayer );
                }
            }
        }
    }
}

if ( ! function_exists( 'intercessor_get_prayed_for_counts' ) ) {
    /**
     * Get prayed for counts for a prayer ID.
     *
     * @param int $prayer_id Prayer ID.
     *
     * @since 1.0.0 
     */
    function intercessor_get_prayed_for_counts( int $prayer_id ) {
        // Bail if no prayer ID supplied.
        if ( empty( $prayer_id ) ) {
            return false;
        }

        // Setup prayed for args.
        $args = [
            'prayer_id' => $prayer_id,
        ];
     
        // Get prayed for counts.
        $prayed     = intercessor_get_items( 'prayed', $args );
        $key        = esc_attr( 'prayed_for' );
        $prayed_for = array_sum( array_column( $prayed, $key ) );

        // Return values of prayed counts.
        return apply_filters( 'intercesssor_get_prayed_for_counts', $prayed_for );
    }
}
