<?php
/**
 * Intercessor Tools Functions.
 *
 * @package     Intercessor
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 */

use Intercessor\Html;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Shows the tools panel which contains intercessor-specific tools.
 *
 * @since 0.9.5
 */
function intercessor_tools_page() {
	$active_tab = intercessor_get_current_tools_tab();

	// Enqueue necessary scripts.
	if ( 'export' === $active_tab ) {
	    wp_enqueue_script( 'intercessor-export' );
    } elseif ( 'import' === $active_tab ) {
	    wp_enqueue_script( 'intercessor-import' );
    }
	?>

    <div class="wrap">
        <h1><?php esc_html_e( 'Tools', 'intercessor' ); ?></h1>
        <h2 class="nav-tab-wrapper intercessor-nav-tab-wrapper">
			<?php

			intercessor_navigation_tabs( intercessor_get_tools_tabs(), $active_tab, array(
				'settings-updated' => false,
				'intercessor_notice'       => false
			) );
			?>
        </h2>

        <div class="metabox-holder">
			<?php
			do_action( 'intercessor_tools_tab_' . $active_tab );
			?>
        </div><!-- .metabox-holder -->
    </div><!-- .wrap -->

	<?php
}

/**
 * Retrieve tools tabs.
 *
 * @since 0.9.5
 *
 * @return array Tabs for the 'Tools' page.
 */
function intercessor_get_tools_tabs() {
	$tabs             = [];
	$tabs['general'] = esc_html__( 'General', 'intercessor' );
	$tabs['export']  = esc_html__( 'Export', 'intercessor' );
	$tabs['import']  = esc_html__( 'Import', 'intercessor' );
	$tabs['recount'] = esc_html__( 'Recount', 'intercessor' );

	return apply_filters( 'intercessor_tools_tabs', $tabs );
}

/**
 * Retrieves the current Tools tab.
 *
 * @since 0.9.5
 *
 * @return string Current Tools tab if present in the URL, 'export' otherwise.
 */
function intercessor_get_current_tools_tab() {
	if ( isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], intercessor_get_tools_tabs() ) ) {
		$active_tab = sanitize_text_field( $_GET['tab'] );
	} else {
		$active_tab = 'general';
	}

	/**
	 * Filter the current Tools tab.
	 *
	 * @since 0.9.5
	 *
	 * @param string $active_tab Current Tools tab ID.
	 */
	return apply_filters( 'intercessor_current_tools_tab', $active_tab );
}

/**
 * Display the ban emails tab
 *
 * @since 0.9.5
 */
function intercessor_tools_banned_emails_display() {
    // Bail if use does not have the required capabilities.
	if ( ! current_user_can( 'manage_prayer_settings' ) ) {
		return;
	}

	/**
	 * Fires before the banned emails box.
     *
     * @since 1.0.0
	 */
	do_action( 'intercessor_tools_banned_emails_before' );
	?>
    <div class="postbox">
        <h3><span><?php esc_html_e( 'Banned Emails', 'intercessor' ); ?></span></h3>
        <div class="inside">
            <p><?php esc_html_e( 'Emails placed in the box below will not be allowed to submit prayer requests.', 'intercessor' ); ?></p>
            <form method="post"
                  action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=general' ); ?>">
                <p>
                    <label for="intercessor_banned_emails"><?php esc_html_e( 'Banned Emails', 'intercessor' ); ?>
                    <textarea name="intercessor_banned_emails" rows="10"
                              class="large-text"><?php echo implode( "\n", intercessor_get_banned_emails() ); ?></textarea>
                    </label>
                    <span class="description"><?php esc_html_e( 'Enter emails and/or domains (starting with "@") and/or TLDs (starting with ".") to disallow, one per line.', 'intercessor' ); ?></span>
                </p>
                <p>
                    <input type="hidden" name="intercessor_action" value="save_banned_emails"/>
					<?php wp_nonce_field( 'intercessor_banned_emails_nonce', 'intercessor_banned_emails_nonce' ); ?>
					<?php submit_button( esc_html__( 'Save', 'intercessor' ), 'secondary', 'submit', false ); ?>
                </p>
            </form>
        </div><!-- .inside -->
    </div><!-- .postbox -->
	<?php

	/**
	 * Fires after the banned emails box.
	 *
	 * @since 1.0.0
	 */
	do_action( 'intercessor_tools_banned_emails_after' );

	/**
	 * Fires after the banned emails box and the tools section.
	 *
	 * @since 1.0.0
	 */
	do_action( 'intercessor_tools_after' );
}
add_action( 'intercessor_tools_tab_general', 'intercessor_tools_banned_emails_display' );

