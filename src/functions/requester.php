<?php
/**
 * Requester Functions
 *
 * @package     IPR
 * @subpackage  Functions/Requester
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.9.5
*/

// Exit if accessed directly.
use Intercessor\Requester;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'intercessor_get_requester' ) ) {
    /**
     * Get a requester by ID
     *
     * @param int $requester_id Requester ID.
     *
     * @return \Intercessor\Requester
     * @since 0.9.5
     */
    function intercessor_get_requester( int $requester_id = 0 ) {
        return intercessor_process_item('requester', 'get', $requester_id, false );
    }
}

if ( ! function_exists( 'intercessor_get_requester_by' ) ) {
	/**
	 * Get a requester by a specific field value
	 *
	 * @param string $field Field.
	 * @param string $value Value.
	 *
	 * @since 0.9.5
	 *
	 * @uses  \intercessor_get_item_by()
	 */
	function intercessor_get_requester_by( $field = '', $value = '' ) {
		// Return requester.
		return intercessor_get_item_by( 'requester', $field, $value );
	}
}

/**
 * Count number of prayers of a requester
 *
 * Returns total number of prayers a requester has made
 *
 * @access      public
 * @since       0.9.5
 * @param       mixed $user - ID or email
 * @return      int - the total number of prayers
 */
function intercessor_count_prayers_of_requester( $user = null ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	$stats = ! empty( $user ) ? intercessor_get_prayer_stats_by_user( $user ) : false;

	return isset( $stats['prayers'] ) ? $stats['prayers'] : 0;
}

if ( ! function_exists( 'intercessor_get_prayer_requester_id' ) ) {
	/**
	 * Get the requester ID associated with a prayer
	 *
	 * @param int $prayer_id Prayer ID
	 *
	 * @return string $requester_id Requester ID
	 * @since 0.9.5
	 */
	function intercessor_get_prayer_requester_id( $prayer_id = null ) {
		$prayer = intercessor_process_item( 'prayer', 'get', $prayer_id, false );
		return $prayer->requester_id;
	}
}

/**
 * Updates the email address of a requester record when the email on a user is updated
 *
 * @access  public
 *
 * @param int   $user_id       User ID:
 * @param array $old_user_data Old user data array.
 *
 * @return false
 * @since   0.9.5
 */
function update_requester_email_on_user_update( $user_id, $old_user_data ) {
	// Bail if no user ID provided.
	if ( $user_id < 1 ) {
		return false;
	}

	$requester = new Requester( $user_id, true );

	// Bail if user is not a requester.
	if ( ! $requester ) {
		return false;
	}

	$user = get_userdata( $user_id );

	if ( ! empty( $user ) && $user->user_email !== $requester->email ) {

		if ( ! intercessor_get_requester_by( 'email', $user->user_email ) ) {

			$success = intercessor_process_item(
				'requester',
			    'update',
			    $requester->id,
                [
	                'email' => $user->user_email,
                ]
			);

			if ( $success ) {
				// Update some prayer meta if we need to.
				$prayers_array = explode( ',', $requester->prayer_ids );

				if ( ! empty( $prayers_array ) ) {

					foreach ( $prayers_array as $prayer_id ) {

						intercessor_update_item_meta( 'prayer', $prayer_id, 'email', $user->user_email );

					}

				}

				do_action( 'intercessor_update_requester_email_on_user_update', $user, $requester );

			}

		}

	}

}

if ( ! function_exists( 'intercessor_get_requester_counts' ) ) {

	/**
	 * Query for and return array of requester counts, keyed by status.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments. See `Intercessor\Database\Queries\Requester` for
	 *                    accepted arguments.
	 * @return array Requester counts keyed by status.
	 */
	function intercessor_get_requester_counts( $args = array() ) {

		// Parse arguments
		$r = wp_parse_args( $args, array(
			'count'   => true,
			'groupby' => 'status',
		) );

		// Query for count.
		$counts = new Intercessor\Database\Queries\Requester( $r );

		// Format & return
		return intercessor_get_counts_format( $counts, $r['groupby'] );
	}
}

if ( ! function_exists( 'intercessor_get_requester_prayers' ) ) {
	/**
	 * Get prayer or prayer counts of requester.
	 *
	 * @param int $requester_id Requester ID.
	 * @param bool $counts Whether to retrieve prayer counts.
	 *
	 * @since 1.1.0
	 * 
	 * @return array|int Array or number of prayers of requester.
	 */
	function intercessor_get_requester_prayers( int $requester_id, bool $counts ) {
		// Bail if no requester ID supplied.
		if ( empty( $requester_id ) ) {
			return false;
		}

		// Setup arguments to get prayers.
		$prayer_args = [
			'requester_id__in' => $requester_id,
		];

		// Get prayer(s) for requester.
		$prayers = intercessor_get_items( 'prayer', $prayer_args );

		// Get counts of prayer.
		if ( true === $counts ) {
			$found = intercessor_count_items( 'prayer', $prayer_args );
		} else {
			$found = $prayers;
		}
		
		// Return array or number of prayers.
		return apply_filters( 'intercessor_get_requester_prayers', $found );
	}
}
