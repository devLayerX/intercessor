<?php
/**
 * Intercessor Prayer Submit Form Login Fields.
 *
 * This template is used to display the login fields.
 *
 * @package   	Intercessor
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<fieldset id="intercessor_login_fields">
	<?php if ( $display_form === 'both' ) : ?>
		<p id="intercessor-new-account-wrap">
			<?php _e( 'Create an account?', 'intercessor' ); ?>
			<a href="<?php echo esc_url( remove_query_arg( 'login' ) ); ?>" class="intercessor_request_register_login <?php echo $style; ?>" data-action="prayer_request_register">
				<?php _e( 'Register', 'intercessor' ); 
				if ( ! intercessor_account_required()) { 
					echo ' ' . __( 'or submit prayer request as a guest.', 'intercessor' ); 
				} ?>
			</a>
		</p>
	<?php endif; ?>
	<?php do_action( 'intercessor_request_login_fields_before' ); ?>
	<p id="intercessor-user-login-wrap">
		<label class="intercessor-label" for="intercessor-username">
			<?php _e( 'Username or Email', 'intercessor' ); ?>
			<?php if ( intercessor_account_required() ) : ?>
			<span class="intercessor-required-indicator">*</span>
			<?php endif; ?>
		</label>
		<input class="<?php if ( intercessor_account_required() ) { echo 'required '; } ?>intercessor-input" type="text" name="intercessor_user_login" id="intercessor_user_login" value="" placeholder="<?php _e( 'Your username or email address', 'intercessor' ); ?>"/>
	</p>
	<p id="intercessor-user-pass-wrap" class="intercessor_login_password">
		<label class="intercessor-label" for="intercessor-password">
			<?php _e( 'Password', 'intercessor' ); ?>
			<?php if ( intercessor_account_required() ) : ?>
			<span class="intercessor-required-indicator">*</span>
			<?php endif; ?>
		</label>
		<input class="<?php if ( intercessor_account_required() ) { echo 'required '; } ?>intercessor-input" type="password" name="intercessor_user_pass" id="intercessor_user_pass" placeholder="<?php _e( 'Your password', 'intercessor' ); ?>"/>
		<?php if ( intercessor_account_required() ) : ?>
			<input type="hidden" name="intercessor-request-var" value="needs-to-login"/>
		<?php endif; ?>
	</p>
	<p id="intercessor-user-login-submit">
		<input type="submit" class="intercessor-submit button" name="intercessor_login_submit" value="<?php _e( 'Login', 'intercessor' ); ?>"/>
	</p>
	<?php do_action( 'intercessor_request_login_fields_after' ); ?>
</fieldset>