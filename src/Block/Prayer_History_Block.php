<?php
/**
 * Prayer History block render callback.
 *
 * @package Intercessor
 * @since   1.0.0
 */
declare(strict_types=1);

namespace Intercessor\Block;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


use Intercessor\Database\Query\Prayer_History_Query;
use Intercessor\Database\Query\Prayer_Request_Query;
use Intercessor\Http\Request;
use Intercessor\Util\Recaptcha;

/**
 * Server-side render callback for the intercessor/prayer-history Gutenberg block.
 *
 * When reCAPTCHA is enabled for the history page, the Google script is
 * enqueued so any supplementary JS on the history page (e.g. a "Pray for this"
 * button) can use the same reCAPTCHA session.
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class Prayer_History_Block {

	/**
	 * Return the default block attribute definitions.
	 *
	 * @since  1.0.0
	 * @return array<string, array<string, mixed>>
	 */
	public static function default_attributes(): array {
		return array(
			'requestId'     => array( 'type' => 'integer', 'default' => 0 ),
			'showNotes'     => array( 'type' => 'boolean', 'default' => true ),
			'showModerator' => array( 'type' => 'boolean', 'default' => false ),
		);
	}

	/**
	 * Render the prayer request status-change timeline on the front end.
	 *
	 * @since  1.0.0
	 * @param  array<string, mixed> $attributes Block attributes from the block editor.
	 * @param  string               $content    Inner block content (unused).
	 * @return string                           Rendered HTML string.
	 */
	public function render( array $attributes, string $content ): string {
		$requestId = absint( $attributes['requestId'] ?? 0 );
		if ( $requestId === 0 ) {
			$requestId = Request::capture()->get_int( 'prayer_request' );
		}

		if ( $requestId === 0 ) {
			// translators: %s: login URL
			return sprintf(
				'<p class="intercessor-no-request">%s</p>',
				esc_html__( 'No prayer request specified.', 'intercessor' )
			);
		}

		$requestQuery = new Prayer_Request_Query();
		$request      = $requestQuery->get_item( $requestId );

		if ( ! $request || ( ! $request->is_public() && ! current_user_can( 'edit_prayers' ) ) ) {
			// translators: %s: login URL
			return sprintf(
				'<p class="intercessor-not-found">%s</p>',
				esc_html__( 'Prayer request not found.', 'intercessor' )
			);
		}

		// If the site requires login to view prayer history and the user is not
		// logged in, show a login prompt instead of the timeline.
		$require_login = (bool) \Intercessor\Admin\Settings::get( 'require_login', false );

		if ( $require_login && ! is_user_logged_in() ) {
			$login_url = wp_login_url( get_permalink() );

			return sprintf(
				'<div class="intercessor-login-prompt">' .
					'<p class="intercessor-login-prompt__message">%s</p>' .
					'<a href="%s" class="intercessor-login-prompt__button wp-element-button">%s</a>' .
				'</div>',
				esc_html__( 'Please log in to view prayer requests.', 'intercessor' ),
				esc_url( $login_url ),
				esc_html__( 'Log In', 'intercessor' )
			);
		}

		// Enqueue reCAPTCHA on the history page when enabled, so any front-end
		// interaction widgets (e.g. a "Pray for this" button) can use it.
		if ( Recaptcha::is_enabled_for_history() ) {
			Recaptcha::enqueue( 'intercessor_history' );
		}

		$historyQuery = new Prayer_History_Query();
		$history      = $historyQuery->get_for_request( $requestId );

		$showNotes     = (bool) ( $attributes['showNotes']     ?? true );
		$showModerator = (bool) ( $attributes['showModerator'] ?? false ) && current_user_can( 'edit_prayers' );

		ob_start();
		require INTERCESSOR_DIR . 'templates/blocks/prayer-history.php';
		return ob_get_clean() ?: '';
	}
}
