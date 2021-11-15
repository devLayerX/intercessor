<?php
/**
 * Intercessor Prayer Submit Form Agree to Privacy Policy Fields.
 *
 * This template is used to display the privacy policy agreement fields.
 *
 * @package   	Intercessor
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<div id="intercessor-privacy-policy-agreement" class="intercessor-row">
	<div class="col form-column">
		<?php if ( $show_on ) : ?>

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

		<?php endif; ?>

		<div class="intercessor-privacy-policy-agreement">
			<input name="intercessor_agree_to_privacy_policy" required="required" type="checkbox" id="intercessor-agree-to-privacy-policy" value="1"/>
			<label for="intercessor-agree-to-privacy-policy"><?php echo stripslashes( $privacy_label ); ?></label>
		</div>
	</div>
</div>