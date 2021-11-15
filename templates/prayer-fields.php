<?php
/**
 * Intercessor Prayer Submit Form Request Fields.
 *
 * This template is used to display the prayer content fields.
 *
 * @package   	Intercessor
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$share_label  = esc_html__( 'Prayer share', 'intercessor' );
$notify_label = esc_html__( 'Notify me when someone prays.', 'intercessor' );

?>

<div id="intercessor_request_content" class="intercessor-row">
	<div class="col form-col6">
		<label class="intercessor-label" for="intercessor-title">
			<?php esc_html_e( 'Prayer Title', 'intercessor' ); ?>
			<?php if( intercessor_request_form_required_fields( 'intercessor_title' ) ) { ?>
				<span class="intercessor-required-indicator">*</span>
			<?php } ?>
		</label>
		<input class="intercessor-input required" type="text" name="intercessor_title" placeholder="<?php esc_html_e( 'Prayer Request Title', 'intercessor' ); ?>" id="intercessor-title" aria-describedby="intercessor-title-description"<?php if( intercessor_request_form_required_fields( 'intercessor_title' ) ) {  echo ' required '; } ?>/>
	</div>
	
	<div class="col form-col6">
		<label for="intercessor_share">
			<?php echo stripslashes( $share_label ); ?>
			<span class="intercessor-required-indicator">*</span>
		</label>
		<select name="intercessor_share" id="intercessor_share" data-nonce="<?php echo wp_create_nonce( 'intercessor-shared-field-nonce' ); ?>" class="prayer_share intercessor-select<?php if ( intercessor_request_form_required_fields( 'prayer_share' ) ) { echo ' required'; } ?>"<?php if ( intercessor_request_form_required_fields( 'prayer_share' ) ) {  echo ' required '; } ?>>
			<option value="0" selected="selected" disabled="disabled">
				<?php esc_html_e( 'Please choose an option', 'intercessor' ); ?>
			</option>
			<option data-type="freely" value="freely">
				<?php esc_html_e( 'Share freely', 'intercessor' ); ?>
			</option>
			<option data-type="anon" value="anon">
				<?php esc_html_e( 'Share anonymously', 'intercessor' ); ?>
			</option>
			<option data-type="personal" value="personal">
				<?php esc_html_e( 'Do not share - private prayer', 'intercessor' ); ?>
			</option>			
		</select>
		<span class="intercessor-description" id="intercessor-email-description">
			<?php esc_html_e( 'Choose how we share your prayer request.', 'intercessor' ); ?>
		</span>
	</div>
	
	<div id="intercessor-prayer-message-wrap" class="col form-column">
		<label class="intercessor-label" for="intercessor-message">
			<?php esc_html_e( 'Prayer Request', 'intercessor' ); ?>
			<?php if( intercessor_request_form_required_fields( 'intercessor_message' ) ) { ?>
				<span class="intercessor-required-indicator">*</span>
			<?php } ?>
		</label>
		<textarea cols="20" rows="4"  class="intercessor-input" required="required" name="intercessor_message" placeholder="<?php esc_html_e( 'Prayer Request Message', 'intercessor' ); ?>" id="intercessor-message" aria-describedby="intercessor-message-description"<?php if( intercessor_request_form_required_fields( 'intercessor_message' ) ) {  echo ' required '; } ?>></textarea>
	</div>
	<div class="col form-column">
		<input name="intercessor_notify" type="checkbox" id="intercessor_notify" value="1"/>
		<label for="intercessor_notify"><?php echo stripslashes( $notify_label ); ?></label>
	</div>
	<?php
	if ( intercessor_get_option( 'show_agree_to_terms', false ) ) :
		$agree_text  = intercessor_get_option( 'agree_text', '' );
		$agree_label = intercessor_get_option( 'agree_label', esc_html__( 'Agree to Terms?', 'intercessor' ) );		
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

		<div id="intercessor-terms-agreement">
			<input name="intercessor_agree_to_terms" required="required" type="checkbox" id="intercessor_agree_to_terms" value="1"/>
			<label for="intercessor_agree_to_terms"><?php echo stripslashes( $agree_label ); ?></label>
		</div>
	</div>
	<?php
	endif;
	
	if ( '1' === intercessor_get_option( 'show_privacy_policy', false ) ) :
		$privacy_label = intercessor_get_option( 'agree_privacy_label', esc_html__( 'Agree to Privacy Policy?', 'intercessor' ) );
		$privacy_page  = get_option( 'wp_page_for_privacy_policy' );
		$privacy_text  = get_post_field( 'post_content', $privacy_page );
		$show_on 	   = intercessor_get_option( 'show_on_submission', false );
	?>
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
	<?php
	endif;
	?>
</div>