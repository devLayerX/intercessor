<?php
/**
 * Front-end template: Prayer Form block.
 *
 * Variables provided by Prayer_Form_Block::render():
 *
 * @var bool   $show_anon           Whether to show the anonymous checkbox.
 * @var string $submitLabel         Localised submit button label.
 * @var bool   $recaptchaEnabled    Whether reCAPTCHA is active for this form.
 * @var string $recaptchaWidgetHtml HTML for the v2 checkbox widget (may be empty).
 * @var string $recaptchaTokenInput HTML hidden input for v3 token (may be empty).
 *
 * @package Intercessor
 * @since   1.0.0
 */

declare(strict_types=1);

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\Admin\Settings;

// ── Account confirmation status notices ──────────────────────────────────────
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$intercessor_account_status = isset( $_GET['intercessor_account'] ) ? sanitize_key( $_GET['intercessor_account'] ) : '';

// Pre-fill name and email for logged-in users.
$current_user  = wp_get_current_user();
$intercessor_is_logged_in  = is_user_logged_in();
$intercessor_first_name_value = $intercessor_is_logged_in ? $current_user->first_name : '';
$intercessor_last_name_value  = $intercessor_is_logged_in ? $current_user->last_name  : '';
$intercessor_email_value      = $intercessor_is_logged_in ? $current_user->user_email  : '';

