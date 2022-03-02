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

// Exit if accessed directly,
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tools Options Page
 *
 * Renders the tools options page contents.
 *
 * @since 1.0.0
 * @return void
 */
function intercessor_tools_page() {

	// Enqueue styles and scripts.
	wp_enqueue_style( 'intercessor-datepicker' );
	wp_enqueue_script( 'intercessor-export' );

	// Get active tab.
	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], intercessor_get_tools_tabs() ) ? $_GET[ 'tab' ] : 'export';

	ob_start();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Tools', 'intercessor' ); ?></h1>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( intercessor_get_tools_tabs() as $tab_id => $tab_name ) {
				// Set up tab url.
				$tab_url = add_query_arg(
					[
						'settings-updated'   => false,
						'tab'                => $tab_id,
						'intercessor_notice' => false,
					]
				);

				// Get the active tab.
				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div id="tab_container">
			<?php do_action( 'intercessor_tools_tab_' . $active_tab ); ?>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}

/**
 * Retrieve tools tabs
 *
 * @since 1.0.0
 * @return array $tabs Array of tools tabs.
 */
function intercessor_get_tools_tabs() : array {

	$tabs              = [];
	$tabs['export']    = esc_html__( 'Export', 'intercessor' );
	$tabs['import']    = esc_html__( 'Import', 'intercessor' );
	$tabs['recount']   = esc_html__( 'Recount Stats', 'intercessor' );
	$tabs['migration'] = esc_html__( 'Migration Assistant', 'intercessor' );
	$tabs['misc']      = esc_html__( 'Miscellaneous', 'intercessor' );

	return apply_filters( 'intercessor_tools_tabs', $tabs );
}

/**
 * Export tab
 *
 * @since 1.0.0
 * @return void
 */
