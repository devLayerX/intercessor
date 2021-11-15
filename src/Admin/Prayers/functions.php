<?php
/**
 * Prayer Functions
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
 * Retrieves the view prayer request sidebar top.
 *
 * @since 0.9.5
 *
 * @param object $prayer The prayer object.
 */
function intercessor_get_view_request_sidebar_top( $prayer ) {
	$prayer_date = strtotime( $prayer->date_created );

	$status_help  = '<ul>';
	$status_help .= '<li>' . __( '<strong>Pending</strong>: prayer is pending admin activation.', 'intercessor' ) . '</li>';
	$status_help .= '<li>' . __( '<strong>Active</strong>: prayer request is active.', 'intercessor' ) . '</li>';
	$status_help .= '<li>' . __( '<strong>Archived</strong>: this prayer request is archived.', 'intercessor' ) . '</li>';
	$status_help .= '<li>' . __( '<strong>Private</strong>: this prayer request is private. only you and the requester can have access to it.', 'intercessor' ) . '</li>';
	$status_help .= '</ul>';
	?>

	<div id="intercessor-request-update" class="postbox intercessor-request-data">

		<h3 class="hndle">
			<span><?php esc_html_e( 'Update Prayer Request', 'intercessor' ); ?></span>
		</h3>

		<div class="inside">
			<div class="intercessor-admin-box">

				<?php do_action( 'intercessor_view_prayer_details_before_status', $prayer->id ); ?>

				<div class="intercessor-admin-box-inside">
					<p>
						<label for="prayer_status"><?php esc_html_e( 'Status:', 'intercessor' ); ?>&nbsp;
							&nbsp;
							<select name="intercessor-request-status" class="intercessor-select-chosen">
								<?php foreach ( intercessor_prayer_statuses() as $key => $status ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"
								<?php selected( $prayer->status, $key, true );
								?>><?php echo esc_html( $status ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<span alt="f223" class="intercessor-help-tip dashicons dashicons-editor-help" title="<?php echo $status_help; ?>"></span>
					</p>

				</div>

				<div class="intercessor-admin-box-inside">
					<p>
						<label><?php esc_html_e( 'Date:', 'intercessor' ); ?>
							<input type="text" name="intercessor-request-date"
							       value="<?php echo esc_attr( date( 'm/d/Y', $prayer_date ) ); ?>"
							       class="medium-text intercessor_datepicker"/>
						</label>
					</p>
				</div>

				<div class="intercessor-admin-box-inside">
					<p>
                        <label><?php esc_html_e( 'Time:', 'intercessor' ); ?>
						<input type="text" maxlength="2" name="intercessor-request-time-hour" value="<?php echo esc_attr( date_i18n( 'H', $prayer_date ) ); ?>" class="small-text intercessor-request-time-hour"/>&nbsp;:&nbsp;
                        <input type="text" maxlength="2" name="intercessor-request-time-min" value="<?php echo esc_attr( date( 'i', $prayer_date ) ); ?>" class="small-text intercessor-request-time-min"/>
                        </label>
					</p>
				</div>

				<?php do_action( 'intercessor_view_prayer_details_update_inner', $prayer->id ); ?>

			</div><!-- /.intercessor-admin-box -->

		</div><!-- /.inside -->

		<div class="intercessor-request-update-box intercessor-admin-box">
			<?php do_action( 'intercessor_view_prayer_details_before_update', $prayer->id ); ?>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<a href="<?php echo wp_nonce_url(
						add_query_arg(
							array(
								'intercessor-action' => 'delete_prayer',
								'prayer_id'          => $prayer->id ),
							admin_url( 'admin.php?page=intercessor-prayers' ) ), 'intercessor_prayer_nonce' ) ?>" class="intercessor-delete-prayer intercessor-delete"><?php esc_html_e( 'Delete Prayer', 'intercessor' ); ?>
					</a>
				</div>
				<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Prayer', 'intercessor' ); ?>"/>
				<div class="clear"></div>
			</div>
			<?php do_action( 'intercessor_view_prayer_details_update_after', $prayer->id ); ?>
		</div><!-- /.intercessor-request-update-box -->

	</div><!-- /#intercessor-request-data -->
	<?php
}

/**
 * Retrieves the view prayer request sidebar bottom.
 *
 * @since 0.9.5
 *
 * @param object $prayer    The prayer request
 * @param object $requester The prayer requester object.
 */
function intercessor_get_view_request_sidebar_bottom( $prayer, $requester ) {

	if ( '1' === $prayer->notify ) :
	?>
	<div id="intercessor-request-resend-notification" class="postbox intercessor-request-data">
		<div class="inside">
			<div class="intercessor-request-resend-notification-box intercessor-admin-box">
				<?php do_action( 'intercessor_view_prayer_details_resend_notification_before', $prayer->id ); ?>
				<a href="<?php echo esc_url(
					add_query_arg(
						array(
							'intercessor-action' => 'email_links',
							'prayer_id'          => $prayer->id,
						)
					) ); ?>" id="<?php if ( ! empty( $requester->emails ) && count( (array) $requester->emails ) > 1 ) {
						echo 'intercessor-select-notification-email';
					} else { echo 'intercessor-resend-notification';
					} ?>" class="button-secondary"><?php esc_html_e( 'Resend Notification', 'intercessor' ); ?>
                </a>
				<span alt="f223" class="intercessor-help-tip dashicons dashicons-editor-help" title="<?php esc_html_e( '<strong>Resend Notification</strong>: This will send a new copy of the prayer notification to the requester&#8217;s email address.', 'intercessor' ); ?>"></span>
				<?php if ( ! empty( $requester->emails ) && count( (array) $requester->emails ) > 1 ) : ?>
					<div class="clear"></div>
					<div class="intercessor-request-resend-notification-addresses" style="display:none;">
						<select class="intercessor-request-resend-notification-email">
							<option value=""><?php esc_html_e( ' -- select email --', 'intercessor' ); ?></option>
							<?php foreach ( $requester->emails as $email ) : ?>
								<option value="<?php echo urlencode( sanitize_email( $email ) ); ?>"><?php echo $email; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
				<div class="clear"></div>
				<?php do_action( 'intercessor_view_prayer_details_resend_notification_after', $prayer->id ); ?>
			</div><!-- /.intercessor-request-resend-notification-box -->
		</div>
	</div>

	<?php
	endif;

	if ( intercessor_is_active_prayer( $prayer->id ) ) : ?>
	<div id="intercessor_pray_for_request" class="postbox intercessor-request-data">
		<div class="inside">
			<div class="intercessor-request-box intercessor-admin-box">
				<?php do_action( 'intercessor_view_prayer_details_before_pray_for', $prayer->id ); ?>
				<a href="<?php echo esc_url( add_query_arg( array(
					'intercessor-action' => 'uplift_prayer',
					'prayer_id'       => $prayer->id
					) ) ); ?>" id="" class="button-primary intercessor-icon-praying"><?php esc_html_e( '  Pray for Request', 'intercessor' ); ?></a>
				<span alt="f223" class="intercessor-help-tip dashicons dashicons-editor-help" title="<?php esc_html_e( '<strong>Pray for Request</strong>: Click this button whenever you pray for the prayer request to increase the prayed for counts.', 'intercessor' ); ?>">
				</span>
				<div class="clear"></div>
				<?php do_action( 'intercessor_view_prayer_details_after_pray_for', $prayer->id ); ?>
			</div><!-- /.intercessor-request-resend-notification-box -->
		</div>
	</div>

	<?php
	if ( ! intercessor_is_answered_prayer( $prayer->id ) ) :
	?>
		<div id="intercessor_answered_prayer_request" class="postbox intercessor-request-data">
			<div class="inside">
				<div class="intercessor-request-box intercessor-admin-box">
					<?php do_action( 'intercessor_view_prayer_details_before_answered', $prayer->id ); ?>
					<a href="<?php echo esc_url( add_query_arg( array(
						'intercessor-action' => 'answered_prayer',
						'prayer_id'          => $prayer->id
						) ) ); ?>" id="" class="button-secondary intercessor-icon-checkmark"><?php esc_html_e( '  Answered Prayer?', 'intercessor' ); ?></a>
					<span alt="f223" class="intercessor-help-tip dashicons dashicons-editor-help" title="<?php esc_html_e( '<strong>Answered Prayer</strong>: Click this button to mark this prayer request as answered.', 'intercessor' ); ?>">
					</span>
					<div class="clear"></div>
					<?php do_action( 'intercessor_view_prayer_details_after_answered', $prayer->id ); ?>
				</div><!-- /.intercessor-request-resend-notification-box -->
			</div>
		</div>
	<?php endif; ?>

	<?php endif;
}