/**
 * Save banned emails.
 *
 * @return bool|void
 * @since 0.9.5
 */
function intercessor_tools_banned_emails_save() {
	if ( ! wp_verify_nonce( $_POST['intercessor_banned_emails_nonce'], 'intercessor_banned_emails_nonce' ) ) {
		return;
	}

	// Bail if user is not allowed.
	if ( ! current_user_can( 'manage_prayer_settings' ) ) {
		return;
	}

	if ( ! empty( $_POST['intercessor_banned_emails'] ) ) {
		// Sanitize the input.
		$emails = array_map( 'trim', explode( "\n", $_POST['intercessor_banned_emails'] ) );
		$emails = array_unique( $emails );
		$emails = array_map( 'sanitize_text_field', $emails );

		foreach ( $emails as $id => $email ) {
			if ( ! is_email( $email ) && $email[0] != '@' && $email[0] != '.' ) {
				unset( $emails[ $id ] );
			}
		}
	} else {
		$emails = '';
	}

	// Update banned emails option.
	return intercessor_update_option( 'banned_emails', $emails );
}
add_action( 'intercessor_save_banned_emails', 'intercessor_tools_banned_emails_save' );

/**
 * Display the recount stats.
 *
 * @since 0.9.5
 */
function intercessor_tools_recount_stats_display() {

	// Bail if the user does not have the required capabilities.
	if ( ! current_user_can( 'manage_prayer_settings' ) ) {
		return;
	}

	do_action( 'intercessor_tools_recount_stats_before' );
	?>

	<div class="postbox">
		<h3><span><?php esc_html_e( 'Recount Stats', 'intercessor' ); ?></span></h3>
		<div class="inside recount-stats-controls">
			<p><?php esc_html_e( 'Use these tools to recount / reset store stats.', 'intercessor' ); ?></p>
			<form method="post" id="intercessor-tools-recount-form" class="intercessor-export-form">
				<span>
					<?php wp_nonce_field( 'intercessor_ajax_export', 'intercessor_ajax_export' ); ?>

					<label for="recount-stats-type"></label><select name="intercessor-export-class" id="recount-stats-type">
						<option value="0" selected="selected"
								disabled="disabled"><?php esc_html_e( 'Please select an option', 'intercessor' ); ?></option>
						<option data-type="recount-request"
								value="IPR_Tools_Recount_Prayer_Requests"><?php esc_html_e( 'Recount Store Prayer Requests', 'intercessor' ); ?></option>
						<option data-type="recount-prayer"
								value="IPR_Tools_Recount_Prayer_Stats"><?php esc_html_e( 'Recount Prayed Counts for a Prayer Request', 'intercessor' ); ?></option>
						<option data-type="recount-all"
								value="IPR_Tools_Recount_All_Stats"><?php printf( esc_html__( 'Recount Stats for All %s', 'intercessor' ), 'Prayer Requests' ); ?></option>
						<option data-type="recount-requester-stats"
								value="IPR_Tools_Recount_Requester_Stats"><?php esc_html_e( 'Recount Requester Stats', 'intercessor' ); ?></option>
						<?php do_action( 'intercessor_recount_tool_options' ); ?>
						<option data-type="reset-stats"
								value="IPR_Tools_Reset_Stats"><?php esc_html_e( 'Reset Statistics', 'intercessor' ); ?></option>
					</select>

					<span id="tools-prayer-dropdown" style="display: none">
						<?php
						$args = [
							'name'   => 'prayer_id',
							'number' => - 1,
							'chosen' => true,
						];

						$html = new Html();
						echo $html->prayers_dropdown( $args );
						?>
					</span>

					<input type="submit" id="recount-stats-submit"
						   value="<?php esc_html_e( 'Submit', 'intercessor' ); ?>" class="button-secondary"/>

					<br/>

					<span class="intercessor-recount-stats-descriptions">
						<span id="recount-requests"><?php esc_html_e( 'Recalculates the total prayer requests and prayed counts.', 'intercessor' ); ?></span>
						<span id="recount-prayer"><?php esc_html_e( 'Recalculates the prayed counts for a specific prayer request.', 'intercessor' ); ?></span>
						<span id="recount-all"><?php  esc_html_e( 'Recalculates the prayers and sales stats for all %s.', 'intercessor' ); ?></span>
						<span id="recount-requester-stats"><?php esc_html_e( 'Recalculates the prayer counts for all Requesters.', 'intercessor' ); ?></span>
						<?php do_action( 'intercessor_recount_tool_descriptions' ); ?>
						<span id="reset-stats"><?php esc_html_e( '<strong>Deletes</strong> all prayer request records, Requesters, and related log entries.', 'intercessor' ); ?></span>
					</span>

					<span class="spinner"></span>

				</span>
			</form>
			<?php do_action( 'intercessor_tools_recount_forms' ); ?>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<?php
	do_action( 'intercessor_tools_recount_stats_after' );
}
add_action( 'intercessor_tools_tab_recount', 'intercessor_tools_recount_stats_display' );

