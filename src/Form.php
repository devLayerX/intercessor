<?php
/**
 * Intercessor Request Form Class.
 *
 * @package    Intercessor
 * @subpackage Form
 * @copyright  Copyright (c) 2020, Victor Aigbeghian
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      0.9.5
 */

namespace Intercessor;

use intercessor_get_option;
use function intercessor_set_error;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request_Form Class
 *
 * This class handles submission and precessing of prayer requests.
 *
 * @since 0.9.5
 */
class Form {

	/**
	 * Instance of this class.
	 *
	 * @since    0.9.5
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Form action.
	 *
	 * @access protected
	 * @var string
	 */
	protected $action = '';

	/**
	 * Form requester.
	 *
	 * @access public
	 * @var    string
	 */
	public $requester;


	/**
	 * Get things going
	 *
	 * @since 0.9.5
	 */
	public function __construct() {
		add_action( 'intercessor_request_form', [ $this, 'intercessor_show_prayer_form' ] );
		add_action( 'intercessor_request_form_top_title', [ $this, 'intercessor_form_top_title' ] );
		add_action( 'intercessor_request_content', [ $this, 'intercessor_request_fields' ] );
		add_action( 'intercessor_request_form_after_user_info', [ $this, 'intercessor_user_info_fields' ] );
		add_action( 'intercessor_register_fields_before', [ $this, 'intercessor_user_info_fields' ] );
		add_action( 'intercessor_request_form_register_fields', [ $this, 'intercessor_get_register_fields' ] );
		add_action( 'intercessor_request_form_login_fields', [ $this, 'intercessor_get_login_fields' ] );
		add_action( 'intercessor_request_form_before_submit', [ $this, 'captcha' ] );
		add_action( 'intercessor_request_form_after_message', [ $this, 'intercessor_request_submit' ], 9999 );
		add_action( 'intercessor_disable_form', [ $this, 'intercessor_hide_form' ] );
		add_action( 'init', [ $this, 'intercessor_process_prayer_form' ] );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.9.5
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Gets the action (URL for forms to post to).
	 *
	 * @return string
	 */
	public function get_action() {
		$default_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return esc_url_raw( $this->action ? $this->action : $default_url );
	}

	/**
	 * Renders the Prayer Request Form, hooks are provided to add to the prayer form.
	 * The default Prayer Request Form rendered displays a user registration form.
	 *
	 * @access public
	 * @since 0.9.5
	 */
	public function intercessor_show_prayer_form() {
		/**
		 * Hooks in at the top of the prayer form
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_request_form_top' );

		/**
		 * Fires before the register or login fields on the prayer request form.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_request_form_before_register_login' );

		$show_register_form = intercessor_get_option( 'registration_form', 'none' );
		if ( ( 'registration' === $show_register_form
			|| ( 'both' === $show_register_form
			&& ! isset( $_GET['login'] ) ) )
			&& ! is_user_logged_in() ) : ?>
			<div id="intercessor_request_login_register">
				<?php
				/**
				 * Display the register fields on the prayer request form.
				 *
				 * @since 0.9.5
				 */
				do_action( 'intercessor_request_form_register_fields' );
				?>
			</div>
			<?php
		elseif ( ( 'login' === $show_register_form
			|| ( 'both' === $show_register_form
			&& isset( $_GET['login'] ) ) )
			&& ! is_user_logged_in() ) :
			?>
			<div id="intercessor_request_login_register">
				<?php
				/**
				 * Display the login fields on the prayer request form
				 *
				 * @since 0.9.5
				 */
				do_action( 'intercessor_request_form_login_fields' );
				?>
			</div>
			<?php
		endif;

		if ( ( ! isset( $_GET['login'] ) && is_user_logged_in() )
			|| ! isset( $show_register_form )
			|| 'none' === $show_register_form
			|| 'login' === $show_register_form ) {

			/**
			 * Hooks in after the user information fields.
			 *
			 * @since 0.9.5
			 */
			do_action( 'intercessor_request_form_after_user_info' );
		}

		/**
		 * Hooks in at the main prayer request content.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_request_content' );

		/**
		 * Hooks in after the prayer request message.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_request_form_after_message' );

		/**
		 * Hooks in at the bottom of the prayer form.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_request_form_bottom' );
	}

	/**
	 * Format the request form header title.
	 *
	 * @since 0.9.5
	 * @access public
	 *
	 * @return void
	 */
	public function intercessor_form_top_title() {
		$form_title    = intercessor_get_option( 'request_title' );
		$form_subtitle = intercessor_get_option( 'request_subtitle' );
		$bible_passage = intercessor_get_option( 'bible_passage' );
		$main_title    = ! empty( $form_title )
			? $form_title
			: esc_html__( 'New Prayer Request', 'intercessor' );

		echo '<h2>' . $main_title . '</h2>';
		echo '<h6>' . $form_subtitle . '</h6>';
		echo '<em>' . $bible_passage . '</em>';

	}

	/**
	 * Prayer request content fields.
	 *
	 * @since 0.9.5
	 * @access public
	 *
	 * @return void
	 */
	public function intercessor_request_fields() {
		$share_label  = esc_html__( 'Prayer share', 'intercessor' );
		$notify_label = esc_html__( 'Notify me when someone prays.', 'intercessor' );

		\intercessor_get_template(
			'prayer-fields',
			[
				'share_label'  => $share_label,
				'notify_label' => $notify_label,
			]
		);
	}
	/**
	 * Shows the User Info fields in the Personal Info box.
	 *
	 * @since  0.9.5
	 * @access public
	 *
	 * @return void
	 */
	public function intercessor_user_info_fields() {
        $session   = new Session();
		$requester = $session->get( 'requester' );
		$requester = wp_parse_args(
			$requester,
			[
				'first_name' => '',
				'last_name'  => '',
				'email'      => '',
			]
		);

		if ( is_user_logged_in() ) {
			$user_data = get_userdata( get_current_user_id() );

			foreach ( $requester as $key => $field ) {

				if ( 'email' === $key && empty( $field ) ) {
					$requester[ $key ] = $user_data->user_email;
				} elseif ( empty( $field ) ) {
					$requester[ $key ] = $user_data->$key;
				}
			}
		}

		$requester = array_map( 'sanitize_text_field', $requester );
		\intercessor_get_template(
			'requester-fields',
			[
				'requester' => $requester,
			]
		);
	}

	/**
	 * Renders the user registration fields or login form.
	 *
	 *
	 * @since 0.9.5
	 * @access public
	 *
	 * @return  void
	 */
	public function intercessor_get_register_fields() {
		$display_form = intercessor_get_option( 'registration_form', 'none' );

		ob_start();

		\intercessor_get_template(
			'register-fields',
			[
				'display_form' => $display_form,
			]
		);

		echo ob_get_clean();
	}

	/**
	 * Gets the login fields for the login form on the prayer request.
	 *
	 * Hooks on the intercessor_request_form_login_fields to display the login
	 * form if a user alreadyhad an account.
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function intercessor_get_login_fields() {
		$color        = intercessor_get_option( 'prayer_request_color', 'gray' );
		$color        = ( 'inherit' === $color ) ? '' : $color;
		$display_form = intercessor_get_option( 'registration_form', 'none' );

		ob_start();

		\intercessor_get_template(
			'login-fields',
			[
				'display_form' => $display_form,
				'color'        => $color,
			]
		);

		echo ob_get_clean();
	}

	/**
	 * Renders the Google reCaptcha.
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function captcha() {
		$site_key = intercessor_get_option( 'recaptcha_key' );
		$label    = esc_html__( 'Human Verification', 'intercessor' );
		if ( ! is_user_logged_in() ) {
			ob_start();

			intercessor_get_template(
					'captcha-fields',
					[
						'site_key' => $site_key,
						'label'    => $label,
					]
			);

			echo ob_get_clean();
		}
	}

	/**
	 * Renders the Prayer Submit section
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function intercessor_request_submit() {
		?>
		<div id="intercessor_prayer_submit" class="intercessor-rows">
			<?php
			/**
			 * Fires before the prayer form submit.
			 *
			 * @since 0.9.5
			 */
			do_action( 'intercessor_request_form_before_submit' );

			echo $this->get_prayer_request_button_prayer();

			/**
			 * Fires after the prayer form submit.
			 *
			 * @since 0.9.5
			 */
			do_action( 'intercessor_request_form_after_submit' );
			?>

		</div>
		<?php
	}

	/**
	 * Renders the Prayer Request button on the Form.
	 *
	 * @since 0.9.5
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function get_prayer_request_button_prayer() {
		$color      = intercessor_get_option( 'button_font_color', '#fff' );
		$color      = ( 'inherit' === $color ) ? '' : $color;
		$background = intercessor_get_option( 'button_background_color', '#00bfef' );
		$background = ( 'inherit' === $background ) ? '' : $background;
		$border     = intercessor_get_option( 'button_border_color', '#0094d3' );
		$border     = ( 'inherit' === $border ) ? '' : $border;
		$label      = intercessor_get_option( 'submit_prayer_label', esc_html__( 'Submit Prayer Request', 'intercessor' ) );
		?>
		<style type="text/css" media="screen">
			/*<![CDATA[*/
			#intercessor_prayer_button {
				background-color: <?php echo $background; ?>;
				border-color: <?php echo $border; ?>;
				color: <?php echo $color; ?>;
			}