/**
 * Retrieves the view prayer details.
 *
 * @since 0.9.5
 *
 * @param object $prayer The prayer request
 */
function intercessor_request_view_details( $prayer ) {

	$personal_text = esc_html__( 'This prayer request is marked personal by the requester. It will not be displayed on the prayer list page of your site.', 'intercessor' );
	$notify_text   = esc_html__( 'The requester wants to be notified anytime this request is prayed for.', 'intercessor' );
	$prayed_args   = [
		'date_created_query' => false,
		'prayer_id'          => $prayer->id,
	];
	$prayed_text   = intercessor_count_prayed( $prayed_args );

	if ( $prayer->anon ) {
		$anonymous = esc_html__( 'Yes', 'intercessor' );
	} else {
		$anonymous = esc_html__( 'No', 'intercessor' );
	}
	?>
	<div id="intercessor-request-details" class="postbox intercessor-request-data">

		<h3 class="hndle">
			<span><?php esc_html_e( 'Prayer Metadata', 'intercessor' ); ?></span>
		</h3>
		<div class="inside">
			<div class="intercessor-admin-box">

				<?php do_action( 'intercessor_view_prayer_details_prayer_meta_before', $prayer->id ); ?>

				<?php if ( $prayer->id ) : ?>
				<div class="intercessor-request-id intercessor-admin-box-inside">
					<p>
						<span class="label"><?php esc_html_e( 'Prayer Request ID:', 'intercessor' ); ?></span>&nbsp;
						<span><?php echo apply_filters( 'intercessor_prayer_details_prayer_id_' . $prayer->id, $prayer->id ); ?></span>
					</p>
				</div>
				<?php endif; ?>

				<div class="intercessor-request-prayer-key intercessor-admin-box-inside">
					<p>
						<span class="label intercessor-icon-key"><?php esc_html_e( ' Prayer Key:', 'intercessor' ); ?></span>&nbsp;
						<span><?php echo $prayer->prayer_key; ?></span>
					</p>
				</div>

				<?php if ( 'personal' === $prayer->share ) : ?>
				<div class="intercessor-request-anon intercessor-admin-box-inside">
					<p>
						<span class="label private-prayer intercessor-icon-warning1"><strong><?php esc_html_e( ' Private Prayer:', 'intercessor' ); ?></strong></span>&nbsp;
						<span><?php echo esc_attr( $personal_text ); ?></span>
					</p>
				</div>
				<?php endif; ?>

				<div class="intercessor-request-anon intercessor-admin-box-inside">
					<p>
						<span class="label intercessor-icon-locked"><?php esc_html_e( ' Anonymous Prayer:', 'intercessor' ); ?></span>&nbsp;
						<span><?php echo esc_attr( $anonymous ); ?></span>
					</p>
				</div>

				<?php if ( '1' === $prayer->notify ) : ?>
				<div class="intercessor-request-anon intercessor-admin-box-inside">
					<p>
						<span class="label intercessor-icon-heart"><?php esc_html_e( ' Notify:', 'intercessor' ); ?></span>&nbsp;
						<span><?php echo esc_attr( $notify_text ); ?></span>
					</p>
				</div>
				<div class="intercessor-request-notify intercessor-admin-box-inside">
					<p>
						<span class="label intercessor-icon-heart"><?php esc_html_e( ' Prayed For:', 'intercessor' ); ?></span>&nbsp;
						<span><?php echo esc_attr( $prayed_text ); ?><?php esc_html_e( ' times', 'intercessor' ); ?></span>
					</p>
				</div>
				<?php endif; ?>

				<?php do_action( 'intercessor_view_prayer_details_prayer_meta_after', $prayer->id ); ?>

			</div><!-- /.column-container -->

		</div><!-- /.inside -->

	</div><!-- /#intercessor-request-data -->
	<?php
}