function intercessor_export_tab() {

	/**
	 * Fires before the export selection boxes.
	 *
	 * @since 1.0.0
	 */
	do_action( 'intercessor_before_tools_export' );

	?>
	<div id="intercessor-dashboard-widgets-wrap">
		<div class="metabox-holder">

			<?php
			/**
			 * Fires before the prayer requests export tab.
			 *
			 * @since 1.0.0
			 */
			do_action( 'intercessor_before_tools_export_prayers' );
			?>
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Export Prayer Requests', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Export Prayer Requests to a CSV file.', 'intercessor' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=export' ); ?>">
						<p>

							<input type="text" class="intercessor-datepicker" autocomplete="off" name="start_date" placeholder="<?php esc_html_e( 'From - mm/dd/yyyy', 'intercessor' ); ?>"/>
							<input type="text" class="intercessor-datepicker" autocomplete="off" name="end_date" placeholder="<?php esc_html_e( 'To - mm/dd/yyyy', 'intercessor' ); ?>"/>

							<select name="status" id="status">
								<option value="0"><?php esc_html_e( 'All Statuses', 'intercessor' ); ?></option>
								<option value="active"><?php esc_html_e( 'Active', 'intercessor' ); ?></option>
								<option value="personal"><?php esc_html_e( 'Private', 'intercessor' ); ?></option>
								<option value="pending"><?php esc_html_e( 'Pending', 'intercessor' ); ?></option>
								<option value="archived"><?php esc_html_e( 'Archived', 'intercessor' ); ?></option>
							</select>
						</p>
						<p>
							<input type="hidden" name="intercessor_action" value="export_prayers" />
							<?php wp_nonce_field( 'intercessor_export_prayers_nonce', 'intercessor_export_prayers_nonce' ); ?>
							<?php submit_button( esc_html__( 'Export', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		
			<?php
			/**
			 * Fires before the prayed counts export tab.
			 *
			 * @since 1.0.0
			 */
			do_action( 'intercessor_before_tools_export_prayed_counts' );
			?>
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Export Prayed Counts', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Export Prayed Counts to a CSV file.', 'intercessor' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=export' ); ?>">
						<p>
							<input type="text" class="intercessor-datepicker" autocomplete="off" name="start_date" placeholder="<?php esc_html_e( 'From - mm/dd/yyyy', 'intercessor' ); ?>"/>
							<input type="text" class="intercessor-datepicker" autocomplete="off" name="end_date" placeholder="<?php esc_html_e( 'To - mm/dd/yyyy', 'intercessor' ); ?>"/>
						</p>
						<p>
							<input type="hidden" name="intercessor_action" value="export_prayed_counts" />
							<?php wp_nonce_field( 'intercessor_export_prayed_counts_nonce', 'intercessor_export_prayed_counts_nonce' ); ?>
							<?php submit_button( esc_html__( 'Export', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<?php
			/**
			 * Fires before the requester export tab.
			 *
			 * @since 1.0.0
			 */
			do_action( 'intercessor_before_tools_export_requesters' );
			?>
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Export Requesters', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Export requesters to a CSV file.', 'intercessor' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=export' ); ?>">
						<p>
							<span class="intercessor-ajax-search-wrap">
								<img class="intercessor-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
							</span>
							<input type="text" class="intercessor-datepicker" autocomplete="off" name="start_date" placeholder="<?php esc_html_e( 'From - mm/dd/yyyy', 'intercessor' ); ?>"/>
							<input type="text" class="intercessor-datepicker" autocomplete="off" name="end_date" placeholder="<?php esc_html_e( 'To - mm/dd/yyyy', 'intercessor' ); ?>"/>
							<div id="intercessor_user_search_results"></div>
						</p>
						<p>
							<input type="hidden" name="intercessor_action" value="export_requesters" />
							<?php wp_nonce_field( 'intercessor_export_requesters_nonce', 'intercessor_export_requesters_nonce' ); ?>
							<?php submit_button( esc_html__( 'Export', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<?php
			/**
			 * Fires before the PDF of Prayer Request export tab.
			 *
			 * @since 1.0.0
			 */
			do_action( 'intercessor_before_tools_export_pdf_prayers' );
			?>
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Export Prayers PDF', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Export Prayer Requests to a PDF file.', 'intercessor' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=export' ); ?>">
						<p>
							<input type="hidden" name="intercessor_action" value="export_pdf_prayers" />
							<?php wp_nonce_field( 'intercessor_export_pdf_prayers_nonce', 'intercessor_export_pdf_prayers_nonce' ); ?>
							<?php submit_button( esc_html__( 'Export', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<?php
			/**
			 * Fires before the settings export tab.
			 *
			 * @since 1.0.0
			 */
			do_action( 'intercessor_before_tools_export_settings' );
			?>
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Export Settings', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Export the Intercessor settings for this site as a .json file. You could then easily import the configuration into another site.', 'intercessor' ); ?></p>
					<form method="post" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=export' ); ?>">
						<p><input type="hidden" name="intercessor_action" value="admin_export_settings" /></p>
						<p>
							<?php wp_nonce_field( 'intercessor_export_settings_nonce', 'intercessor_export_settings_nonce' ); ?>
							<?php submit_button( esc_html__( 'Export', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

		</div><!-- .metabox-holder -->
	</div><!-- #intercessor-dashboard-widgets-wrap -->
	<?php

	/**
	 * Fires after the export selection boxes.
	 *
	 * @since 1.0.0
	 */
	do_action( 'intercessor_after_tools_export' );
}
add_action( 'intercessor_tools_tab_export', 'intercessor_export_tab' );


/**
 * Import tab
 *
 * @since 1.0.0
 * @return void
 */
function intercessor_import_tab() {
	// Enqueue necessary script.
	wp_enqueue_script( 'intercessor-import' );

	/**
	 * Fires before the import selection boxes.
	 *
	 * @since 1.0.0
	 */
	do_action( 'intercessor_before_tools_import' );

	?>
	<div id="intercessor-dashboard-widgets-wrap">
		<div class="metabox-holder">

			<?php
			/**
			 * Fires before the prayer requests import tab.
			 *
			 * @since 1.0.0
			 */
			do_action( 'intercessor_before_tools_import_prayers' );
			?>
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Import Prayers', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Import the Intercessor prayer requests from a CSV file.', 'intercessor' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=import' ); ?>">
						<p>
							<input type="file" name="import_prayers"/>
						</p>
						<p>
							<input type="hidden" name="intercessor_action" value="import_prayers" />
							<?php wp_nonce_field( 'intercessor_import_prayers_nonce', 'intercessor_import_prayers_nonce' ); ?>
							<?php submit_button( esc_html__( 'Import', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<?php
			/**
			 * Fires before the prayed counts import tab.
			 *
			 * @since 1.0.0
			 */
			do_action( 'intercessor_before_tools_import_prayed_counts' );
			?>
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Import Prayed Counts', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Import the Intercessor prayed counts from a CSV file.', 'intercessor' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=import' ); ?>">
						<p>
							<input type="file" name="import_prayed"/>
						</p>
						<p>
							<input type="hidden" name="intercessor_action" value="import_prayed_counts" />
							<?php wp_nonce_field( 'intercessor_import_prayed_counts_nonce', 'intercessor_import_prayed_counts_nonce' ); ?>
							<?php submit_button( esc_html__( 'Import', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<?php
			/**
			 * Fires before the requesters import tab.
			 *
			 * @since 1.0.0
			 */
			do_action( 'intercessor_before_tools_import_requesters' );
			?>
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Import Requesters', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Import the Intercessor requesters from a CSV file.', 'intercessor' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=import' ); ?>">
						<p>
							<input type="file" name="import_requester"/>
						</p>
						<p>
							<input type="hidden" name="intercessor_action" value="import_reqeusters" />
							<?php wp_nonce_field( 'intercessor_import_reqeusters_nonce', 'intercessor_import_reqeusters_nonce' ); ?>
							<?php submit_button( esc_html__( 'Import', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<?php
			/**
			 * Fires before the settings import tab.
			 *
			 * @since 1.0.0
			 */
			do_action( 'intercessor_before_tools_import_settings' );
			?>
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Import Settings', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Import the Intercessor settings from a .json file. ', 'intercessor' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=import' ); ?>">
						<p>
							<input type="file" name="import_file"/>
						</p>
						<p>
							<input type="hidden" name="intercessor_action" value="import_settings" />
							<?php wp_nonce_field( 'intercessor_import_settings_nonce', 'intercessor_import_settings_nonce' ); ?>
							<?php submit_button( esc_html__( 'Import', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

		</div><!-- .metabox-holder -->
	</div><!-- #intercessor-dashboard-widgets-wrap -->
	<?php

	/**
	 * Fires after the import selection boxes.
	 *
	 * @since 1.0.0
	 */
	do_action( 'intercessor_after_tools_import' );
}
add_action( 'intercessor_tools_tab_import', 'intercessor_import_tab' );

/**
 * Recount Tab
 *
 * @since       1.0
 * @return      void
 */
function intercessor_recount_tab() {
	$html       = new \Intercessor\Html();
	$requesters = $html->requester_dropdown();
?>
	<div id="intercessor-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div class="postbox">
				<h3><span><?php esc_html_e( 'Recount Requester Stats', 'intercessor' ); ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Use this tool to recount requester statistics.', 'intercessor' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=intercessor-tools&tab=recount' ); ?>">
						<p>
							<span class="intercessor-ajax-search-wrap">
								<input type="text" name="user_name" id="user_name" class="intercessor-user-search" autocomplete="off" placeholder="<?php esc_html_e( 'Requester name', 'intercessor' ); ?>"/>
								<img class="intercessor-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
							</span>
							<select name="recount_type">
								<option value="single"><?php esc_html_e( 'All Requesters', 'intercessor' ); ?></option>
								<option value="all"><?php echo $requesters; ?></option>
							</select>
							<div id="intercessor_user_search_results"></div>
							<div class="description"><?php esc_html_e( 'Enter the name of the requester or begin typing to perform a search based on the requester\'s name.', 'intercessor' ); ?></div>
						</p>
						<p>
							<input type="hidden" name="user_id" id="user_id" value="0"/>
							<input type="hidden" name="requester_id" id="user_id" value="0"/>
							<input type="hidden" name="intercessor_action" value="recount_stats"/>
							<?php submit_button( esc_html__( 'Recount', 'intercessor' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->
	</div><!-- #intercessor-dashboard-widgets-wrap -->
<?php
}
add_action( 'intercessor_tools_tab_recount', 'intercessor_recount_tab' );

/**
 * Migration assistant tab
 *
 * @since 1.0.0
 * @return oid
 */
function intercessor_migration_tab() {
	$user_counts = count_users();

	$_roles = new WP_Roles();
	$roles  = [];

	foreach ( $_roles->get_names() as $role => $label ) {
		$roles[ $role ]['label'] = translate_user_role( $label );
		$roles[ $role ]['count'] = isset( $user_counts['avail_roles'][ $role ] ) ? $user_counts['avail_roles'][ $role ] : 0;
	}
	$html = new Html();
	?>
	<div id="intercessor-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div class="postbox">
				<div class="inside">
					<p><?php esc_html_e( 'These tools assist in migrating requester and referral data from existing platforms.', 'intercessor' ); ?></p>
				</div><!-- .inside -->
			</div><!-- .postbox -->

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

			<div class="postbox">
				<h3><span>WP Affiliate</span></h3>
				<div class="inside">
					<p><?php esc_html_e( 'Use this tool to migrate existing requester accounts from WP Affiliate to AffiliateWP.', 'intercessor' ); ?></p>
					<form method="get">
						<input type="hidden" name="type" value="wp-requester"/>
						<input type="hidden" name="part" value="requesters"/>
						<input type="hidden" name="page" value="intercessor-migrate"/>
						<p>
							<input type="submit" value="<?php esc_html_e( 'Migrate Data from WP Affiliate', 'intercessor' ); ?>" class="button"/>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

		</div><!-- .metabox-holder -->
	</div><!-- #intercessor-dashboard-widgets-wrap -->
<?php
}
add_action( 'intercessor_tools_tab_migration', 'intercessor_migration_tab' );

/**
 * Miscellaneous Tool tab.
 *
 * @since 1.0.0
 * @return void
 */
function intercessor_miscellanous_tab() {
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

	<div id="intercessor-dashboard-widgets-wrap">
		<div class="metabox-holder">
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

		</div><!-- .metabox-holder -->
	</div><!-- #intercessor-dashboard-widgets-wrap -->
	<?php

	/**
	 * Fires after the banned emails box.
	 *
	 * @since 1.0.0
	 */
	do_action( 'intercessor_tools_banned_emails_after' );

}
add_action( 'intercessor_tools_tab_misc', 'intercessor_miscellanous_tab' );
