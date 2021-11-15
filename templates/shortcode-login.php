<?php
/**
 * This template is used to display the login form with [intercessor_login]
 */
global $intercessor_login_redirect;
if ( ! is_user_logged_in() ) :

	// Show any error messages after form submission
	intercessor_print_errors(); ?>
	<form id="intercessor_login_form" class="intercessor_form" action="" method="post">
		<fieldset>
			<legend><?php _e( 'Log into Your Account', 'intercessor' ); ?></legend>
			<?php do_action( 'intercessor_login_fields_before' ); ?>
			<p class="intercessor-login-username">
				<label for="intercessor_user_login"><?php _e( 'Username or Email', 'intercessor' ); ?></label>
				<input name="intercessor_user_login" id="intercessor_user_login" class="intercessor-required intercessor-input" type="text"/>
			</p>
			<p class="intercessor-login-password">
				<label for="intercessor_user_pass"><?php _e( 'Password', 'intercessor' ); ?></label>
				<input name="intercessor_user_pass" id="intercessor_user_pass" class="intercessor-password intercessor-required intercessor-input" type="password"/>
			</p>
			<p class="intercessor-login-remember">
				<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember Me', 'intercessor' ); ?></label>
			</p>
			<p class="intercessor-login-submit">
				<input type="hidden" name="intercessor_redirect" value="<?php echo esc_url( $intercessor_login_redirect ); ?>"/>
				<input type="hidden" name="intercessor_login_nonce" value="<?php echo wp_create_nonce( 'intercessor-login-nonce' ); ?>"/>
				<input type="hidden" name="intercessor_action" value="user_login"/>
				<input id="intercessor_login_submit" type="submit" class="intercessor-submit" value="<?php _e( 'Log In', 'intercessor' ); ?>"/>
			</p>
			<p class="intercessor-lost-password">
				<a href="<?php echo wp_lostpassword_url(); ?>">
					<?php _e( 'Lost Password?', 'intercessor' ); ?>
				</a>
			</p>
			<?php do_action( 'intercessor_login_fields_after' ); ?>
		</fieldset>
	</form>
<?php else : ?>

	<?php do_action( 'intercessor_login_form_logged_in' ); ?>

<?php endif; ?>
