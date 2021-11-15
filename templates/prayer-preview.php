<?php
/**
 * Intercessor Prayer Preview.
 *
 * This template is used to display the prayer request preview during submission.
 *
 * @package   	Intercessor
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<form method="post" id="prayer_preview" action="<?php echo esc_url( $form->get_action() ); ?>">
	<div class="prayer_preview_title">
		<input type="submit" name="continue" id="prayer_preview_submit_button" class="button intercessor-submit" value="<?php echo apply_filters( 'submit_prayer_move_preview_submit_text', __( 'Submit Prayer', 'intercessor' ) ); ?>" />
		<input type="submit" name="edit_prayer" class="button intercessor-submit" value="<?php _e( 'Edit Prayer', 'intercessor' ); ?>" />
		<h2><?php _e( 'Preview You Prayer Request', 'intercessor' ); ?></h2>
	</div>
	<div class="prayer_preview single_prayer">
		<h1><?php intercessor_process_item( 'prayer', 'get', $prayer_id, false ); ?></h1>

		<?php intercessor_get_template_part( 'content-single', 'prayer' ); ?>

		<input type="hidden" name="prayer_id" value="<?php echo esc_attr( $form->get_prayer_id() ); ?>" />
		<input type="hidden" name="move" value="<?php echo esc_attr( $form->get_move() ); ?>" />
		<input type="hidden" name="intercessor_form" value="<?php echo $form->get_form_name(); ?>" />
	</div>
</form>