// Terms and privacy settings — correct keys matching Display_Page::get_schema().
$intercessor_show_terms   = (bool) Settings::get( 'show_site_terms', false );
$intercessor_terms_label  = (string) Settings::get( 'terms_label', '' );
$intercessor_terms_url    = (string) Settings::get( 'terms_url', '' );
$intercessor_show_privacy = (bool) Settings::get( 'show_privacy_policy', false );
$intercessor_privacy_label = (string) Settings::get( 'privacy_label', '' );
$intercessor_privacy_url  = (string) Settings::get( 'privacy_url', '' );
?>
<div class="intercessor-prayer-form wp-block-intercessor-prayer-form" data-intercessor-form>

	<?php if ( $intercessor_account_status === 'confirmed' ) : ?>
		<div class="intercessor-alert intercessor-alert--success">
			<?php esc_html_e( 'Your email has been confirmed. Your account is now active.', 'intercessor' ); ?>
		</div>
	<?php elseif ( $intercessor_account_status === 'expired' ) : ?>
		<div class="intercessor-alert intercessor-alert--error">
			<?php esc_html_e( 'This confirmation link has expired. Please submit a new prayer request and create your account again.', 'intercessor' ); ?>
		</div>
	<?php elseif ( $intercessor_account_status === 'invalid' ) : ?>
		<div class="intercessor-alert intercessor-alert--error">
			<?php esc_html_e( 'This confirmation link is invalid. Please check your email or try again.', 'intercessor' ); ?>
		</div>
	<?php endif; ?>

	<div class="intercessor-form-messages" aria-live="polite"></div>

	<form class="intercessor-form" novalidate>

		<div class="intercessor-field-row">
			<div class="intercessor-field">
				<label for="intercessor-first-name">
					<?php esc_html_e( 'First Name', 'intercessor' ); ?>
					<span aria-hidden="true">*</span>
				</label>
				<input type="text" id="intercessor-first-name" name="first_name"
					   required autocomplete="given-name" maxlength="100"
					   value="<?php echo esc_attr( $intercessor_first_name_value ); ?>"
					   <?php echo $intercessor_is_logged_in ? 'readonly' : ''; ?>>
			</div>

			<div class="intercessor-field">
				<label for="intercessor-last-name">
					<?php esc_html_e( 'Last Name', 'intercessor' ); ?>
				</label>
				<input type="text" id="intercessor-last-name" name="last_name"
					   autocomplete="family-name" maxlength="100"
					   value="<?php echo esc_attr( $intercessor_last_name_value ); ?>"
					   <?php echo $intercessor_is_logged_in ? 'readonly' : ''; ?>>
			</div>
		</div>

		<div class="intercessor-field">
			<label for="intercessor-email">
				<?php esc_html_e( 'Email Address', 'intercessor' ); ?>
				<span aria-hidden="true">*</span>
			</label>
			<input type="email" id="intercessor-email" name="requester_email"
				   required autocomplete="email" maxlength="255"
				   value="<?php echo esc_attr( $intercessor_email_value ); ?>"
				   <?php echo $intercessor_is_logged_in ? 'readonly' : ''; ?>>
		</div>

		<div class="intercessor-field">
			<label for="intercessor-subject">
				<?php esc_html_e( 'Subject', 'intercessor' ); ?>
				<span aria-hidden="true">*</span>
			</label>
			<input type="text" id="intercessor-subject" name="subject"
				   required maxlength="255">
		</div>

		<div class="intercessor-field">
			<label for="intercessor-content">
				<?php esc_html_e( 'Prayer Request', 'intercessor' ); ?>
				<span aria-hidden="true">*</span>
			</label>
			<textarea id="intercessor-content" name="content" rows="6" required></textarea>
		</div>

		<?php if ( $show_anon ) : ?>
		<div class="intercessor-field intercessor-field--checkbox">
			<label>
				<input type="checkbox" name="is_anonymous" value="1">
				<?php esc_html_e( 'Keep my name anonymous on the public list', 'intercessor' ); ?>
			</label>
		</div>
		<?php endif; ?>

		<?php
		/**
		 * "Create an account?" section — only for guests when enable_registration
		 * is on. The visibility of username/password fields is toggled by JS
		 * reading window.intercessorForm.generateUsername / generatePassword.
		 * The fieldset is always rendered when enableRegistration is true so
		 * it works without JS (fields shown by default, hidden progressively).
		 */
		$intercessor_enable_registration = ! $intercessor_is_logged_in && (bool) \Intercessor\Admin\Settings::get( 'enable_registration', false );
		$intercessor_generate_username   = (bool) \Intercessor\Admin\Settings::get( 'generate_username', false );
		$intercessor_generate_password   = (bool) \Intercessor\Admin\Settings::get( 'generate_password', false );
		$intercessor_need_username       = ! $intercessor_generate_username;
		$intercessor_need_password       = ! $intercessor_generate_password;
		?>

		<?php if ( $intercessor_enable_registration ) : ?>
		<div class="intercessor-field intercessor-field--checkbox intercessor-field--register-toggle">
			<label>
				<input type="checkbox" name="create_account" value="1"
				       id="intercessor-create-account"
				       aria-controls="intercessor-register-fields"
				       aria-expanded="false">
				<?php esc_html_e( 'Create an account?', 'intercessor' ); ?>
			</label>
		</div>

		<div id="intercessor-register-fields"
		     class="intercessor-register-fields"
		     aria-hidden="true"
		     hidden>

			<?php if ( $intercessor_need_username ) : ?>
			<div class="intercessor-field">
				<label for="intercessor-username">
					<?php esc_html_e( 'Username', 'intercessor' ); ?>
					<span aria-hidden="true">*</span>
				</label>
				<input type="text" id="intercessor-username" name="username"
				       autocomplete="username" maxlength="60"
				       placeholder="<?php esc_attr_e( 'Choose a username', 'intercessor' ); ?>">
			</div>
			<?php endif; ?>

			<?php if ( $intercessor_need_password ) : ?>
			<div class="intercessor-field-row">
				<div class="intercessor-field">
					<label for="intercessor-password">
						<?php esc_html_e( 'Password', 'intercessor' ); ?>
						<span aria-hidden="true">*</span>
					</label>
					<input type="password" id="intercessor-password"
					       name="account_password"
					       autocomplete="new-password"
					       minlength="8"
					       placeholder="<?php esc_attr_e( 'Min. 8 characters', 'intercessor' ); ?>">
				</div>
				<div class="intercessor-field">
					<label for="intercessor-password-confirm">
						<?php esc_html_e( 'Confirm Password', 'intercessor' ); ?>
						<span aria-hidden="true">*</span>
					</label>
					<input type="password" id="intercessor-password-confirm"
					       name="account_password_confirm"
					       autocomplete="new-password"
					       minlength="8"
					       placeholder="<?php esc_attr_e( 'Repeat password', 'intercessor' ); ?>">
				</div>
			</div>
			<?php endif; ?>

			<p class="intercessor-register-hint">
				<span class="dashicons dashicons-info" aria-hidden="true"></span>
				<?php esc_html_e( 'After submitting, you will receive a confirmation email to activate your account.', 'intercessor' ); ?>
			</p>

		</div><!-- #intercessor-register-fields -->
		<?php endif; ?>

		<?php if ( $intercessor_show_terms && ! empty( $intercessor_terms_url ) ) : ?>
		<div class="intercessor-field intercessor-field--checkbox">
			<label>
				<input type="checkbox" name="accept_terms" value="1" required>
				<?php echo wp_kses_post( $intercessor_terms_label ?: __( 'I agree to the Terms of Service.', 'intercessor' ) ); ?>
				<a href="<?php echo esc_url( $intercessor_terms_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Read Terms', 'intercessor' ); ?>
				</a>
			</label>
		</div>
		<?php elseif ( $intercessor_show_terms ) : ?>
		<div class="intercessor-field intercessor-field--checkbox">
			<label>
				<input type="checkbox" name="accept_terms" value="1" required>
				<?php echo wp_kses_post( $intercessor_terms_label ?: __( 'I agree to the Terms of Service.', 'intercessor' ) ); ?>
			</label>
		</div>
		<?php endif; ?>

		<?php if ( $intercessor_show_privacy && ! empty( $intercessor_privacy_url ) ) : ?>
		<div class="intercessor-field intercessor-field--checkbox">
			<label>
				<input type="checkbox" name="accept_privacy" value="1" required>
				<?php
				// translators: %s: requester display name used in terms acceptance label
				printf(
					/* translators: %1$s: URL to the privacy policy page. */
					wp_kses_post( __( 'I have read and accept the <a href="%1$s" target="_blank" rel="noopener noreferrer">Privacy Policy</a>.', 'intercessor' ) ),
					esc_url( $intercessor_privacy_url )
				);
				?>
			</label>
		</div>
		<?php elseif ( $intercessor_show_privacy ) : ?>
		<div class="intercessor-field intercessor-field--checkbox">
			<label>
				<input type="checkbox" name="accept_privacy" value="1" required>
				<?php echo wp_kses_post( $intercessor_privacy_label ?: __( 'I have read and accept the Privacy Policy.', 'intercessor' ) ); ?>
			</label>
		</div>
		<?php endif; ?>

		<?php
		// reCAPTCHA — only shown to guests; logged-in users are already verified.
		// Recaptcha::widget_html() / token_input_html() produce plugin-controlled
		// HTML with all dynamic values escaped — safe to output directly.
		if ( ! $intercessor_is_logged_in ) :
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $recaptchaWidgetHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped HTML returned by Recaptcha::widget_html()
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $recaptchaTokenInput; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped HTML returned by Recaptcha::token_input_html()
		endif;
		?>

		<?php wp_nonce_field( 'intercessor_submit_request', 'nonce' ); ?>
		<input type="hidden" name="source_url" value="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>">

		<div class="intercessor-field">
			<button type="submit" class="intercessor-submit wp-element-button">
				<?php echo esc_html( $submitLabel ); ?>
			</button>
		</div>

	</form>

