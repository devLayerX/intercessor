<?php
/**
 * User Functions
 *
 * Functions related to users / Requesters
 *
 * @package     Intercessor
 * @subpackage  Functions
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

use Intercessor\Prayer;
use Intercessor\Requester;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'intercessor_user_prayers' ) ) {
	/**
	 * Retrieve the prayer requests of a requester.
	 *
	 * @param int $user   User ID.
	 * @param int $number Number of prayers to retrieve.
	 *
	 * @return bool
	 * @since 0.9.5
	 */
	function intercessor_user_prayers( $user = 0, $number = 10 ) {
		if ( empty( $user ) ) {
			$user = get_current_user_id();
		}

		$status = [ 'active', 'personal', 'archived', ];
		$page   = intercessor_get_current_page_number();
		$args   = [
			'user'    => $user,
			'number'  => $number,
			'status'  => $status,
			'offset'  => $number * ( $page - 1 ),
			'orderby' => 'id',
			'order'   => 'DESC',
		];

		$by_user_id = is_numeric( $user ) ? true : false;
		$requester  = new Requester( $user, $by_user_id );
		$prayer_ids = $requester->get_prayer_ids();

		if ( ! empty( $prayer_ids ) ) {
			$args['id__in'] = $prayer_ids;
		}

		$args['requester_id'] = $requester->id;

	//	$query   = new \Intercessor\Database\Queries\Prayer();
		$prayers = intercessor_get_items( 'prayer', $args );

		// No prayers.
		if ( ! $prayers ) {
			return false;
		}

		return $prayers;
	}
}

if ( ! function_exists( 'intercessor_get_user_prayer_requests' ) ) {
	/**
	 * Retrieve prayer requests by a specific user.
	 *
	 * @param int|string $user       User ID or email address.
	 * @param bool       $pagination Use pagination for listing prayers. Default false.
	 *
	 * @return Query[]|false Array of prayer requests, false otherwise.
	 * @since 0.9.5
	 */
	function intercessor_get_user_prayer_requests( $user = 0, $pagination = false ) {

		$status = [ 'active', 'personal', 'archived' ];

		// Fall back to user ID.
		if ( empty( $user ) ) {
			$user = get_current_user_id();
		}

		// Bail if no user.
		if ( empty( $user ) ) {
			return false;
		}

		// Try to get requester.
		if ( is_numeric( $user ) ) {
			$requester = intercessor_get_item_by( 'requester', 'user_id', $user );
		} elseif ( is_email( $user ) ) {
			$requester = intercessor_get_item_by( 'requester', 'email', $user );
		} else {
			return false;
		}

		// Bail if no requester.
		if ( empty( $requester ) ) {
			return false;
		}

		// Format pagination if specified.
		$paged = 1;
		$page  = intercessor_get_current_page_number();

		if ( $pagination ) {
			if ( get_query_var( 'paged' ) ) {
				$paged = get_query_var( 'paged' );
			} elseif ( get_query_var( 'page' ) ) {
				$paged = get_query_var( 'page' );
			} else {
				$paged = 1;
			}
		}

		if ( $pagination ) {
			$args['page']     = $paged;
		} else {
			$args['nopaging'] = true;
		}

		// Fetch the prayer IDs.
		$number = apply_filters( 'intercessor_users_prayer_requests', 9999 );
		$args   = [
			'requester_id' => $requester->id,
			'fields'       => 'ids',
			'status'       => $status,
			'number'       => $number,
			'offset'       => $number * ( $page - 1 ),
			'orderby'      => 'id',
			'order'        => 'DESC',
		];
/*
		$query   = new \Intercessor\Database\Queries\Prayer();
		$prayers = $query->query( $args );
*/
		$prayers = intercessor_get_items( 'prayer', $args );

		// No prayers.
		if ( ! $prayers ) {
			return false;
		}

		return $prayers;
	}
}

/**
 * Get Prayer Status for User
 *
 * Retrieves the prayer count and the total amount spent for a specific user
 *
 * @access public
 * @since   0.9.5
 *
 * @param int|string $user - the ID or email of the requester to retrieve stats for.
 * @return      array
 */