/**
 * Prayer request message view column
 *
 * @since 0.9.5
 *
 * @param object $prayer Prayer object.
 */
function intercessor_view_prayer_message( $prayer ) {
	$message = esc_html( $prayer->message );
	if ( $message ) : ?>
	<div id="intercessor-request-request-details" class="postbox">
		<h3 class="hndle"><?php esc_html_e( 'Prayer Request Details', 'intercessor' ); ?></h3>

		<div class="inside">
			<div class="column-container">
				<div class="column">
					<p>
						<?php echo apply_filters( 'intercessor_request_details_message', $message ); ?>
					</p>

					<p>
						<strong><?php esc_html_e( 'Edit Prayer Request', 'intercessor' ); ?></strong><br>
						<?php

							printf(
								'<a class="button-secondary" href="%1$s" target="_blank">Edit Prayer #%2$s</a>',
								admin_url( 'admin.php?page=intercessor-prayers&intercessor-action=edit_prayer&prayer=' . $prayer->id ),
								$prayer->id
							);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>
	<?php endif;
}

/**
 * Output the prayer requester column on the view prayer details screen.
 *
 * @since 0.9.5
 *
 * @param object $prayer    Prayer request object.
 * @param object $requester Requester object.
 */
function intercessor_view_prayer_requester( $prayer, $requester ) {
    wp_enqueue_script( 'intercessor-admin-prayers' );

	/**
	 * Fires in the view prayer details before the requester section.
	 *
	 * @param int $id Prayer ID.
     *
     * @since 1.0.0
	 */
	do_action( 'intercessor_view_prayer_details_requester_before', $prayer->id );

	?>

	<div id="intercessor-requester-details" class="postbox">
		<h3 class="hndle">
			<span><?php esc_html_e( 'Requester Details', 'intercessor' ); ?></span>
		</h3>
		<div class="inside intercessor-clearfix">

			<div class="column-container requester-info">
				<div class="column">
					<?php if ( ! empty( $requester->id ) ) : ?>
						<?php $requester_url = admin_url( 'admin.php?page=intercessor-requesters&view=overview&id=' . esc_attr( $requester->id ) ); ?>
						<a href="<?php echo $requester_url; ?>"><?php echo esc_attr( $requester->name ); ?> - <?php echo esc_attr( $requester->email ); ?></a>
					<?php endif; ?>
					<input type="hidden" name="intercessor-current-requester" value="<?php echo $requester->id; ?>" />
				</div>
				<div class="column">
					<a href="#change" class="intercessor-prayer-change-requester"><?php esc_html_e( 'Assign to another requester', 'intercessor' ); ?></a>
					&nbsp;|&nbsp;
					<a href="#new" class="intercessor-prayer-new-requester"><?php esc_html_e( 'New Requester', 'intercessor' ); ?></a>
				</div>
			</div>

            <div class="column-container change-requester" style="display: none">
				<div class="column">
					<strong><?php esc_html_e( 'Select a requester', 'intercessor' ); ?>:</strong>
					<?php
						$args = array(
							'class'       => 'intercessor-prayer-change-requester-input',
							'selected'    => $prayer->requester_id,
							'name'        => 'requester-id',
							'placeholder' => esc_html__( 'Type to search all Requesters', 'intercessor' ),
						);

						$html = new \Intercessor\Html();
						echo $html->requester_dropdown( $args );
					?>
				</div>
				<div class="column"></div>
				<div class="column">
					<strong><?php esc_html_e( 'Actions', 'intercessor' ); ?>:</strong>
					<br />
					<input type="hidden" id="intercessor-change-requester" name="intercessor-change-requester" value="0" />
					<a href="#cancel" class="intercessor-prayer-change-requester-cancel intercessor-delete"><?php esc_html_e( 'Cancel', 'intercessor' ); ?></a>
				</div>
				<div class="column">
					<small><em>*<?php esc_html_e( 'Click "Save Prayer" to change the requester', 'intercessor' ); ?></em></small>
				</div>
			</div>

			<div class="column-container new-requester" style="display: none">
				<div class="column">
					<strong><?php esc_html_e( 'Name', 'intercessor' ); ?>:</strong>&nbsp;
					<input type="text" name="intercessor-new-requester-name" value="" class="medium-text"/>
				</div>
				<div class="column">
					<strong><?php esc_html_e( 'Email', 'intercessor' ); ?>:</strong>&nbsp;
					<input type="email" name="intercessor-new-requester-email" value="" class="medium-text"/>
				</div>
				<div class="column">
					<strong><?php esc_html_e( 'Actions', 'intercessor' ); ?>:</strong>
					<br />
					<input type="hidden" id="intercessor-new-requester" name="intercessor-new-requester" value="0" />
					<a href="#cancel" class="intercessor-prayer-new-requester-cancel intercessor-delete"><?php esc_html_e( 'Cancel', 'intercessor' ); ?></a>
				</div>
				<div class="column">
					<small><em>*<?php esc_html_e( 'Click "Save Prayer" to create new requester', 'intercessor' ); ?></em></small>
				</div>
			</div>

			<?php do_action( 'intercessor_prayer_view_requester_details', $prayer->id ); ?>

		</div><!-- /.inside -->
	</div><!-- /#intercessor-requester-details -->
	<?php
	do_action( 'intercessor_view_prayer_details_requester_after', $prayer->id );
}