</div>

<script>
( function () {
	'use strict';

	const wrap   = document.querySelector( '[data-intercessor-form]' );
	if ( ! wrap ) return;

	const form   = wrap.querySelector( '.intercessor-form' );
	const msgs   = wrap.querySelector( '.intercessor-form-messages' );
	const config = window.intercessorForm      || {};
	const rc     = window.intercessorRecaptcha || {};

	// ── Registration fieldset toggle ──────────────────────────────────────
	// Declared at outer scope so the submit handler's success branch can also
	// collapse the panel after a successful submission.
	const createChk = form ? form.querySelector( '#intercessor-create-account' ) : null;
	const regFields = form ? form.querySelector( '#intercessor-register-fields' ) : null;

	if ( createChk && regFields ) {
		const usernameInput  = regFields.querySelector( '[name="username"]' );
		const pwInput        = regFields.querySelector( '[name="account_password"]' );
		const pwConfirmInput = regFields.querySelector( '[name="account_password_confirm"]' );

		function toggleRegFields( open ) {
			regFields.hidden = ! open;
			regFields.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
			createChk.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

			[ usernameInput, pwInput, pwConfirmInput ].forEach( function ( el ) {
				if ( el ) el.required = open;
			} );
		}

		toggleRegFields( false );

		createChk.addEventListener( 'change', function () {
			toggleRegFields( this.checked );
			if ( this.checked && usernameInput ) usernameInput.focus();
		} );
	}

	function showMessage( text, type ) {
		msgs.innerHTML = '<div class="intercessor-alert intercessor-alert--' + type + '">' +
			text.replace( /</g, '&lt;' ) + '</div>';
	}

	/**
	 * Obtain a reCAPTCHA v3 token asynchronously.
	 * Returns a Promise that resolves to the token string.
	 */
	function getV3Token() {
		return new Promise( function ( resolve ) {
			if ( typeof grecaptcha === 'undefined' || typeof grecaptcha.execute === 'undefined' ) {
				resolve( '' );
				return;
			}
			grecaptcha.ready( function () {
				grecaptcha.execute( rc.siteKey || '', { action: rc.action || 'submit' } )
					.then( resolve )
					.catch( function () { resolve( '' ); } );
			} );
		} );
	}

	form.addEventListener( 'submit', async function ( e ) {
		e.preventDefault();

		const btn = form.querySelector( '.intercessor-submit' );
		btn.disabled = true;
		msgs.innerHTML = '';

		const data = new FormData( form );
		data.append( 'action', config.action || 'intercessor_submit_request' );
		data.append( 'nonce',  config.nonce  || '' );

		// Inject reCAPTCHA v3 token before submitting.
		if ( config.recaptchaActive && config.recaptchaV === 'v3' ) {
			const token = await getV3Token();
			data.set( 'g-recaptcha-response', token );
		}

		try {
			const res  = await fetch( config.ajaxUrl || '/wp-admin/admin-ajax.php', {
				method : 'POST',
				body   : data,
			} );
			const json = await res.json();

			if ( json.success ) {
				form.reset();
				// Collapse the registration panel after reset.
				if ( createChk && regFields ) {
					createChk.checked = false;
					createChk.dispatchEvent( new Event( 'change' ) );
				}
				// Reset reCAPTCHA v2 widget so it can be used again.
				if ( config.recaptchaActive && config.recaptchaV === 'v2' &&
					 typeof grecaptcha !== 'undefined' ) {
					grecaptcha.reset();
				}
				showMessage( json.data.message, 'success' );
			} else {
				showMessage(
					json.data.message || <?php echo wp_json_encode( __( 'An error occurred.', 'intercessor' ) ); ?>,
					'error'
				);
			}
		} catch ( err ) {
			showMessage( <?php echo wp_json_encode( __( 'Network error. Please try again.', 'intercessor' ) ); ?>, 'error' );
		} finally {
			btn.disabled = false;
		}
	} );
} () );
</script>
