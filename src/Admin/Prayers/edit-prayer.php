<?php
/**
 * Edit Prayer Page
 *
 * @package     IPR
 * @subpackage  Admin/Prayers
 * @copyright   Copyright (c) 2018, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! isset( $_GET['prayer'] ) || ! is_numeric( $_GET['prayer'] ) ) {
	wp_die(
	    esc_html__( 'Something went wrong. Please try again.', 'intercessor' ),
        esc_html__( 'Error', 'intercessor' ),
        array( 'response' => 400 )
    );
}

$prayer_id  = absint( $_GET['prayer'] );
$prayer     = intercessor_process_item( 'prayer', 'get', $prayer_id, false );
$title      = stripslashes( $prayer->title );
$message    = stripslashes( $prayer->message );
$prayer_key = esc_attr( $prayer->prayer_key );
$requester  = new Intercessor\Requester( $prayer->email );
$first_name = $requester->get_first_name();
$last_name  = $requester->get_last_name();
$share      = esc_attr( $prayer->share );
$notify     = absint( $prayer->notify );
$html       = new \Intercessor\Html();

// Dates & times.
$date_active  = empty( $prayer->date_active ) ? '' : date( 'Y-m-d', strtotime( $prayer->date_active ) );
$start_hour   = empty( $prayer->date_active ) ? '00' : date( 'H', strtotime( $prayer->date_active ) );
$start_minute = empty( $prayer->date_active ) ? '00' : date( 'i', strtotime( $prayer->date_active ) );
$end_date     = empty( $prayer->end_date ) ? '' : date( 'Y-m-d', strtotime( $prayer->end_date ) );
$end_hour     = empty( $prayer->end_date ) ? '23' : date( 'H', strtotime( $prayer->end_date ) );
$end_minute   = empty( $prayer->end_date ) ? '59' : date( 'i', strtotime( $prayer->end_date ) );
$hours        = intercessor_get_hour_values();
$minutes      = intercessor_get_minute_values();

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Edit Prayer', 'intercessor' ); ?> - <a href="<?php echo admin_url( 'admin.php?page=intercessor-request-requests' ); ?>" class="button-secondary"><?php esc_html_e( 'Go Back', 'intercessor' ); ?></a></h1>

	<hr class="wp-header-end">

	<form id="intercessor-edit-prayer" action="" method="post">
		<?php do_action( 'intercessor_edit_prayer_form_top', $prayer_id, $prayer ); ?>
		<table class="form-table">
			<tbody>
				<?php do_action( 'intercessor_edit_prayer_form_before_name', $prayer->id, $prayer ); ?>
				<tr>
					<th scope="row" valign="top">
						<label for="intercessor-first-name"><?php esc_html_e( 'First Name', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="first_name" id="intercessor-first-name" type="text" value="<?php echo esc_attr( stripslashes( $first_name ) ); ?>" />
						<p class="description"><?php esc_html_e( 'First name.', 'intercessor' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="intercessor-last-name"><?php esc_html_e( 'Last Name', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="last_name" id="intercessor-last-name" type="text" value="<?php echo esc_attr( stripslashes( $last_name ) ); ?>" />
						<p class="description"><?php esc_html_e( 'Last name.', 'intercessor' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="intercessor-email"><?php esc_html_e( 'Email', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="email" id="intercessor-email" type="email disabled" value="<?php echo esc_attr( stripslashes( $prayer->email ) ); ?>" />
						<p class="description"><?php esc_html_e( 'Email addresss.', 'intercessor' ); ?></p>
					</td>
				</tr>
				<?php do_action( 'intercessor_edit_prayer_form_before_title', $prayer->id, $prayer ); ?>
				<tr>
					<th scope="row" valign="top">
						<label for="intercessor-title"><?php esc_html_e( 'Prayer Request Title', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="title" id="intercessor-title" type="text" value="<?php echo esc_attr( $prayer->title ); ?>" />
						<p class="description"><?php esc_html_e( 'Prayer Request Title.', 'intercessor' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="intercessor-message"><?php esc_html_e( 'Prayer Request Message', 'intercessor' ); ?></label>
					</th>
					<td>
						<textarea cols="100" rows="7" name="message" id="intercessor-message"><?php echo esc_attr(  $prayer->message ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Prayer Request Message.', 'intercessor' ); ?></p>
					</td>
				</tr>


				<?php do_action( 'intercessor_edit_prayer_form_before_status', $prayer->id, $prayer ); ?>
				<tr>
					<th scope="row" valign="top">
						<label for="intercessor-status"><?php esc_html_e( 'Status', 'intercessor' ); ?></label>
					</th>
					<td>
						<select name="status" id="intercessor-status">
							<option value="active"<?php selected( $prayer->status, 'active' ); ?>><?php esc_html_e( 'Active', 'intercessor' ); ?></option>
							<option value="pending"<?php selected( $prayer->status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'intercessor' ); ?></option>
							<option value="personal"<?php selected( $prayer->status, 'personal' ); ?>><?php esc_html_e( 'Private', 'intercessor' ); ?></option>
							<option value="archived"<?php selected( $prayer->status, 'archived' ); ?>><?php esc_html_e( 'Archived', 'intercessor' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'The status of this prayer request.', 'intercessor' ); ?></p>
					</td>
				</tr>
				<?php
				do_action( 'intercessor_edit_prayer_form_before_share', $prayer->id, $prayer );
				?>
				<tr>
					<th scope="row" valign="top">
						<label for="intercessor-anon"><?php esc_html_e( 'Prayer Share', 'intercessor' ); ?></label>
					</th>
					<td>
						<select name="share" id="intercessor-share">
							<option value="freely" <?php selected( $prayer->share, 'freely' ); ?>><?php esc_html_e( 'Share freely', 'intercessor' ); ?></option>
							<option value="anon"<?php selected( $prayer->share, 'anon' ); ?>><?php esc_html_e( 'Share anonymously', 'intercessor' ); ?></option>
							<option value="no_share"<?php selected( $prayer->share, 'no_share' ); ?>><?php esc_html_e( 'Do not share - Private prayer', 'intercessor' ); ?></option>
							<option value="tweet"<?php selected( $prayer->share, 'tweet' ); ?>><?php esc_html_e( 'Share and tweet', 'intercessor' ); ?></option>
						</select>
						<span class="description">
							<?php esc_html_e( 'Choose how this prayer request will be share.', 'intercessor' ); ?>
						</span>
					</td>
				</tr>

                <?php do_action( 'intercessor_edit_prayer_form_before_start', $prayer->id, $prayer ); ?>

                <tr>
                    <th scope="row" valign="top">
                        <label for="intercessor-start"><?php _e( 'Start date', 'intercessor' ); ?></label>
                    </th>
                    <td>
                        <input name="date_active" id="intercessor-start" type="text" value="<?php echo esc_attr( $date_active ); ?>" class="intercessor_datepicker" placeholder="<?php echo esc_html__( 'Start Date', 'intercessor'  ); ?>" />
						<?php
						echo $html->select( array(
							'name'             => 'date_active_hour',
							'options'          => $hours,
							'selected'         => $start_hour,
							'chosen'           => true,
							'class'            => 'intercessor-time',
							'show_option_none' => false,
							'show_option_all'  => false,
						) );
						?>
						:
						<?php
						echo $html->select( array(
							'name'             => 'date_active_minute',
							'options'          => $minutes,
							'selected'         => $start_minute,
							'chosen'           => true,
							'class'            => 'intercessor-time',
							'show_option_none' => false,
							'show_option_all'  => false
						) );
						?>
                        <p class="description"><?php _e( 'Enter the display start date for this prayer request in the format of yyyy-mm-dd. For no start date, leave blank. If entered, the prayer can only be displayed after or on this date.', 'intercessor' ); ?></p>
                    </td>
                </tr>

                <?php do_action( 'intercessor_edit_prayer_form_before_end_date', $prayer->id, $prayer ); ?>

                <tr>
                    <th scope="row" valign="top">
                        <label for="intercessor-expiration"><?php _e( 'Expiration date', 'intercessor' ); ?></label>
                    </th>
                    <td>
                        <input name="end_date" id="intercessor-expiration" type="text" value="<?php echo esc_attr( $end_date ); ?>"  class="intercessor_datepicker" placeholder="<?php echo esc_html__( 'End Date', 'intercessor'  ); ?>" />
						<?php
						echo $html->select( array(
							'name'             => 'end_date_hour',
							'options'          => $hours,
							'selected'         => $end_hour,
							'chosen'           => true,
							'class'            => 'intercessor-time',
							'show_option_none' => false,
							'show_option_all'  => false
						) );
						?>
						:
						<?php
						echo $html->select( array(
							'name'             => 'end_date_minute',
							'options'          => $minutes,
							'selected'         => $end_minute,
							'chosen'           => true,
							'class'            => 'intercessor-time',
							'show_option_none' => false,
							'show_option_all'  => false
						) );
						?>
                        <p class="description"><?php _e( 'Enter the display end date for this prayer request in the format of yyyy-mm-dd. For no display end date, leave blank', 'intercessor' ); ?></p>
                    </td>
                </tr>
				<?php

				do_action( 'intercessor_edit_prayer_form_before_notify', $prayer->id, $prayer );
				?>
				<tr>
					<th scope="row" valign="top">
						<label for="intercessor-notify"><?php esc_html_e( 'Notify the requester', 'intercessor' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="intercessor-notify" name="notify" value="1"<?php checked( true, $notify ); ?>/>
						<span class="description"><?php esc_html_e( 'If checked the requester will be notified whenever the request gets prayed for.', 'intercessor' ); ?></span>
					</td>
				</tr>

			</tbody>
		</table>
		<?php do_action( 'intercessor_edit_prayer_form_bottom', $prayer_id, $prayer );
		?>
		<p class="submit">
			<input type="hidden" name="intercessor-action" value="admin_edit_prayer"/>
			<input type="hidden" name="prayer-id" value="<?php echo absint( $prayer->id ); ?>"/>
			<input type="hidden" name="intercessor-redirect" value="<?php echo esc_url( admin_url( 'admin.php?page=intercessor-prayers' ) ); ?>"/>
			<input type="hidden" name="intercessor-request-nonce" value="<?php echo wp_create_nonce( 'intercessor_edit_prayer_nonce' ); ?>"/>
			<input type="submit" value="<?php esc_html_e( 'Update Prayer Request', 'intercessor' ); ?>" class="button-primary"/>
		</p>
	</form>
</div>