			/*]]>*/
		</style>

		<?php
		ob_start();
		?>

		<p>
			<?php if ( is_user_logged_in() ) { ?>
			<input type="hidden" name="intercessor-user-id" value="<?php echo get_current_user_id(); ?>"/>
			<?php } ?>
			<input type="hidden" name="intercessor_honeypot" value="" />
			<input type="hidden" name="intercessor_action" value="submit_prayer"/>
			<input type="hidden" name="intercessor_request_form_submit" value="prayer_submit" />
			<input type="hidden" name="ipr_prayer_nonce" value="<?php echo wp_create_nonce( 'ipr-prayer-nonce' ); ?>" />
			<input type="submit" class="intercessor-submit" id="intercessor_prayer_button" name="intercessor-prayer" value="<?php echo $label; ?>"/>
		</p>
		<?php
		/**
		 * Action for the intercessor request.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_request' );
		return apply_filters( 'intercessor_request_button_prayer', ob_get_clean() );
	}

	/**
	 * Output the prayer request form.
	 *
	 * @access public
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 * @since  0.9.5
	 *
	 */
	public function output( $atts = [] ) {
		$background  = intercessor_get_option( 'prayer_page_background_color', '#fff' );
		$background  = ( 'inherit' === $background ) ? '' : $background;
		$page_id     = intercessor_get_option( 'form_page' );
		$listing_url = esc_url( get_permalink( $page_id ) );
		$redirect    = ! empty( $atts['redirect'] ) ? $atts['redirect'] : '';

		// redirect added to shortcode.
		if ( $redirect ) {
			if ( 'current' === $redirect ) {
				// redirect to current page
				$redirect = '';
			} else {
				// redirect to the location entered in the shortcode.
				$redirect = $atts['redirect'];
			}
		} else {
			// redirect to the prayer listing page.
			$redirect = $listing_url;
		}
		?>
		<style type="text/css" media="screen">
			/*<![CDATA[*/
			#intercessor_form_page {
				background-color: <?php echo $background; ?>;
			}