function intercessor_get_prayer_stats_by_user( $user = '' ) {
	$field = '';
	if ( is_email( $user ) ) {
		$field = 'email';
	} elseif ( is_numeric( $user ) ) {
		$field = 'user_id';
	}

	$stats     = [];
	$requester = intercessor_get_requester_by( $field, $user );

	if ( $requester ) {
		$requester        = intercessor_get_requester( $requester->id );
		$stats['prayers'] = absint( $requester->prayer_count );
	}

	return (array) apply_filters( 'intercessor_prayer_stats_by_user', $stats, $user );
}

/**
 * Validate a potential username
 *
 * @access public
 * @since  0.9.5
 *
 * @param string $username The username to validate.
 * @return bool
 */
function intercessor_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid     = ( $sanitized === $username );
	return (bool) apply_filters( 'intercessor_validate_username', $valid, $username );
}

/**
 * Attach the requester to an existing user account when completing guest prayer
 *
 * This only runs when a user account already exists and a guest prayer is made
 * with the account's email address
 *
 * After attaching the requester to the user ID, the account is set to pending
 *
 * @param object $requester The \Intercessor\Requester object.
 *
 * @since  0.9.5
 * @return void
 */
function intercessor_connect_guest_requester_to_existing_user( $requester ) {

	if ( ! empty( $requester->user_id ) ) {
		return;
	}

	$user = get_user_by( 'email', $requester->email );

	if ( ! $user ) {
		return;
	}

	$requester->update(
		array(
			'user_id' => $user->ID,
		)
	);

}

/**
 * Attach the newly created user_id to a requester, if one exists
 *
 * @since  0.9.5
 * @param int $user_id The User ID that was created.
 * @return void
 */
function intercessor_connect_existing_requester_to_new_user( $user_id ) {
	$email = get_the_author_meta( 'user_email', $user_id );

	// Update the user ID on the requester.
	$requester = new Requester( $email );

	if ( $requester->id > 0 ) {
		$requester->update(
			[
			    'user_id' => $user_id,
            ]
		);
	}
}

/**
 * Looks up prayers by email that match the registering user
 *
 * This is for users that prayer as a guest and then came
 * back and created an account.
 *
 * @access      public
 * @since       0.9.5
 * @param       int $user_id - the new user's ID.
 * @return      void
 */
function intercessor_add_past_prayers_to_new_user( $user_id ) {
/*
	$email   = get_the_author_meta( 'user_email', $user_id );

	// Bail if email address is not supplied.
    if ( empty( $email ) ) {
        return;
    }

	$prayers = intercessor_get_items(
		'prayer',
		array(
			's'      => $email,
			'output' => 'prayers',
		)
	);

	if ( $prayers ) {

		// Set a flag to force the account to be verified before prayer history can be accessed.

		foreach ( $prayers as $prayer ) {
			if ( is_object( $prayer ) && $prayer instanceof Prayer ) {
				if ( intval( $prayer->user_id ) > 0 ) {
					continue; // This prayer already associated with an account.
				}

				$prayer->user_id = $user_id;
				$prayer->save();
			}
		}
	}
*/
}

/**
 * When a user is deleted, detach that user id from the requester record
 *
 * @since  0.9.5
 * @param  int $user_id The User ID being deleted.
 * @return bool         If the detachment was successful
 */
function intercessor_detach_deleted_user( $user_id ) {

	$requester = new Requester( $user_id, true );
	$detached  = false;

	if ( $requester->id > 0 ) {
		$detached = $requester->update( array( 'user_id' => 0 ) );
	}

	do_action( 'intercessor_detach_deleted_user', $user_id, $requester, $detached );

	return $detached;
}


/**
 * Enable registration during prayer submission.
 *
 * @since  0.9.5
 * @return bool $enable True if registration is enabled, false otherwise
 */
function intercessor_enable_registration() {
	$enable = intercessor_get_option( 'enable_registration', false );
	return (bool) apply_filters( 'intercessor_enable_registration', $enable );

}

/**
 * Account creation is required for prayer submission.
 *
 * @since  0.9.5
 * @return bool True if account is required or otherwise false
 */
