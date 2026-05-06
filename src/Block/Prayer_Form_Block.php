<?php
/**
 * Prayer Form block render callback.
 *
 * @package Intercessor
 * @since   1.0.0
 */
declare(strict_types=1);

namespace Intercessor\Block;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


use Intercessor\Admin\Settings;
use Intercessor\Util\Recaptcha;

/**
 * Server-side render callback for the intercessor/prayer-form Gutenberg block.
 *
 * Renders the front-end submission form including:
 *   - Optional reCAPTCHA v2 widget or v3 hidden token input.
 *   - AJAX configuration output as an inline <script> tag directly before
 *     the form markup, guaranteeing window.intercessorForm is always defined
 *     when the submit handler runs regardless of script enqueue state.
 *   - reCAPTCHA script enqueued when enabled for the form.
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class Prayer_Form_Block {

	/**
	 * Return the default block attribute definitions.
	 *
	 * @since  1.0.0
	 * @return array<string, array<string, mixed>>
	 */
	public static function default_attributes(): array {
		return array(
			'showAnonymousOption' => array( 'type' => 'boolean', 'default' => true ),
			'submitLabel'         => array( 'type' => 'string',  'default' => '' ),
			'successMessage'      => array( 'type' => 'string',  'default' => '' ),
		);
	}

	/**
	 * Render the prayer request submission form on the front end.
	 *
	 * @since  1.0.0
	 * @param  array<string, mixed> $attributes Block attributes from the block editor.
	 * @param  string               $content    Inner block content (unused).
	 * @return string                           Rendered HTML string.
	 */
	public function render( array $attributes, string $content ): string {
		$allow_anonymous = Settings::get( 'allow_anonymous', true );
		$show_anon       = $allow_anonymous && ( $attributes['showAnonymousOption'] ?? true );
		$submitLabel     = ! empty( $attributes['submitLabel'] )
			? esc_html( $attributes['submitLabel'] )
			: esc_html__( 'Submit Prayer Request', 'intercessor' );

		$requireLogin = Settings::get( 'require_login', false );

		if ( $requireLogin && ! is_user_logged_in() ) {
			// translators: %s: site name shown in reCAPTCHA disclaimer
			return sprintf(
				'<p class="intercessor-login-required">%s</p>',
				esc_html__( 'Please log in to submit a prayer request.', 'intercessor' )
			);
		}

		// Enqueue reCAPTCHA script + config when enabled for the prayer form.
		$recaptchaEnabled = Recaptcha::is_enabled_for_form();
		if ( $recaptchaEnabled ) {
			Recaptcha::enqueue( 'intercessor_submit' );
		}

		// Registration settings — controls whether "Create an account?" UI appears.
		$enable_registration = (bool) Settings::get( 'enable_registration', false );
		$generate_username   = (bool) Settings::get( 'generate_username',   false );
		$generate_password   = (bool) Settings::get( 'generate_password',   false );

		// Build the JS config object that the inline form script reads from
		// window.intercessorForm. We output it as a direct <script> tag rather
		// than relying on wp_localize_script(), which silently no-ops when the
		// target script handle has not been enqueued on the current page — the
		// cause of the "Security check failed" error on form submission.
		$formConfig = wp_json_encode( array(
			'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
			'nonce'              => wp_create_nonce( 'intercessor_submit' ),
			'action'             => 'intercessor_submit_request',
			'recaptchaActive'    => $recaptchaEnabled,
			'recaptchaV'         => $recaptchaEnabled ? Recaptcha::get_version() : '',
			'enableRegistration' => $enable_registration && ! is_user_logged_in(),
			'generateUsername'   => $generate_username,
			'generatePassword'   => $generate_password,
		) );

		// Pass template variables.
		$recaptchaWidgetHtml = $recaptchaEnabled ? Recaptcha::widget_html()     : '';
		$recaptchaTokenInput = $recaptchaEnabled ? Recaptcha::token_input_html() : '';

		ob_start();
		// Inline the config before the template so window.intercessorForm is
		// always defined when the form's submit listener runs.
		echo '<script>window.intercessorForm = ' . $formConfig . ';</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		require INTERCESSOR_DIR . 'templates/blocks/prayer-form.php';
		return ob_get_clean() ?: '';
	}
}