			/*]]>*/
		</style>

		<?php
		ob_start();
			echo '<div class="intercessor" id="intercessor_form_page">';

			/**
			 * Action to display the prayer form top.
			 *
			 * @since 0.9.5
			 */
			do_action( 'intercessor_prayer_request_form_top' );

			// Display error notices if present.
			\intercessor_print_errors();
			\intercessor_output_frontend_notices();

			if ( intercessor_can_post_prayer() ) :

			/**
			 * Action to display the prayer form title.
			 *
			 * @since 0.9.5
			 */
			do_action( 'intercessor_request_form_top_title' );
			?>
				<div id="intercessor_request_form_wrap">

				<?php
					/**
					 * Hooks in before the prayer request form
					 *
					 * @since 0.9.5
					 */
					do_action( 'intercessor_before_prayer_form' );
			?>

					<form id="intercessor_request_form" class="intercessor_form" action="<?php echo $redirect; ?>" method="POST">
						<?php

					/**
					 * Hooks into the prayer request form
					 *
					 * @since 0.9.5
					 */
					do_action( 'intercessor_request_form' );

					/**
					 * Hooks into the prayer request form prayer content
					 *
					 * @since 0.9.5
					 */
					do_action( 'intercessor_request_form_request_content' );

					/**
					 * Hooks in at the bottom of the request form
					 *
					 * @since 0.9.5
					 */
					do_action( 'intercessor_request_form_bottom' )
					?>
				</form>
				<?php do_action( 'intercessor_after_prayer_form' ); ?>
			</div><!--end #intercessor_request_form_wrap-->
			<?php
			else :
				/**
				 * Fires when the prayer request form is disabled.
				 *
				 * @since 0.9.5
				 */
				do_action( 'intercessor_disable_form' );
			endif;
			echo '</div>';
			return ob_get_clean();
	}

	/**
	 * Disable the prayer request form
	 *
	 * @since  0.9.5
	 * @return void
	 */
	public function intercessor_hide_form() {

	}

	/**
	 * Process Prayer Request Form
	 *
	 * Handles the prayer form process.
	 *
	 * @access public
	 * @since  0.9.5
	 * @return bool|void
	 */
	public function intercessor_process_prayer_form() {
		// Bailout if no $_POST or nonce fails.
		if ( ! isset( $_POST['ipr_prayer_nonce'] )
			|| ! wp_verify_nonce( $_POST['ipr_prayer_nonce'], 'ipr-prayer-nonce' )
		) {
			return;
		}

		// Bailout if it is spam prayer submission.
		if ( ! empty( $_POST['intercessor_honeypot'] ) ) {
			$spam_msg = esc_html__( 'Nice try honey bear, don\'t touch our honey', 'intercessor' );
			intercessor_set_error( 'spam-prayer', $spam_msg );
		}

		// Check if captcha is enabled and validate the values.
		if ( ! is_user_logged_in() && \intercessor_recaptcha_is_enabled() ) {
			$data = [
				'g-recaptcha-response' => isset( $_POST['g-recaptcha-response'] ) ? esc_attr( $_POST['g-recaptcha-response'] ) : '',
				'g-recaptcha-remoteip' => isset( $_POST['g-recaptcha-remoteip'] ) ? esc_attr( $_POST['g-recaptcha-remoteip'] ) : '',
			];

			if ( ! \intercessor_is_valid_recaptcha_response( $data ) ) {
				$message = intercessor_get_option( 'captcha_message' );
				intercessor_set_error( 'failed-captcha', $message );
			}
		}

		// Bailout if user has submitted a similar prayer request before.
		$title = isset( $_POST['intercessor_title'] ) ? \intercessor_clean( $_POST['intercessor_title'] ) : '';
		$email = isset( $_POST['intercessor_email'] ) ? \intercessor_clean( $_POST['intercessor_email'] ) : '';
		if ( \intercessor_is_multiple_request( $email, $title ) ) {
			$multiple_prayer = esc_html__( 'Sorry, your prayer request could not be successfully processed because it seems like you have submitted a similar prayer request recently. Please, try submitting a new request or visit the prayer history page if you would like to edit your former prayer request.', 'intercessor' );

		//	intercessor_set_error( 'multiple_prayer_request', $multiple_prayer );
			intercessor_display_frontend_notice( $multiple_prayer, true, 'error', false );
			return false;
		}

		// Bailout if user is submitting with a banned email.
		$posted['intercessor_email'] = $email;
		if ( intercessor_check_prayer_email( $posted ) ) {
			intercessor_set_error( 'is_banned_email', esc_html__( 'Please contact the site administrator to submit your prayer request', 'intercessor' ) );
		}

		/**
		 * Hooks in before processing of the submitted request form.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_pre_process_prayer' );

		// Validate the form $_POST data.
		$valid_data = \intercessor_request_form_validate_fields();

		/**
		 * Hooks in to check for errors of the request form.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_request_error_checks', $valid_data, $_POST );

		$is_ajax = isset( $_POST['intercessor_ajax'] );

		// Process the login form.
		if ( isset( $_POST['intercessor_login_submit'] ) ) {
			\intercessor_process_prayer_login();
		}

		// Validate the user.
		$user = \intercessor_get_prayer_form_user( $valid_data );

		/*
		 *	Validate fields after user is logged in
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_request_user_error_checks', $user, $valid_data, $_POST );

		if ( false === $valid_data || \intercessor_get_errors() || ! $user ) {
			if ( $is_ajax ) {
				do_action( 'intercessor_ajax_prayer_request_errors' );
				\intercessor_die();
			} else {
				return false;
			}
		}

		if ( $is_ajax ) {
			echo 'success';
			\intercessor_die();
		}

		// Setup user information
		$user_info = [
			'id'         => $user['user_id'],
			'email'      => $user['user_email'],
			'first_name' => $user['user_first'],
			'last_name'  => $user['user_last'],
		];

		// Update a requester record if they have added/updated information.
		$requester = new Requester( $user_info['email'] );

		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
		if ( empty( $requester->name ) || $name !== $requester->name ) {
			$update_data = [
				'name' => $name,
			];

			// Update the requester's name and update the user record too.
			$requester->update( $update_data );
			wp_update_user(
				[
					'ID'         => get_current_user_id(),
					'first_name' => $user_info['first_name'],
					'last_name'  => $user_info['last_name'],
				]
			);
		}

		// Set up the unique prayer key.
		$auth_key   = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$prayer_key = strtolower( md5( $user['user_email'] . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'ipr', true ) ) );
        $activated  = date( 'Y-m-d H:i:s', time() );

		// Setup the status.
		if ( \intercessor_hold_prayers() ) {
			$status = 'pending';
		} elseif ( 'personal' === $valid_data['share'] ) {
			$status    = 'personal';
		} else {
			$status    = 'active';
		}

		// Data to register new user.
		$user_data = [
			'user_first'   => isset( $_POST['intercessor_first'] ) ? sanitize_text_field( $_POST['intercessor_first'] ) : '',
			'user_last'    => isset( $_POST['intercessor_last'] ) ? sanitize_text_field( $_POST['intercessor_last'] ) : '',
			'user_login'   => isset( $_POST['intercessor_user_login'] ) ? trim( $_POST['intercessor_user_login'] ) : false,
			'user_email'   => isset( $_POST['intercessor_email'] ) ? trim( $_POST['intercessor_email'] ) : false,
			'user_pass'    => isset( $_POST['intercessor_user_pass'] ) ? trim( $_POST['intercessor_user_pass'] ) : false,
			'pass_confirm' => isset( $_POST['intercessor_user_pass_confirm'] ) ? trim( $_POST['intercessor_user_pass_confirm'] ) : false,
		];

		// Get the user ID.
		if ( is_user_logged_in() ) {
			$intercessor_user_id = get_current_user_id();
		} else {
			if ( \intercessor_enable_registration() && ! empty( $_POST['intercessor_create_account'] ) ) {
				$intercessor_user_id = \intercessor_create_new_user( $user_data );
			} else {
				$intercessor_user_id = '0';
			}
		}

		// Setup prayer information.
		$prayer_data = [
			'user_id'    => $intercessor_user_id,
			'first_name' => $user['user_first'],
			'last_name'  => $user['user_last'],
			'user_email' => $user['user_email'],
			'title'      => $valid_data['prayer_title'],
			'message'    => $valid_data['prayer_message'],
			'status'     => $status,
			'prayer_key' => $prayer_key,
			'user_info'  => stripslashes_deep( $user_info ),
			'share'      => $valid_data['share'],
			'notify'     => $valid_data['notify'],
			'created'    => $activated,
		];

		// Terms of service.
		if ( ! empty( $valid_data['terms'] ) ) {
			$prayer_data['terms'] = $valid_data['terms'];
		}

		// Privacy policy.
		$privacy    = intercessor_get_option( 'show_privacy_policy' );
		$submission = intercessor_get_option( 'show_on_submission' );
		if ( $privacy && $submission ) {
			$prayer_data['privacy'] = $valid_data['privacy'];
		}

		// Add the user data for hooks.
		$valid_data['user'] = $user;

		/**
		 * Allow themes and plugins to hook before the submission.
		 *
		 * @param string $_POST The  $_POST value.
		 * @param array  $user_info  User information array.
		 * @param array  $valid_data The validated data.
		 *
		 * @since 0.9.5
		 */
		do_action( 'intercessor_request_before_submit', $_POST, $user_info, $valid_data );

		// Allow the prayer data to be modified before it is sent to the submit.
		$prayer_data = apply_filters(
			'intercessor_prayer_data_before_submit',
			$prayer_data,
			$valid_data
		);

		// Setup the data we're storing in the prayer session.
		$session_data = $prayer_data;

		\intercessor_set_prayer_session( $session_data );

		// Insert the prayer request.
		\intercessor_insert_prayer( $prayer_data );
	}
}
