<?php
/**
 * Message and Notice Functions
 *
 * @package     Intercessor
 * @subpackage  Functions/Notices
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.9.7
 */

use Intercessor\Session;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

/**
 * Get Errors
 *
 * Retrieves all error messages stored during the prayer submission process.
 * If errors exist, they are returned.
 *
 * @since 0.9.5
 * @uses \Intercessor\Session::get()
 * @return mixed array if errors are present, false if none found
 */
function intercessor_get_errors() {
    $session = new Session();
    $errors  = $session->get( 'intercessor_errors' );
	$errors  = apply_filters( 'intercessor_errors', $errors );
	return $errors;
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @since 0.9.6
 * @uses Intercessor\Session::get()
 * @param int    $error_id      ID of the error being set.
 * @param string $error_message Message to store with the error.
 * @return void
 */
function intercessor_set_error( $error_id, $error_message ) {
	$errors = intercessor_get_errors();
	if ( ! $errors ) {
		$errors = [];
	}
	$errors[ $error_id ] = $error_message;
    $session             = new Session();
	$session->set( 'intercessor_errors', $errors );
}

/**
 * Clears all stored errors.
 *
 * @since 0.9.6
 * @uses Intercessor\Session::set()
 * @return void
 */
function intercessor_clear_errors() {
    $session = new Session();
	$session->set( 'intercessor_errors', null );
}

/**
 * Removes (unsets) a stored error
 *
 * @since 0.9.6
 * @uses Intercessor\Session::set()
 * @param int $error_id ID of the error being set.
 *
 * @return string
 */
function intercessor_unset_error( $error_id ) {
	$errors = intercessor_get_errors();

	if ( $errors && isset( $errors[ $error_id ] ) ) {
		unset( $errors[ $error_id ] );
        $session = new Session();
		$session->set( 'intercessor_errors', $errors );
	}
}


/**
 * Print Errors
 *
 * Prints all stored errors. For use during prayer submission.
 * If errors exist, they are returned.
 *
 * @since 0.9.5
 * @uses intercessor_get_errors()
 * @uses intercessor_clear_errors()
 * @return void
 */
function intercessor_print_errors() {
	$errors = intercessor_get_errors();
	if ( $errors ) {

		$classes = apply_filters(
			'intercessor_error_class',
			[
                'intercessor_errors',
                'intercessor-alert',
                'intercessor-alert-error',
            ]
		);

		if ( ! empty( $errors ) ) {
			echo '<div class="' . implode( ' ', $classes ) . '">';
			// Loop error codes and display errors.
			foreach ( $errors as $error_id => $error ) {

				echo '<p class="intercessor_error" id="intercessor_error_' . $error_id . '"><strong>' . __( 'Error', 'intercessor' ) . '</strong>: ' . $error . '</p>';

			}

			echo '</div>';
		}

		intercessor_clear_errors();

	}
}
add_action( 'intercessor_prayer_request_form_top', 'intercessor_print_errors' );
add_action( 'intercessor_ajax_prayer_errors', 'intercessor_print_errors' );
add_action( 'intercessor_print_errors', 'intercessor_print_errors' );

/**
 * Render Intercessor frontend notices.
 *
 * @since 0.9.5
 */
function intercessor_output_frontend_notices() {
	$errors = intercessor_get_errors();

	if ( $errors ) {
		intercessor_frontend_errors( $errors );

		intercessor_clear_errors();
	}
}

/**
 * Print frontend errors.
 *
 * @since  0.9.5
 *
 * @param array $errors Array of errors.
 */
function intercessor_frontend_errors( $errors ) {
	// Bailout.
	if ( ! $errors ) {
		return;
	}

	/**
	 * Change auto_dismissible to dismissible and set the value to true
	 *
	 * @since 0.9.5
	 */
	$default_notice_args = array(
		'dismissible'      => true,
		'dismiss_interval' => 5000,
	);

	// Note: we will remove intercessor_errors class in future.
	$classes = apply_filters( 'intercessor_error_class', array( 'intercessor_notices', 'intercessor_errors' ) );

	echo sprintf( '<div class="%s">', implode( ' ', $classes ) );

	// Loop error codes and display errors.
	foreach ( $errors as $error_id => $error ) {

		$notice_args = wp_parse_args( $error['notice_args'], $default_notice_args );

		/**
		 * Filter to modify Frontend Errors args before errors is display.
		 *
		 * @since 0.9.5
		 */
		$notice_args = apply_filters( 'intercessor_frontend_errors_args', $notice_args );

		echo sprintf(
			'<div class="intercessor_error intercessor_notice" id="intercessor_error_%1$s" data-dismissible="%2$s" data-dismiss-interval="%3$d">
					<p><strong>%4$s</strong>: %5$s</p>
				</div>',
			$error_id,
			intercessor_clean( $notice_args['dismissible'] ),
			absint( $notice_args['dismiss_interval'] ),
			esc_html__( 'Error', 'intercessor' ),
			$error['message']
		);
	}

	echo '</div>';
}

/**
 * Print frontend notice.
 * Notice: notice type can be success/error/warning
 *
 * @since  0.9.5
 * @access public
 *
 * @param string $message     Message to display.
 * @param bool   $echo        Whether to echo the message. Default true.
 * @param string $notice_type Notice type.
 * @param array  $notice_args Notice arguments.
 *
 * @return  string
 */
function intercessor_display_frontend_notice( $message, $echo = true, $notice_type = 'warning', $notice_args = [] ) {
	if ( empty( $message ) ) {
		return '';
	}

	/**
	 * Change auto_dismissible to dismissible and set the value to true
	 *
	 * @since 0.9.5
	 */
	$default_notice_args = [
		'dismissible'      => false,
		'dismiss_type'     => 'auto',
		'dismiss_interval' => 5000,
	];

	$notice_args = wp_parse_args( $notice_args, $default_notice_args );

	// Notice dismissible must be true for dismiss type.
	$notice_args['dismiss_type'] = ! $notice_args['dismissible'] ? '' : $notice_args['dismiss_type'];

	/**
	 * Filter to modify Frontend notice args before notices is display.
	 *
	 * @since 0.9.5
	 */
	$notice_args = apply_filters( 'intercessor_frontend_notice_args', $notice_args );

	$close_icon  = 'manual' === $notice_args['dismiss_type'] ?
		sprintf(
			'<img class="notice-dismiss intercessor-notice-close" src="%s" />',
			esc_url( INTERCESSOR_URL . 'assets/images/close.svg' )
		) :
		'';

	$error = sprintf(
		'<div class="intercessor_notices intercessor_errors" id="intercessor_error_%1$s">
			<p class="intercessor_error intercessor_notice intercessor_%1$s" data-dismissible="%2$s" data-dismiss-interval="%3$d" data-dismiss-type="%4$s">
				%5$s
			</p>
			%6$s
		</div>',
		$notice_type,
		intercessor_clean( $notice_args['dismissible'] ),
		absint( $notice_args['dismiss_interval'] ),
		intercessor_clean( $notice_args['dismiss_type'] ),
		$message,
		$close_icon
	);

	if ( ! $echo ) {
		return $error;
	}

	echo $error;
}

/**
 * Register die handler for intercessor_die()
 *
 * @since 0.9.5
 */
function _intercessor_die_handler() {
	if ( defined( 'IPR_UNIT_TESTS' ) ) {
		return '_intercessor_die_handler';
	} else {
		die();
	}
}

/**
 * Wrapper function for wp_die().
 *
 * @since 0.9.5
 * @return void
 */
function intercessor_die( $message = '', $title = '', $status = 400 ) {
	add_filter( 'wp_die_ajax_handler', '_intercessor_die_handler', 10, 3 );
	add_filter( 'wp_die_handler', '_intercessor_die_handler', 10, 3 );
	wp_die( esc_attr( $message ), esc_attr( $title ), array( 'response' => esc_attr( $status ) ) );
}
