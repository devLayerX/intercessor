<?php
/**
 * Shortcode Register template
 *
 * This template is used to display the registration form with [intercessor_register].
 *
 * @package     Intercessor
 * @since       0.9.5
 * @subpackage  Templates/Shortcode_Register
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0php GNU Public License
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $intercessor_register_redirect;

intercessor_print_errors();
$redirect = intercessor_get_current_page_url();
?>

<?php if ( ! is_user_logged_in() ) : ?>

<form id="intercessor_register_form" class="intercessor_form" action="" method="post">
	<?php do_action( 'intercessor_register_form_fields_top' ); ?>
	<h3><?php esc_html_e( 'Do you have an account?', 'intercessor' ); ?></h3>
	<div class="field account-sign-in">
		<a class="button intercessor-submit" href="<?php echo esc_url( apply_filters( 'intercessor_prayer_history_login_url', wp_login_url( get_permalink() ) ) ); ?>">
			<?php esc_html_e( 'Sign in', 'intercessor' ); ?>
		</a>
	</div>

	<?php
	if ( intercessor_enable_registration() ) :
	?>
	<h3><?php esc_html_e( 'Register New Account', 'intercessor' ); ?></h3>
	<div class="intercessor-row">

		<?php do_action( 'intercessor_register_form_fields_before' ); ?>

		<div class="col form-col6">
			<label for="intercessor-user-login"><?php esc_html_e( 'Username', 'intercessor' ); ?></label>
			<input id="intercessor-user-login" required="required" class="intercessor-input" type="text" name="intercessor_history_login" placeholder="<?php esc_html_e( 'Username', 'intercessor' ); ?>"/>
		</div>

		<div class="col form-col6">
			<label for="intercessor-user-email"><?php esc_html_e( 'Email', 'intercessor' ); ?></label>
			<input id="intercessor-user-email" required="required" class="intercessor-input" type="email" name="intercessor_history_email" placeholder="<?php esc_html_e( 'Email', 'intercessor' ); ?>" />
		</div>

		<div class="col form-col6">
			<label for="intercessor-user-pass"><?php esc_html_e( 'Password', 'intercessor' ); ?></label>
			<input id="intercessor-user-pass" required="required" class="password intercessor-input" type="password" name="intercessor_history_pass" placeholder="<?php esc_html_e( 'Password', 'intercessor' ); ?>" />
			<span class="intercessor-desc"><?php echo esc_attr( intercessor_get_password_hint() ) ?></span>
		</div>

		<div class="col form-col6">
			<label for="intercessor-user-pass2"><?php esc_html_e( 'Confirm Password', 'intercessor' ); ?></label>
			<input id="intercessor-user-pass2" required="required" class="password intercessor-input" type="password" name="intercessor_history_pass2" placeholder="<?php esc_html_e( 'Confirm Password', 'intercessor' ); ?>" />
			<span class="intercessor-desc"><?php echo esc_attr( intercessor_get_password2_hint() ) ?></span>
		</div>

		<?php
		$site_key = intercessor_get_option( 'recaptcha_key' );
		$label    = intercessor_get_option( 'captcha_label', esc_html__( 'Human Verifications', 'intercessor' ) );
		if ( ! empty( $site_key ) ) :
			?>
			<div class="col form-column">
				<script src="https://www.google.com/recaptcha/api.js" async defer></script>
				<label for="intercessor-captcha">
					<?php echo esc_attr( $label ); ?>
				</label>
				<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>

					<p>
						<input type="hidden" name="g-recaptcha-remoteip" value=<?php echo esc_attr( intercessor_get_ip() ); ?> />
					</p>
			</div>
		<?php
		endif;
		?>

		<?php do_action( 'intercessor_register_form_fields_before_submit' ); ?>
	</div>
	<p>
		<input type="hidden" name="intercessor_history_honeypot" value="" />
		<input type="hidden" name="intercessor_action" value="history_user_register" />
		<input type="hidden" name="intercessor_history_redirect" value="<?php echo esc_url( $redirect ); ?>"/>
		<input type="hidden" name="intercessor_history_register_nonce" value="<?php echo wp_create_nonce( 'intercessor-history-register-nonce' ); ?>" />
		<input class="intercessor-submit" name="intercessor_register_submit" type="submit" value="<?php esc_html_e( 'Register', 'intercessor' ); ?>" />
	</p>

	<?php do_action( 'intercessor_register_form_fields_after' ); ?>


	<?php do_action( 'intercessor_register_form_fields_bottom' ); ?>
</form>
<?php
endif;
?>

<?php else : ?>

	<?php do_action( 'intercessor_register_form_logged_in' ); ?>

<?php endif; ?>
