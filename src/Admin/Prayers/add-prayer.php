<?php
/**
 * Add Prayer Request Page
 *
 * @package     IPR
 * @subpackage  Admin/Prayers
 * @copyright   Copyright (c) 2018, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

?>
<div class="wrap">
	<h1><?php esc_html_e('Add New Prayer Request', 'intercessor' ); ?> - <a href="<?php echo admin_url( 'admin.php?page=intercessor-prayers' ); ?>" class="button-secondary"><?php esc_html_e('Go Back', 'intercessor' ); ?></a></h1>

	<hr class="wp-header-end">

	<form id="intercessor-add-prayer" action="" method="POST">
		<?php do_action( 'intercessor_add_prayer_form_top' ); ?>
		<table class="form-table">
			<tbody>
				<?php do_action( 'intercessor_add_prayer_form_before_first_name' ); ?>
				<tr>
					<th scope="row">
						<label for="intercessor-first-name"><?php esc_html_e('First Name', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="first_name" required="required" id="intercessor-first-name" type="text" value="" />
						<p class="description"><?php esc_html_e('First name.', 'intercessor' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'intercessor_add_prayer_form_before_last_name' ); ?>

				<tr>
					<th scope="row">
						<label for="intercessor-last-name"><?php esc_html_e('Last Name', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="last_name" required="required" id="intercessor-last-name" type="text" value="" />
						<p class="description"><?php esc_html_e('Last name.', 'intercessor' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'intercessor_add_prayer_form_before_email' ); ?>

				<tr>
					<th scope="row">
						<label for="intercessor-email"><?php esc_html_e('Email', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="email" required="required" id="intercessor-email" type="email" value="" />
						<p class="description"><?php esc_html_e('Email addresss.', 'intercessor' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'intercessor_add_prayer_form_before_title' ); ?>

				<tr>
					<th scope="row">
						<label for="intercessor-title"><?php esc_html_e('Prayer Request Title', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="title" required="required" id="intercessor-title" type="text" value="" />
						<p class="description"><?php esc_html_e('Prayer Request Title.', 'intercessor' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'intercessor_add_prayer_form_before_message' ); ?>

				<tr>
					<th scope="row">
						<label for="intercessor-message"><?php esc_html_e('Prayer Request Message', 'intercessor' ); ?></label>
					</th>
					<td>
						<textarea cols="100" rows="7" name="message" required="required" id="intercessor-message"></textarea>
						<p class="description"><?php esc_html_e('Prayer Request Message.', 'intercessor' ); ?></p>
					</td>
				</tr>


				<?php do_action( 'intercessor_add_prayer_form_before_status' ); ?>

				<tr>
					<th scope="row">
						<label for="intercessor-status"><?php esc_html_e('Status', 'intercessor' ); ?></label>
					</th>
					<td>
						<select name="status" id="intercessor-status" required="required">
							<option value="active"><?php esc_html_e('Active', 'intercessor' ); ?></option>
							<option value="pending"><?php esc_html_e('Pending', 'intercessor' ); ?></option>
							<option value="personal"><?php esc_html_e('Private', 'intercessor' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e('Choose the status for this prayer request.', 'intercessor' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'intercessor_add_prayer_form_before_share' ); ?>

				<tr>
					<th scope="row">
						<label for="intercessor-share"><?php esc_html_e('Prayer Share', 'intercessor' ); ?></label>
					</th>
					<td>
						<select name="share" id="intercessor-share" required="required">
							<option value="freely"><?php esc_html_e('Share freely', 'intercessor' ); ?></option>
							<option value="anon"><?php esc_html_e('Share anonymously', 'intercessor' ); ?></option>
							<option value="personal"><?php esc_html_e('Do not share', 'intercessor' ); ?></option>
							<option value="tweet"><?php esc_html_e('Share and tweet', 'intercessor' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e('Choose how this prayer request will be shared.', 'intercessor' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'intercessor_add_prayer_form_before_active_date' ); ?>

				<tr>
					<th scope="row">
						<label for="intercessor-date-active"><?php esc_html_e('Active date', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="date_active" id="intercessor-date-active" type="text" value="" class="intercessor_datepicker"/>
						<p class="description"><?php esc_html_e('Enter the date this prayer request will be active in the format of mm/dd/yyyy.', 'intercessor' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'intercessor_add_prayer_form_before_end_date' ); ?>

				<tr>
					<th scope="row">
						<label for="intercessor-end-date"><?php esc_html_e('End date', 'intercessor' ); ?></label>
					</th>
					<td>
						<input name="end_date" id="intercessor-end-date" type="text" class="intercessor_datepicker"/>
						<p class="description"><?php esc_html_e('Enter the last display date date for this prayer request in the format of mm/dd/yyyy. For no end-date, leave blank.', 'intercessor' ); ?></p>
					</td>
				</tr>

				<?php do_action( 'intercessor_add_prayer_form_before_notify' ); ?>

				<tr>
					<th scope="row">
						<label for="intercessor-notify"><?php esc_html_e('Notify the requester', 'intercessor' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="intercessor-notify" name="notify" value="1"/>
						<span class="description"><?php esc_html_e('If checked the requester will be notified whenever the request gets prayed for.', 'intercessor' ); ?></span>
					</td>
				</tr>

			</tbody>
		</table>
		<?php do_action( 'intercessor_add_prayer_form_bottom' ); ?>
		<p class="submit">
			<input type="hidden" name="intercessor-action" value="admin_add_prayer"/>
			<input type="hidden" name="intercessor-redirect" value="<?php echo esc_url( admin_url( 'admin.php?page=intercessor-prayers' ) ); ?>"/>
			<input type="hidden" name="intercessor-admin-prayer-nonce" value="<?php echo wp_create_nonce( 'intercessor_admin_prayer_nonce' ); ?>"/>
			<?php submit_button( esc_html__( 'Add Prayer', 'intercessor' ) ); ?>
		</p>
	</form>
</div>
