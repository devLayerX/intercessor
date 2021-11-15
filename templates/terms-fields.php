<?php
/**
 * Intercessor Prayer Submit Form Agree to Terms Fields.
 *
 * This template is used to display the terms fields.
 *
 * @package   	Intercessor
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

	<div class="col form-column">
		<div id="intercessor_terms" class="intercessor-terms" style="display:none;">
			<?php
			do_action( 'intercessor_before_terms' );
			echo wpautop( stripslashes( $agree_text ) );
			do_action( 'intercessor_after_terms' );
			?>
		</div>
		<div id="intercessor_show_terms" class="intercessor-show-terms">
			<a href="#" class="intercessor_terms_links"><?php esc_html_e( 'Show Terms', 'intercessor' ); ?></a>
			<a href="#" class="intercessor_terms_links" style="display:none;"><?php esc_html_e( 'Hide Terms', 'intercessor' ); ?></a>
		</div>

		<?php if ( '1' !== intercessor_get_option( 'show_privacy_policy', false ) && '1' === intercessor_get_option( 'show_on_submission', false ) ) : ?>
			<?php
			$privacy_page = get_option( 'wp_page_for_privacy_policy' );
			if ( ! empty( $privacy_page ) ) {
				$privacy_text = get_post_field( 'post_content', $privacy_page );
				if ( ! empty( $privacy_text  ) ) {
					?>
					<div id="intercessor-privacy-policy" class="intercessor-terms" style="display:none;">
						<?php
						do_action( 'intercessor_before_privacy_policy' );
						echo wpautop( do_shortcode( stripslashes( $privacy_text ) ) );
						do_action( 'intercessor_after_privacy_policy' );
						?>
					</div>
					<div id="intercessor-show-privacy-policy" class="intercessor-show-terms">
						<a href="#" class="intercessor_terms_links"><?php _e( 'Show Privacy Policy', 'intercessor' ); ?></a>
						<a href="#" class="intercessor_terms_links" style="display:none;"><?php _e( 'Hide Privacy Policy', 'intercessor' ); ?></a>
					</div>
					<?php
				}
			}
		endif ?>
		<div id="intercessor-terms-agreement">
			<input name="intercessor_agree_to_terms" required="required" type="checkbox" id="intercessor_agree_to_terms" value="1"/>
			<label for="intercessor_agree_to_terms"><?php echo stripslashes( $agree_label ); ?></label>
		</div>
	</div>