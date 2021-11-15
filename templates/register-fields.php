<?php
/**
 * Intercessor Prayer Submit Form Register Fields.
 *
 * This template is used to display the register fields.
 *
 * @package   	Intercessor
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<?php do_action( 'intercessor_register_fields_before' ); ?>

<?php if ( 'both' === $display_form ) : ?>
	<div id="intercessor-login-account-wrap" class="col form-column">
		<?php esc_html_e( 'Do you have an account?', 'intercessor' ); ?> 
		<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" 
			class="intercessor_request_register_login intercessor-submit" 
			data-action="prayer_request_login">
			<?php esc_html_e( 'Login', 'intercessor' ); ?>
		</a>
	</div>
<?php endif; ?>

<div class="intercessor-row">
	<div class="intercessor-create-account col form-column">
		<input name="intercessor_create_account" type="checkbox" id="intercessor_create_account" value="1"/>
		<label for="intercessor_create_account">
			<?php
			esc_html_e( 'Create an account', 'intercessor' );
			if ( ! intercessor_account_required() ) {
				echo ' ' . esc_html__( '(optional)', 'intercessor' );
			}
			?>
		</label>
		<span class="intercessor-description" id="intercessor-email-description">
			<?php esc_html_e( 'Having an account allows you to edit or delete your prayer request afterwards.', 'intercessor' ); ?>
		</span>
	</div>
</div>

<div id="intercessor_register_fields" class="intercessor-row">

	<?php do_action( 'intercessor_register_account_fields_before' ); ?>
	<div class="col form-col4" id="intercessor_username">
		<label for="intercessor_user_login">
			<?php esc_html_e( 'Username', 'intercessor' ); ?>
			<?php if ( intercessor_account_required() ) : ?>
			<span class="intercessor-required-indicator">*</span>
			<?php endif; ?>
		</label>
		<input name="intercessor_user_login" id="intercessor_user_login" 
			class="<?php if ( intercessor_account_required() ) {
				echo 'required '; } ?>intercessor-input" type="text" placeholder="<?php esc_html_e( 'Username', 'intercessor' ); ?>"/>

		<span class="intercessor-description">
			<?php esc_html_e( 'Account username.', 'intercessor' ); ?>
		</span>
	</div>
	<div class="col form-col4" id="intercessor_password">
		<label for="intercessor_user_pass">
			<?php esc_html_e( 'Password', 'intercessor' ); ?>
			<?php if ( intercessor_account_required() ) : ?>
			<span class="intercessor-required-indicator">*</span>
			<?php endif; ?>
		</label>
		<input name="intercessor_user_pass" id="intercessor_user_pass" class="<?php if ( intercessor_account_required() ) { echo 'required '; } ?>intercessor-input" placeholder="<?php esc_html_e( 'Password', 'intercessor' ); ?>" type="password"/>			
		<span class="intercessor-description">
			<?php esc_html_e( 'Account password.', 'intercessor' ); ?>
		</span>
	</div>
	<div class="col form-col4" id="intercessor_password2">
		<label for="intercessor_user_pass_confirm">
			<?php esc_html_e( 'Verify Password', 'intercessor' ); ?>
			<?php if ( intercessor_account_required() ) : ?>
			<span class="intercessor-required-indicator">*</span>
			<?php endif; ?>
		</label>
		<input name="intercessor_user_pass_confirm" id="intercessor_user_pass_confirm" class="<?php if ( intercessor_account_required() ) { echo 'required '; } ?>intercessor-input" placeholder="<?php esc_html_e( 'Password', 'intercessor' ); ?>" type="password"/>				
		<span class="intercessor-description">
			<?php esc_html_e( 'Verify account password.', 'intercessor' ); ?>
		</span>
	</div>
	<?php do_action( 'intercessor_register_account_fields_after' ); ?>

	<?php do_action( 'intercessor_register_fields_after' ); ?>

	<input type="hidden" name="intercessor-request-var" value="needs-to-register"/>

	<?php do_action( 'intercessor_request_form_user_info' ); ?>
	<?php do_action( 'intercessor_request_form_user_register_fields' ); ?>

</div>