function intercessor_account_required() {
	$account_required = intercessor_get_option( 'logged_in_only', false );
	return (bool) apply_filters( 'intercessor_account_required', $account_required );

}

if ( ! function_exists( 'intercessor_can_post_prayer' ) ) :
/**
 * User who can submit prayer request.
 *
 * @since  0.9.5
 * @return mixed|void
 */
function intercessor_can_post_prayer() {
	// Everyone can post prayer for now.
	$can_post = true;

	if ( ! is_user_logged_in() ) {
		if ( intercessor_account_required() && ! intercessor_enable_registration() ) {
			$can_post = false;
		}
	}

	return apply_filters( 'intercessor_can_post_prayer', $can_post );
}
endif;

if ( ! function_exists( 'intercessor_can_edit_prayer' ) ) :
	/**
	 * User who can submit prayer request.
	 *
	 * @since  0.9.5
	 *
	 * @param  int $prayer_id The prayer request ID.
	 * @return mixed|void
	 */
	function intercessor_can_edit_prayer( $prayer_id ) {
		// Everyone can edit prayer for now.
		$can_edit = true;

		if ( ! $prayer_id ) {
			$can_edit = false;
		} else {
			$prayer       = new Prayer( $prayer_id );
			$requester_id = intercessor_get_prayer_requester_id( $prayer_id );
			$requester    = new Requester( $requester_id );

			if ( ! $prayer || ( absint( $requester->id !== $prayer->requester_id ) ) ) {
				$can_edit = false;
			}
		}

		return apply_filters( 'intercessor_can_edit_prayer', $can_edit );
	}
endif;

