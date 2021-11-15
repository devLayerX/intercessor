<?php
/**
 * View Prayer Details
 *
 * @package     IPR
 * @subpackage  Admin/Prayers
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * View Prayer Request Details Page
 *
 * @since 0.9.5
 * @return void
*/

if ( ! isset( $_GET['prayer'] ) || ! is_numeric( $_GET['prayer'] ) ) {
	wp_die(
		esc_html__( 'Something went wrong.', 'intercessor' ),
		esc_html__( 'Error', 'intercessor' ),
		array( 'response' => 400 )
	);
}

// Setup the variables.
$prayer_id      = absint( $_GET['prayer'] );
$prayer         = intercessor_get_item_by( 'prayer', 'id', $prayer_id );
$number         = $prayer->id;
$title			= $prayer->title;
$requester_id   = absint( $prayer->requester_id );
$email		    = intercessor_get_prayer_email( $number );
$requester 	    = new \Intercessor\Requester( $prayer->email );
?>
<div class="wrap intercessor-wrap">
	<h2 id="prayer-details-heading">
		<?php
		printf(
			esc_html__( 'Prayer Request # %s', 'intercessor' ), $number );
		?>
	</h2>
	<?php do_action( 'intercessor_view_prayer_details_before', $number ); ?>
	<h3><?php echo esc_attr( $title ); ?></h3>
	<form id="intercessor-edit-prayer-form" method="post">

		<?php do_action( 'intercessor_view_prayer_details_form_top', $number ); ?>

		<div id="poststuff">
			<div id="intercessor-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php
                            do_action( 'intercessor_view_prayer_details_sidebar_before', $prayer->id );

                            intercessor_get_view_request_sidebar_top( $prayer );

							intercessor_get_view_request_sidebar_bottom( $prayer, $requester );

							intercessor_request_view_details( $prayer );

							do_action( 'intercessor_view_prayer_details_sidebar_after', $number );
							?>

						</div><!-- /#side-sortables -->
					</div><!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

						<?php
						do_action( 'intercessor_view_prayer_details_before_main', $number );

						intercessor_view_prayer_message( $prayer );

						intercessor_view_prayer_requester( $prayer, $requester );

						intercessor_view_prayer_testimony( $prayer );

						intercessor_view_prayer_details_notes( $prayer );

						do_action( 'intercessor_view_prayer_details_after_main', $number );
						?>
						</div><!-- /#normal-sortables -->
					</div><!-- #postbox-container-2 -->


				</div><!-- /#post-body -->
			</div><!-- #intercessor-dashboard-widgets-wrap -->
		</div><!-- /#post-stuff -->
		<?php do_action( 'intercessor_view_prayer_details_form_bottom', $prayer->id ); ?>

		<?php wp_nonce_field( 'intercessor_update_prayer_details_nonce' ); ?>
		<input type="hidden" name="prayer_id" value="<?php echo esc_attr( $prayer->id ); ?>"/>
		<input type="hidden" name="intercessor_action" value="update_prayer_details"/>
	</form>
	<?php do_action( 'intercessor_view_prayer_details_after', $prayer->id ); ?>
</div><!-- /.wrap -->
