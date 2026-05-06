<?php
/**
 * Front-end public loader.
 *
 * @package Intercessor
 * @since   1.0.0
 */
declare(strict_types=1);

namespace Intercessor\Public;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


use Intercessor\Admin\Settings;
use Intercessor\Database\Query\Prayed_Count_Query;
use Intercessor\Database\Query\Prayer_Request_Query;
use Intercessor\Database\Query\Requester_Query;
use Intercessor\Http\Request;
use Intercessor\Util\Notifier;
use Intercessor\Util\Profanity_Filter;
use Intercessor\Util\Rate_Limiter;
use Intercessor\Util\Recaptcha;
use Intercessor\Util\Registration_Handler;

/**
 * Handles all front-end (public) concerns for the Intercessor plugin.
 *
 * All superglobal access is centralised through a Request instance created
 * via Request::capture() at the top of each AJAX handler so every input is
 * unslashed and typed before use.
 *
 * Submission validation order:
 *   1. Nonce verification.
 *   2. Login gate (if require_login is enabled).
 *   3. reCAPTCHA token verification (if enabled for the form).
 *   4. Field presence / format validation.
 *   5. Daily rate limit check per email address (HTTP 429 on breach).
 *   6. Profanity filter — flags matching requests to 'pending' with a
 *      moderator note; does NOT block the submission.
 *   7. DB insert + email notifications.
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class Public_Loader {

	/**
	 * Register all front-end WordPress hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		add_action( 'wp_ajax_intercessor_submit_request',        array( $this, 'handle_form_submission' ) );
		add_action( 'wp_ajax_nopriv_intercessor_submit_request', array( $this, 'handle_form_submission' ) );

		add_action( 'wp_ajax_intercessor_record_prayer',        array( $this, 'handle_record_prayer' ) );
		add_action( 'wp_ajax_nopriv_intercessor_record_prayer', array( $this, 'handle_record_prayer' ) );
	}

	/**
	 * Enqueue the front-end stylesheet on all public pages.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function enqueue_assets(): void {
		wp_enqueue_style(
			'intercessor-iconfont',
			INTERCESSOR_URL . 'assets/css/iconfont.css',
			array(),
			INTERCESSOR_VERSION
		);

		wp_enqueue_style(
			'intercessor-public',
			INTERCESSOR_URL . 'assets/css/public.css',
			array( 'intercessor-iconfont' ),
			INTERCESSOR_VERSION
		);

		// Register a minimal front-end JS handle so that wp_localize_script()
		// and wp_add_inline_script() calls targeting 'intercessor-public' have
		// a real script to attach to. The handle is registered with an empty
		// src — WordPress will not emit a <script src=""> tag for it, but the
		// localised data will still be output as a separate inline <script>.
		if ( ! wp_script_is( 'intercessor-public', 'registered' ) ) {
			wp_register_script(
				'intercessor-public',
				'',               // no src — inline data only
				array(),
				INTERCESSOR_VERSION,
				true
			);
			wp_enqueue_script( 'intercessor-public' );
		}
	}

	// -------------------------------------------------------------------------
	// AJAX: prayer form submission
	// -------------------------------------------------------------------------

	/**
	 * AJAX handler for prayer form block submissions.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function handle_form_submission(): void {
		$req = Request::capture();

		// 1. Nonce.
		if ( ! $req->verify_nonce( 'intercessor_submit', 'nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'intercessor' ) ), 403 );
		}

		// 2. Login gate.
		if ( Settings::get( 'require_login', false ) && ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in to submit a prayer request.', 'intercessor' ) ), 401 );
		}

		// 3. reCAPTCHA.
		if ( Recaptcha::is_enabled_for_form() ) {
			$token = $req->get_string( 'g-recaptcha-response' );
			if ( ! Recaptcha::verify( $token, $req->get_remote_addr() ) ) {
				wp_send_json_error( array( 'message' => __( 'reCAPTCHA verification failed. Please try again.', 'intercessor' ) ), 403 );
			}
		}

		// 4. Read and validate fields.
		$first_name = $req->get_string( 'first_name' );
		$last_name  = $req->get_string( 'last_name' );
		$email      = $req->get_email( 'requester_email' );
		$subject    = $req->get_string( 'subject' );
		$content    = $req->get_textarea( 'content' );
		$anonymous  = (bool) $req->input( 'is_anonymous', false );

		$errors = array();

		if ( $first_name === '' ) {
			$errors[] = __( 'First name is required.', 'intercessor' );
		}
		if ( ! is_email( $email ) ) {
			$errors[] = __( 'A valid email address is required.', 'intercessor' );
		}
		if ( $subject === '' ) {
			$errors[] = __( 'Subject is required.', 'intercessor' );
		}
		if ( $content === '' ) {
			$errors[] = __( 'Prayer request content is required.', 'intercessor' );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json_error( array( 'message' => implode( ' ', $errors ) ), 422 );
		}

		// 5. Rate limit.
		if ( ! Rate_Limiter::is_allowed( $email ) ) {
			$limit = Rate_Limiter::get_limit();
			wp_send_json_error(
				array(
					// translators: %s: error message string
					'message' => sprintf(
						/* translators: %d: daily submission limit number */
						_n(
							'You may only submit %d prayer request per day. Please try again tomorrow.',
							'You may only submit %d prayer requests per day. Please try again tomorrow.',
							$limit,
							'intercessor'
						),
						$limit
					),
				),
				429
			);
		}

		// 6. Profanity filter.
		$autoApprove   = (bool) Settings::get( 'auto_approve', false );
		$initialStatus = $autoApprove ? 'approved' : 'pending';
		$moderatorNote = '';

		if ( Profanity_Filter::is_enabled() ) {
			$matched = array_unique( array_merge(
				Profanity_Filter::get_matched_words( $subject ),
				Profanity_Filter::get_matched_words( $content )
			) );

			if ( ! empty( $matched ) ) {
				$initialStatus = 'pending';
				$moderatorNote = Profanity_Filter::build_moderator_note( $matched );
			}
		}

		// 7. Find/create requester and insert the prayer request.
		$requesterQuery = new Requester_Query();
		$requesterId    = $requesterQuery->find_or_create( $email, $first_name, $last_name );

		if ( ! $requesterId ) {
			wp_send_json_error( array( 'message' => __( 'Could not save requester information.', 'intercessor' ) ), 500 );
		}

		$prayerQuery = new Prayer_Request_Query();
		$newId       = $prayerQuery->add_item( array(
			'requester_id'   => $requesterId,
			'subject'        => $subject,
			'content'        => $content,
			'status'         => $initialStatus,
			'is_anonymous'   => $anonymous ? 1 : 0,
			'is_public'      => 1,
			'moderator_note' => $moderatorNote,
		) );

		if ( ! $newId ) {
			wp_send_json_error( array( 'message' => __( 'Could not save your prayer request.', 'intercessor' ) ), 500 );
		}

		Notifier::notify_admin_new_request( $newId );
		Notifier::notify_requester_received( $newId );

		// Optionally create a WordPress account for the submitter.
		// The prayer request is already saved at this point — registration
		// errors are reported as advisory notices, not as fatal failures.
		$reg_errors = Registration_Handler::maybe_create_account(
			$email,
			$first_name,
			$last_name,
			$_POST // phpcs:ignore WordPress.Security.NonceVerification.Missing
		);

		$success_message = __( 'Thank you. Your prayer request has been received.', 'intercessor' );

		if ( empty( $reg_errors ) && ! empty( $_POST['create_account'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$success_message .= ' ' . __( 'A confirmation email has been sent — please check your inbox to activate your account.', 'intercessor' );
		} elseif ( ! empty( $reg_errors ) ) {
			$success_message .= ' ' . implode( ' ', $reg_errors );
		}

		wp_send_json_success( array(
			'message' => $success_message,
			'id'      => $newId,
		) );
	}

	// -------------------------------------------------------------------------
	// AJAX: "I prayed for this" interaction
	// -------------------------------------------------------------------------

	/**
	 * AJAX handler that records a "prayed for" interaction on a prayer request.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function handle_record_prayer(): void {
		$req = Request::capture();

		if ( ! $req->verify_nonce( 'intercessor_record_prayer', 'nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'intercessor' ) ), 403 );
		}

		$requestId = $req->get_int( 'request_id' );

		if ( $requestId === 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'intercessor' ) ), 400 );
		}

		$prayerQuery = new Prayer_Request_Query();
		$request     = $prayerQuery->get_item( $requestId );

		if ( ! $request || ! $request->is_public() || ! $request->is_approved() ) {
			wp_send_json_error( array( 'message' => __( 'Prayer request not found.', 'intercessor' ) ), 404 );
		}

		$userId       = get_current_user_id();
		$anonymousKey = '';

		if ( $userId === 0 ) {
			$anonymousKey = wp_hash( $req->get_remote_addr() . '|' . $req->get_user_agent() );
		}

		$countQuery = new Prayed_Count_Query();
		$recorded   = $countQuery->record_prayer( $requestId, $userId, $anonymousKey );

		if ( ! $recorded ) {
			wp_send_json_error( array( 'message' => __( 'Could not record your prayer.', 'intercessor' ) ), 500 );
		}

		$total = $countQuery->get_total_for_request( $requestId );

		wp_send_json_success( array(
			'message' => __( 'Your prayer has been recorded. Thank you!', 'intercessor' ),
			'total'   => $total,
		) );
	}
}