if ( ! function_exists( 'intercessor_create_new_requester' ) ) {

	/**
	 * Create a new requester from the prayer history page.
	 *
	 * @param  array $user_data Array of data to create new user.
	 *
	 * @return int|WP_Error Returns Int (user ID) on success, WP_Error on failure.
	 */
	function intercessor_create_new_requester( $user_data = [] ) {
		$email     = $user_data['user_email'];
		$username  = $user_data['user_login'];
		$password  = $user_data['user_pass'];
		$password2 = $user_data['user_pass2'];

		// Check the email address.
		if ( empty( $email ) || ! is_email( $email ) ) {
			return new WP_Error( 'registration-error-invalid-email', __( 'Please provide a valid email address.', 'intercessor' ) );
		}

		if ( email_exists( $email ) ) {
			return new WP_Error( 'registration-error-email-exists', apply_filters( 'intercessor_registration_error_email_exists', __( 'An account is already registered with your email address. Please log in.', 'intercessor' ), $email ) );
		}

		// Handle username creation.
		if ( ! intercessor_generate_username() || ! empty( $username ) ) {
			$username = sanitize_user( $username );

			if ( empty( $username ) || ! validate_username( $username ) ) {
				return new WP_Error( 'registration-error-invalid-username', __( 'Please enter a valid account username.', 'intercessor' ) );
			}

			if ( username_exists( $username ) ) {
				return new WP_Error( 'registration-error-username-exists', __( 'An account is already registered with that username. Please choose another.', 'intercessor' ) );
			}

			if ( strlen( $username ) > 60 ) {
				return new WP_Error( 'registration-error-username-lenght', __( 'Invalid Username. Must be between 1 and 60 characters.', 'intercessor' ) );
			}

			if ( is_numeric( $username ) ) {
				return new WP_Error( 'registration-error-username-numeric', __( 'Invalid username. Username must include at least one letter.', 'intercessor' ) );
			}

		} else {
			$username = sanitize_user( current( explode( '@', $email ) ), true );

			// Ensure username is unique.
			$append     = 1;
			$o_username = $username;

			while ( username_exists( $username ) ) {
				$username = $o_username . $append;
				$append++;
			}
		}

		// Handle password creation.
		$password_generated = false;
		if ( intercessor_generate_password() && empty( $password ) ) {
			$password           = wp_generate_password();
			$password_generated = true;
		}

		if ( empty( $password ) ) {
			return new WP_Error( 'registration-error-missing-password', esc_html__( 'Please enter an account password.', 'intercessor' ) );
		}

		if ( strlen( $password ) < 8 ) {
			return new WP_Error( 'registration-error-short-password', esc_html__( 'Passwords must be at least 8 characters long.', 'intercessor' ) );
		}

		if ( empty( $password2 ) || $password !== $password2 ) {
			return new WP_Error( 'registration-error-missmatched-password', esc_html__( 'Passwords do not match.', 'intercessor' ) );
		}

		// Use WP_Error to handle registration errors.
		$errors = new WP_Error();

		/**
		 * Fires before a user is registered.
		 *
		 * @param string   $username Username.
		 * @param string   $email    Email.
		 * @param WP_Error $errors   Error.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_pre_register_user', $username, $email, $errors );

		/**
		 * Filters the errors before a user is registered.
		 *
		 * @param WP_Error $errors   Error.
		 * @param string   $username Username.
		 * @param string   $email    Email.
		 *
		 * @since 0.9.5
		 */
		$errors = apply_filters( 'intercessor_registration_errors', $errors, $username, $email );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		$new_requester_data = apply_filters(
			'intercessor_new_requester_data',
			[
				'user_login'      => $username,
				'user_pass'       => $password,
				'user_email'      => $email,
				'first_name'      => isset( $user_data['first_name'] ) ? $user_data['first_name'] : '',
				'last_name'       => isset( $user_data['last_name'] )  ? $user_data['last_name']  : '',
				'user_registered' => date( 'Y-m-d H:i:s' ),
				'role'       	  => 'requester',
			]
		);

		$new_user_id = wp_insert_user( $new_requester_data );

		if ( is_wp_error( $new_user_id ) ) {
			return new WP_Error( 'registration-error', __( 'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'intercessor' ) );
		}

		/**
		 * Fires after a user is registered.
		 *
		 * @param int   $new_user_id        The ID of the newly inserted user.
		 * @param array $new_requester_data Array of data to register user.
		 * @param bool  $password_generated True if password was generated, otherwise false.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_created_user', $new_user_id, $new_requester_data, $password_generated );

		// Log user in.
		intercessor_log_user_in( $new_user_id, $new_requester_data['user_login'], $new_requester_data['user_pass'] );

		return $new_user_id;
	}
}

/**
 * Register And Login New User
 *
 * @param array $user_data
 *
 * @return int|mixed|void|WP_Error
 * @access  private
 * @since   0.9.5
 *
 */
function intercessor_create_new_user( $user_data = [] ) {
	// Verify the array
	if ( empty( $user_data ) ) {
		return -1;
	}

	// Check the email address.
	if ( empty( $user_data['user_email'] ) || ! is_email( $user_data['user_email'] ) ) {
		return new WP_Error( 'registration-error-invalid-email', __( 'Please provide a valid email address.', 'intercessor' ) );
	}

	if ( email_exists( $user_data['user_email'] ) ) {
		return new WP_Error( 'registration-error-email-exists', apply_filters( 'intercessor_registration_error_email_exists', __( 'An account is already registered with your email address. Please log in.', 'intercessor' ), $user_data['user_email'] ) );
	}

	// Handle username creation.
	if ( '0' === intercessor_get_option( 'intercessor_generate_username' ) || ! empty( $user_data['user_login'] ) ) {
		$username = sanitize_user( $user_data['user_login'] );

		if ( empty( $user_data['user_login'] ) || ! validate_username( $user_data['user_login'] ) ) {
			return new WP_Error( 'registration-error-invalid-username', __( 'Please enter a valid account username.', 'intercessor' ) );
		}

		if ( username_exists( $user_data['user_login'] ) ) {
			return new WP_Error( 'registration-error-username-exists', __( 'An account is already registered with that username. Please choose another.', 'intercessor' ) );
		}
	} else {
		$username = sanitize_user( current( explode( '@', $user_data['user_email'] ) ), true );

		// Ensure the username is unique.
		$append     = 1;
		$o_username = $username;

		while ( username_exists( $username ) ) {
			$username = $o_username . $append;
			$append++;
		}
	}

	// Handle password creation.
	if ( '1' === intercessor_get_option( 'intercessor_generate_password' ) && empty( $user_data['user_pass'] ) ) {
		$password = wp_generate_password();
	} elseif ( empty( $user_data['user_pass'] ) ) {
		return new WP_Error( 'registration-error-missing-password', __( 'Please enter an account password.', 'intercessor' ) );
	} elseif ( ! empty( $user_data['user_pass'] ) && empty( $user_data['user_pass2'] ) || $user_data['user_pass2'] !== $user_data['user_pass'] ) {
		return new WP_Error( 'registration-error-irregular-password', __( 'Both passwords must be the same.', 'intercessor' ) );
	} elseif ( ! empty( $user_data['user_pass'] ) && ! empty( $user_data['user_pass2'] ) || $user_data['user_pass2'] === $user_data['user_pass'] ) {
		$password = esc_attr( $user_data['user_pass'] );
	} else {
		$password = '';
	}

	// Use WP_Error to handle registration errors.
	$errors = new WP_Error();

	/**
	 * Fires before a new user is inserted
	 *
	 * @param string $username The new user username
	 * @param string $email    The email address
	 * @param string $errors   WP_Error
	 *
	 * @since 0.9.5
	 */
	do_action( 'intercessor_pre_insert_user', $username, $user_data['user_email'], $errors );

	$errors = apply_filters( 'intercessor_registration_errors', $errors, $username, $user_data['user_email'] );

	if ( $errors->get_error_code() ) {
		return $errors;
	}

	$user_args = apply_filters( 'intercessor_insert_user_args', array(
		'user_login'      => isset( $username ) ? $username : '',
		'user_pass'       => isset( $password )  ? $password  : '',
		'user_email'      => isset( $user_data['user_email'] ) ? $user_data['user_email'] : '',
		'first_name'      => isset( $user_data['user_first'] ) ? $user_data['user_first'] : '',
		'last_name'       => isset( $user_data['user_last'] )  ? $user_data['user_last']  : '',
		'user_registered' => date( 'Y-m-d H:i:s' ),
		'role'            => 'requester'
	), $user_data );

	// Insert new user
	$user_id = wp_insert_user( $user_args );

	// Validate inserted user
	if ( is_wp_error( $user_id ) ) {
		return -1;
	}

	/**
	 * Filter the user data
	 *
	 * @param array $user_data Array of user data.
	 * @param array $user_args Array of arguments to create the user with.
	 *
	 * @since 0.9.5
	 */
	$user_data = apply_filters( 'intercessor_insert_user_data', $user_data, $user_args );

	/**
	 * Fires after a new user is inserted
	 *
	 * @param int   $user_id    The new user ID
	 * @param array $user_data  Array of user data
	 *
	 * @since 0.9.5
	 */
	do_action( 'intercessor_insert_user', $user_id, $user_data );

	// Login new user
	intercessor_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );

	// Return user id
	return $user_id;
}

/**
 * Log User In
 *
 * @param int    $user_id    User ID
 * @param string $user_login Username
 * @param string $user_pass  Password.
 *
 * @since 0.9.5
 * @return void
 */
function intercessor_log_user_in( $user_id, $user_login, $user_pass ) {
	if ( $user_id < 1 ) {
		return;
	}

	wp_set_auth_cookie( $user_id, true );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'intercessor_log_user_in', $user_id, $user_login, $user_pass );
}

/**
 *  Check if username should be generated automatically from email
 *
 * @return bool
 */
function intercessor_generate_username() {
	return (bool) intercessor_get_option( 'generate_username' ) ? true : false;
}

/**
 *  Check if password should be generated automatically for new user
 *
 * @return bool
 */
function intercessor_generate_password() {
	return (bool) intercessor_get_option( 'generate_password' ) ? true : false;
}


/**
 * Prevent requesters from seeing the admin bar.
 *
 * @param bool $show_admin_bar If should display admin bar.
 *
 * @return bool
 */
function intercessor_disable_admin_bar( $show_admin_bar ) {
	if ( apply_filters( 'intercessor_disable_admin_bar', true )
		&& ! ( current_user_can( 'edit_posts' )
		|| current_user_can( 'edit_prayers' ) )
	) {
		$show_admin_bar = false;
	}

	return $show_admin_bar;
}
add_filter( 'show_admin_bar', 'intercessor_disable_admin_bar', 10, 1 );
