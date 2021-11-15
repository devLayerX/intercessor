<?php
/**
 * Intercessor Prayer Submit Form Fields.
 *
 * This template is used to display the prayer request form fields.
 *
 * @package   	Intercessor
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$html          = new \Intercessor\Html();
$form_title	   = intercessor_get_option( 'request_title' );
$form_subtitle = intercessor_get_option( 'request_subtitle' );
$bible_passage = intercessor_get_option( 'bible_passage' );
$show_terms    = intercessor_get_option( 'show_agree_to_terms' );
$default_title = ! empty( $form_title ) ? $form_title : esc_html__( 'New Prayer Request', 'intercessor' );
$enabled       = intercessor_enable_registration();
$required      = intercessor_account_required();
?>

<div class="intercessor" id="intercessor_form_page">
	<h2><?php echo esc_attr( $default_title ); ?></h2>
	<h5><?php echo esc_attr( $form_subtitle ); ?></h5>

    <?php
    /**
     * Fires at the top of the prayer request form page.
     *
     * @since 1.0.0
     */
    do_action( 'intercessor_prayer_request_form_top' );

    $prayer_form->show_errors();
    $prayer_form->show_notices();
    ?>

	<p><em><?php echo esc_attr( $bible_passage ); ?></em></p>

	<form action="<?php echo esc_url( $action ); ?>" method="post" id="submit-prayer-form" class="intercessor-form" enctype="multipart/form-data">

		<?php do_action( 'submit_prayer_form_start' ); ?>

		<div class="intercessor-requester-fields intercessor-row">
			<?php
			foreach ( $requester_fields as $key => $field ) {
				$html->form_fields( $key, $field, $prayer_form->retrieve_value( $key ) );
			}
			?>
		</div>

        <?php if ( intercessor_enable_registration() ) : ?>
            <?php if ( is_user_logged_in() ) : ?>
                <label><?php _e( 'Your account', 'intercessor' ); ?></label>
                <div class="field account-sign-in">
                    <?php
                        $user = wp_get_current_user();
                        printf( __( 'You are currently logged in as <strong>%s</strong>.', 'intercessor' ), $user->user_login );
                    ?>

                    <a class="intercessor-submit" href="<?php echo apply_filters( 'submit_prayers_form_logout_url', wp_logout_url( get_permalink() ) ); ?>">
                        <?php _e( 'Logout', 'intercessor' ); ?>
                    </a>
                </div>

            <?php else : ?>
                <label><?php _e( 'Do you have an account?', 'intercessor' ); ?></label>
                <div class="field account-sign-in">
                    <a class="intercessor-submit" href="<?php echo apply_filters( 'submit_prayers_form_login_url', wp_login_url( get_permalink() ) ); ?>">
                        <?php esc_html_e( 'Sign in', 'intercessor' ); ?>
                    </a>
                </div>

                <div class="intercessor-account-fields">
                    <p class="form-row create-account">
                        <label class="checkbox">
                            <input type="checkbox" class="input-checkbox" id="intercessor_create_account" <?php checked( ( true === $prayer_form->retrieve_value( 'ipr_new_account' ) || ( true === apply_filters( 'intercessor_create_account_default_checked', false ) ) ), true ) ?> name="ipr_new_account" value="1" />
                            <span><?php _e( 'Create an account?', 'intercessor' ); ?></span>
                        </label>
                    </p>
                    <?php if ( $required ) : ?>
                        <?php if ( $account_fields ) : ?>

                            <div class="intercessor-create-account">
                                <?php foreach ( $account_fields as $key => $field ) {
                                    $html->form_fields( $key, $field, $prayer_form->retrieve_value( $key ) );
                                } ?>
                                <div class="clear"></div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php do_action( 'intercessor_before_prayer_registration_form' ); ?>

            <?php endif; ?>
        <?php endif; ?>

		<?php if ( intercessor_can_post_prayer() || intercessor_can_edit_prayer( $prayer_id ) ) : ?>

			<?php do_action( 'submit_prayer_form_prayer_fields_start' ); ?>

			<div class="intercessor-request-fields intercessor-row">
			<?php
				foreach ( $prayer_fields as $key => $field ) {
					$html->form_fields( $key, $field, $prayer_form->retrieve_value( $key ) );
				}
			?>
			</div>

			<?php
            if ( $show_terms ) :
        //    intercessor_get_template( 'terms-field.php' );
            endif;

            /**
             * Fires at the end of prayer form fields.
             *
             * @since 1.0.0
             */
            do_action( 'submit_prayer_form_prayer_fields_end' );
            ?>

			<?php do_action( 'submit_prayer_form_end' ); ?>

			<p>
				<input type="hidden" name="intercessor_form" value="<?php echo $form; ?>" />
				<input type="hidden" name="prayer_id" value="<?php echo esc_attr( $prayer_id ); ?>" />
				<input type="hidden" name="move" value="<?php echo esc_attr( $move ); ?>" />
				<input type="submit" name="intercessor_submit_request" class="button" value="<?php echo esc_attr( $submit_button_text ); ?>" />
				<span class="spinner" style="background-image: url(<?php echo INTERCESSOR_URL . 'assets/images/ajax-loader.gif'; ?>);"></span>
			</p>

		<?php else : ?>

			<?php do_action( 'submit_prayer_form_disabled' ); ?>

		<?php endif; ?>
	</form>
</div>