/**
 * Display the clear upgrades tab.
 *
 * @since 0.9.5
 */
function intercessor_tools_clear_doing_upgrade_display() {
	if ( ! current_user_can( 'manage_prayer_settings' ) || false === get_option( 'intercessor_doing_upgrade' ) ) {
		return;
	}

	do_action( 'intercessor_tools_clear_doing_upgrade_before' );
	?>
    <div class="postbox">
        <h3><span><?php esc_html_e( 'Clear Incomplete Upgrade Notice', 'intercessor' ); ?></span></h3>
        <div class="inside">
            <p><?php esc_html_e( 'Sometimes a database upgrade notice may not be cleared after an upgrade is completed due to conflicts with other extensions or other minor issues.', 'intercessor' ); ?></p>
            <p><?php esc_html_e( 'If you\'re certain these upgrades have been completed, you can clear these upgrade notices by clicking the button below. If you have any questions about this, please contact the Intercessor support team and we\'ll be happy to help.', 'intercessor' ); ?></p>
            <form method="post"
                  action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=general' ); ?>">
                <p>
                    <input type="hidden" name="intercessor_action" value="clear_doing_upgrade"/>
					<?php wp_nonce_field( 'intercessor_clear_upgrades_nonce', 'intercessor_clear_upgrades_nonce' ); ?>
					<?php submit_button( esc_html__( 'Clear Incomplete Upgrade Notice', 'intercessor' ), 'secondary', 'submit', false ); ?>
                </p>
            </form>
        </div><!-- .inside -->
    </div><!-- .postbox -->
	<?php
	do_action( 'intercessor_tools_clear_doing_upgrade_after' );
}

add_action( 'intercessor_tools_tab_general', 'intercessor_tools_clear_doing_upgrade_display' );

/**
 * Execute upgrade notice clear.
 *
 * @since 0.9.5
 */
function intercessor_tools_clear_upgrade_notice() {
	if ( ! wp_verify_nonce( $_POST['intercessor_clear_upgrades_nonce'], 'intercessor_clear_upgrades_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_prayer_settings' ) ) {
		return;
	}

	delete_option( 'intercessor_doing_upgrade' );
}
add_action( 'intercessor_clear_doing_upgrade', 'intercessor_tools_clear_upgrade_notice' );

/**
 * Display the tools export tab.
 *
 * @since 0.9.5
 */