/**
 * Get the HTML used to add a praise to an object ID and type
 *
 * @param int $prayer_id Prayer ID.
 *
 * @since 0.9.5
 *
 * @return string
 */
function intercessor_admin_get_new_praise_form( $prayer_id = 0 ) {

	// Start a buffer.
	ob_start();
	?>

	<div class="intercessor-add-praise">
		<textarea name="intercessor-praise" id="intercessor-praise"></textarea>

		<p>
			<a id="" class="button button-secondary left" href="<?php echo esc_url(
					add_query_arg(
						array(
							'intercessor-action' => 'add_praise',
							'prayer_id'          => $prayer_id,
						)
					)
				);
				?>">
				<?php esc_html_e( 'Add Praise Report', 'intercessor' ); ?>
			</a>
			<input type="hidden" name="prayer_id" value="<?php echo $prayer_id; ?>" />
			<?php echo wp_nonce_field( 'intercessor_praise_nonce', 'intercessor_praise_nonce' ); ?>
		</p>
	</div>
	<div class="clear"></div>

	<?php
	// Return the current buffer.
	return ob_get_clean();
}


/**
 * Add praise report if the prayer request has been marked as answered.
 *
 * @since 0.9.5
 *
 * @param object $prayer Prayer request object
 */
function intercessor_view_prayer_testimony( $prayer ) {
	$answered = intercessor_is_answered_prayer( $prayer->id );
	$praise   = intercessor_get_item_meta( 'prayer', $prayer->id, 'praise_report', false );
	if ( $answered ) : ?>
		<div class="intercessor-notes" id="intercessor-praise-<?php echo esc_attr( $prayer->id ); ?>">
			<h3 class="hndle">
				<span>
					<?php esc_html_e( 'Praise Report', 'intercessor' ); ?>
				</span>
			</h3>

			<?php
			if ( ! empty( $praise ) ) :

				ob_start(); ?>

				<div id="intercessor-new-praise-<?php echo esc_attr( $prayer->id ); ?>">
					<div>
						<p><?php echo make_clickable( $praise ); ?></p>
					</div>
				</div>

				<?php

				// Return the current buffer
				return ob_get_clean();

			else :

				echo intercessor_admin_get_new_praise_form( $prayer->id );

			endif; ?>
		</div>
	<?php
	endif;
}

/**
 * Output the prayer notes on the view prayer details screen.
 *
 * @since 0.9.5
 *
 * @param object $prayer Prayer request object
 */
function intercessor_view_prayer_details_notes( $prayer ) {
	// Setup note arguments.
	$notes_args = [
		'object_id'   => $prayer->id,
		'object_type' => 'prayer',
		'order'       => 'ASC',
	];
	$notes      = intercessor_get_items( 'note', $notes_args );
 ?>
	
	<div id="intercessor-request-notes" class="postbox">
		<h3 class="hndle"><span><?php esc_html_e( 'Prayer Notes', 'intercessor' ); ?></span></h3>
		<div class="inside">
			<div id="intercessor-request-notes-inner">
				<?php

					echo intercessor_admin_get_notes_html( $notes );
					echo intercessor_admin_get_new_note_form( $prayer->id, 'prayer' );
				?>
			</div>

			<div class="clear"></div>
		</div><!-- /.inside -->
	</div><!-- /#intercessor-request-notes -->
	<?php
}