function intercessor_tools_export_display() {
	if ( ! current_user_can( 'manage_prayer_settings' ) ) {
		return;
	}

	// Enqueue scripts.
	wp_enqueue_script( 'intercessor-export' );

	/**
	 * Fires before the export
	 *
	 * @since 0.9.5
	 */
	do_action( 'intercessor_tools_export_before' );

	$html = new Html();
	?>

    <div id="intercessor-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content">

					<?php
					/**
					 * Fires at the top of the export tab content.
					 *
					 * @since 0.9.5
					 */
					do_action( 'intercessor_export_tab_content_top' );
					?>

					<div class="postbox intercessor-export-prayers-report">
						<h3><span><?php esc_html_e( 'Export Prayer Requests', 'intercessor' ); ?></span></h3>
						<div class="inside">
							<p><?php esc_html_e( 'Download a CSV of prayer request over a specific time range.', 'intercessor' ); ?></p>
							<form id="intercessor-export-prayers" class="intercessor-export-form intercessor-move-data" method="post">
								<?php echo $html->month_dropdown( 'start_month' ); ?>
								<?php echo $html->year_dropdown( 'start_year' ); ?>
								<?php echo _x( 'to', 'Date one to date two', 'intercessor' ); ?>
								<?php echo $html->month_dropdown( 'end_month' ); ?>
								<?php echo $html->year_dropdown( 'end_year' ); ?>
								<?php wp_nonce_field( 'intercessor_ajax_export', 'intercessor_ajax_export' ); ?>
								<input type="hidden" name="intercessor-export-class" value="Intercessor\Admin\Tools\Export\Batch_Prayers"/>
								<span>
									<input type="submit" value="<?php esc_html_e( 'Generate CSV', 'intercessor' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox intercessor-export-prayer-history">
						<h3><span><?php esc_html_e('Export Prayer History','intercessor' ); ?></span></h3>
						<div class="inside">
							<p><?php esc_html_e( 'Download a CSV of all prayers requests recorded.', 'intercessor' ); ?></p>

							<form id="intercessor-export-prayers" class="intercessor-export-form intercessor-move-data" method="post">
								<?php echo $html->date_field( array( 'id' => 'intercessor-prayer-export-start', 'name' => 'start', 'placeholder' => esc_html__( 'Choose start date', 'intercessor' ) )); ?>
								<?php echo $html->date_field( array( 'id' => 'intercessor-prayer-export-end','name' => 'end', 'placeholder' => esc_html__( 'Choose end date', 'intercessor' ) )); ?>
								<select name="status">
									<option value="any"><?php esc_html_e( 'All Statuses', 'intercessor' ); ?></option>
									<?php
									$statuses = intercessor_prayer_statuses();
									foreach( $statuses as $status => $label ) {
										echo '<option value="' . $status . '">' . $label . '</option>';
									}
									?>
								</select>
								<?php wp_nonce_field( 'intercessor_ajax_export', 'intercessor_ajax_export' ); ?>
								<input type="hidden" name="intercessor-export-class" value="Batch_History"/>
								<span>
									<input type="submit" value="<?php esc_html_e( 'Generate CSV', 'intercessor' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>

						</div><!-- .inside -->
					</div><!-- .postbox -->

					<?php
					$pdf_prayers = wp_nonce_url( add_query_arg( array(
						'intercessor_action' => 'generate_pdf_prayers'
						) ), 'intercessor_generate_pdf_prayers'
					);
					?>
					<div class="postbox intercessor-export-pdf-prayers">
						<h3><span><?php esc_html_e( 'Export PDF of All Prayer Requests', 'intercessor' ); ?></span></h3>
						<div class="inside">
							<p><?php esc_html_e( 'Download a PDF of all prayer requests', 'intercessor' ); ?></p>
							<form id="intercessor-export-pdf-prayers" method="post">
								<a class="button"
								   href="<?php echo $pdf_prayers; ?>">
									<?php esc_html_e( 'Generate PDF', 'intercessor' ); ?>
								</a>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox intercessor-export-requesters">
						<h3><span><?php esc_html_e('Export Requesters in CSV','intercessor' ); ?></span></h3>
						<div class="inside">
							<p><?php esc_html_e( 'Download a CSV of all Requesters.', 'intercessor' ); ?></p>
							<form id="intercessor-export-requesters" class="intercessor-export-form intercessor-move-data" method="post">
								<?php wp_nonce_field( 'intercessor_ajax_export', 'intercessor_ajax_export' ); ?>
								<input type="hidden" name="intercessor-export-class" value="Intercessor\Admin\Tools\Export\Batch_Requesters"/>
								<input type="submit" value="<?php esc_html_e( 'Generate CSV', 'intercessor' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox intercessor-export-settings">
						<h3><span><?php esc_html_e('Export Settings','intercessor' ); ?></span></h3>
						<div class="inside">
							<p><?php esc_html_e( 'Export the Intercessor settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'intercessor' ); ?></p>
							<form method="post"  class="intercessor-export-form intercessor-move-data" id="intercessor-export-settings">
								<p><input type="hidden" name="intercessor_action" value="export_settings"/></p>
								<p>
									<?php wp_nonce_field( 'intercessor_export_nonce', 'intercessor_export_nonce' ); ?>
									<input type="submit" value="<?php esc_html_e( 'Export JSON', 'intercessor' ); ?>" class="button-secondary"/>
								</p>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<?php do_action( 'intercessor_reports_tab_export_content_bottom' ); ?>

				</div><!-- .post-body-content -->
			</div><!-- .post-body -->
		</div><!-- .metabox-holder -->
	</div><!-- #intercessor-dashboard-widgets-wrap -->

	<?php
	/**
	 * Fires after the tools export tab.
	 *
	 * @since 0.9.5
	 */
	do_action( 'intercessor_tools_export_after' );
}
add_action( 'intercessor_tools_tab_export', 'intercessor_tools_export_display' );

/**
 * Display the tools import tab.
 *
 * @since 0.9.5
 */
function intercessor_tools_import_display() {
	if ( ! current_user_can( 'manage_prayer_settings' ) ) {
		return;
	}

	// Enqueue import scripts.
	wp_enqueue_script( 'intercessor-export' );

	/**
	 * Fires before the tools import tab.
	 *
	 * @since 0.9.5
	 */
	do_action( 'intercessor_tools_before_import_tab' );
	?>

    <div class="postbox intercessor-import-prayer-history">
        <h3>
			<span>
				<?php esc_html_e( 'Import Prayer Requests', 'intercessor' ); ?>
			</span>
		</h3>

        <div class="inside">
            <p><?php esc_html_e( 'Import a CSV file of prayers.', 'intercessor' ); ?></p>
            <form id="intercessor-import-prayers" class="intercessor-import-form intercessor-move-data"
                  action="<?php echo esc_url( add_query_arg( 'intercessor_action', 'upload_import_file', admin_url() ) ); ?>"
                  method="post" enctype="multipart/form-data">

                <div class="intercessor-import-file-wrap">
					<?php wp_nonce_field( 'intercessor_ajax_import', 'intercessor_ajax_import' ); ?>
                    <input type="hidden" name="intercessor-import-class" value="Intercessor\Admin\Tools\Import\Prayers"/>
                    <p>
                        <input name="intercessor-import-file" id="intercessor-prayers-import-file" type="file"/>

                        <span class="intercessor-import-button">
                            <input type="submit" value="<?php esc_html_e( 'Import CSV', 'intercessor' ); ?>"
                                   class="button-secondary"/>
                            <span class="spinner"></span>
                        </span>
                    </p>
                </div>

                <div class="intercessor-import-options" id="intercessor-import-prayers-options" style="display:none;">

                    <p>
						<?php
						printf(
							esc_html__( 'Each column loaded from the CSV needs to be mapped to a prayer field. Select the column that should be mapped to each field below. Any columns not needed can be ignored. See <a href="%s" target="_blank">this guide</a> for assistance with importing prayer requests.', 'intercessor' ),
							'http://docs.intercessor.com/prayers-import'
						);
						?>
                    </p>

                    <table class="widefat intercessor_repeatable_table striped" width="100%" cellpadding="0" cellspacing="0">
                        <thead>
                        <tr>
                            <th><strong><?php esc_html_e( 'Prayer Field', 'intercessor' ); ?></strong></th>
                            <th><strong><?php esc_html_e( 'CSV Column', 'intercessor' ); ?></strong></th>
                            <th><strong><?php esc_html_e( 'Data Preview', 'intercessor' ); ?></strong></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><?php esc_html_e( 'Requester ID', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[requester_id]" class="intercessor-import-csv-column"
                                        data-field="Requester ID">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'User', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[user_id]" class="intercessor-import-csv-column"
                                        data-field="User">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'First Name', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[first_name]" class="intercessor-import-csv-column"
                                        data-field="First Name">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Last Name', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[last_name]" class="intercessor-import-csv-column"
                                        data-field="Last Name">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Email', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[email]" class="intercessor-import-csv-column" data-field="Email">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Title', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[title]" class="intercessor-import-csv-column"
                                        data-field="Prayer Title">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Prayer Message', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[message]" class="intercessor-import-csv-column"
                                        data-field="Prayer Message">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Status', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[status]" class="intercessor-import-csv-column"
                                        data-field="Status">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Prayer Key', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[prayer_key]" class="intercessor-import-csv-column"
                                        data-field="Prayer Key">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Prayer Share', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[share]" class="intercessor-import-csv-column"
                                        data-field="Prayer Share">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>

                        <tr>
                            <td><?php esc_html_e( 'Notify', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[notify]" class="intercessor-import-csv-column"
                                        data-field="Notify">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Date Created', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[date_created]" class="intercessor-import-csv-column" data-field="Date">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Date Active', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[date_active]" class="intercessor-import-csv-column" data-field="Date Active">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'End Date', 'intercessor' ); ?></td>
                            <td>
                                <select name="intercessor-import-field[end_date]" class="intercessor-import-csv-column" data-field="End Date">
                                    <option value=""><?php esc_html_e( '- Ignore this field -', 'intercessor' ); ?></option>
                                </select>
                            </td>
                            <td class="intercessor-import-preview-field"><?php esc_html_e( '- select field to preview data -', 'intercessor' ); ?></td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button class="intercessor-import-proceed button-primary">
							<?php esc_html_e( 'Process Import', 'intercessor' ); ?>
						</button>
                    </p>
                </div>
            </form>
        </div><!-- .inside -->
    </div><!-- .postbox -->

	<div class="postbox">
        <h3><span><?php esc_html_e( 'Import Settings', 'intercessor' ); ?></span></h3>
        <div class="inside">
            <p><?php esc_html_e( 'Import the Intercessor settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'intercessor' ); ?></p>
            <form method="post" enctype="multipart/form-data"
                  action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=import' ); ?>">
                <p>
                    <input type="file" name="import_file"/>
                </p>
                <p>
                    <input type="hidden" name="intercessor_action" value="import_settings"/>
					<?php wp_nonce_field( 'intercessor_import_nonce', 'intercessor_import_nonce' ); ?>
					<?php submit_button( esc_html__( 'Import', 'intercessor' ), 'secondary', 'submit', false ); ?>
                </p>
            </form>
        </div><!-- .inside -->
    </div><!-- .postbox -->


	<?php
	do_action( 'intercessor_tools_import_after' );
}
add_action( 'intercessor_tools_tab_import', 'intercessor_tools_import_display' );

/**
 * Process a settings export that generates a .json file of the prayer settings
 *
 * @since 0.9.5
 */
function intercessor_tools_export_process_export() {

	// Bail if no nonce.
	if ( empty( $_POST['intercessor_export_nonce'] ) ) {
		return;
	}

	// Bail if nonce does not verify.
	if ( ! wp_verify_nonce( $_POST['intercessor_export_nonce'], 'intercessor_export_nonce' ) ) {
		return;
	}

	// Bail if user cannot manage prayer.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	/**
	 * Filter the settings export filename
	 *
	 * @since 0.9.5
	 *
	 * @param string $filename The file name to export settings to
	 */
	$filename             = apply_filters( 'intercessor_settings_export_filename', 'intercessor-settings-export-' . date( 'm-d-Y' ) ) . '.json';
/*
	$settings = [];
	$settings = get_option( 'intercessor_settings' );

	intercessor_set_time_limit();

	nocache_headers();

	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Expires: 0' );

	echo wp_json_encode( $settings );

	wp_send_json(
		array(
			'intercessor_settings' => $settings,
		)
	);
*/
	$settings = new \Intercessor\Admin\Tools\Export\Settings();

	$settings->export();

}
add_action( 'intercessor_export_settings', 'intercessor_tools_export_process_export' );

/**
 * Process a settings import from a json file
 *
 * @since 0.9.5
 * @return void
 */
function intercessor_tools_export_process_import() {

	if ( empty( $_POST['intercessor_import_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['intercessor_import_nonce'], 'intercessor_import_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( intercessor_get_file_extension( $_FILES['import_file']['name'] ) !== 'json' ) {
		wp_die( esc_html__( 'Please upload a valid .json file', 'intercessor' ), esc_html__( 'Error', 'intercessor' ), array( 'response' => 400 ) );
	}

	$import_file = $_FILES['import_file']['tmp_name'];

	if ( empty( $import_file ) ) {
		wp_die( esc_html__( 'Please upload a file to import', 'intercessor' ), esc_html__( 'Error', 'intercessor' ), array( 'response' => 400 ) );
	}

	// Retrieve the settings from the file and convert the json object to an array.
	$settings = intercessor_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	// Update the settings.
	$intercessor_settings = $settings['intercessor_settings'];
	update_option( 'intercessor_settings', $intercessor_settings );

	wp_safe_redirect(
		intercessor_get_admin_url(
			array(
				'page'                => 'intercessor-tools',
				'intercessor-message' => 'settings-imported'
			)
		)
	);
}
add_action( 'intercessor_import_settings', 'intercessor_tools_export_process_import' );
