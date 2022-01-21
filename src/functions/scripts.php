<?php
/**
 * Intercessor Scripts and Styles
 *
 * @link       https://github.com/victoraigbeghian
 * @since      0.9.5
 *
 * @package    Intercessor
 * @subpackage Intercessor/includes
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || exit;

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 0.9.5
 * @global $post
 * @return void
 */
function intercessor_load_scripts() {

	$js_dir = INTERCESSOR_URL . 'assets/js/frontend/';

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	if ( intercessor_is_prayer_request_form_page() ) {
		wp_register_script( 'intercessor-js', $js_dir . 'intercessor' . $suffix . '.js', array( 'jquery' ), INTERCESSOR_VERSION, true );
		wp_enqueue_script( 'intercessor-js' );
	}

	wp_register_script( 'intercessor-ajax', $js_dir . 'intercessor-ajax' . $suffix . '.js', array( 'jquery' ), INTERCESSOR_VERSION, false );

	wp_localize_script(
		'intercessor-ajax',
		'intercessor_params',
		apply_filters(
			'intercessor_ajax_params',
			array(
				'ajaxurl'   => intercessor_get_ajax_url(),
				'praying'   => esc_html__( 'You are praying.', 'intercessor' ),
				'prayed'    => esc_html__( 'Thanks for praying.', 'intercessor' ),
				'nopraying' => esc_html__( 'There was an error processing your praying for that request. please refresh your browser and try again.', 'intercessor' ),
			) )
	);

	if ( intercessor_is_listing_page() ) {

		wp_enqueue_script( 'intercessor-ajax' );

	}

	if ( intercessor_is_prayer_history_page() ) {
		wp_register_script( 'intercessor-history', $js_dir . 'intercessor-history' . $suffix . '.js', array( 'jquery' ), INTERCESSOR_VERSION, false );

		wp_localize_script(
			'intercessor-history',
			'intercessor_vars',
			apply_filters(
				'intercessor_history_params',
				[
					'delete_prayer' => esc_html__( 'Are you sure you want to delete this prayer request? The process is irreversible.', 'intercessor' ),
				]
			)
		);

		wp_enqueue_script( 'intercessor-history' );

		// Enqueue Google recaptcha if the user is not logged in.
		if ( ! is_user_logged_in() ) {
		//	if ( intercessor_recaptcha_is_enabled() ) {
				wp_register_script( 'intercessor-recaptcha', 'https://www.google.com/recaptcha/api.js', [], INTERCESSOR_VERSION, true );
				wp_enqueue_script( 'intercessor-recaptcha' );
		//	}
		}
	}


}
add_action( 'wp_enqueue_scripts', 'intercessor_load_scripts' );

/**
 * Load Styles
 *
 * Enqueues the required styles.
 *
 * @since 0.9.5
 * @return void
 */
function intercessor_load_styles() {
	$css_dir = INTERCESSOR_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Register frontend notices style.
	wp_register_style( 'intercessor-notices', $css_dir . 'intercessor-notices' . $suffix . '.css', [], INTERCESSOR_VERSION, 'all' );

	// Register Widget styles.
	wp_register_style( 'intercessor-recent-prayers', $css_dir . 'recent-prayers' . $suffix . '.css', [], INTERCESSOR_VERSION, 'all' );

	// Register and conditionally enqueue necessary styles.
	if ( intercessor_is_prayer_request_form_page() ) {
		wp_register_style( 'intercessor-form', $css_dir . 'intercessor' . $suffix . '.css', [], INTERCESSOR_VERSION, 'all' );
		wp_enqueue_style( 'intercessor-form' );
		wp_enqueue_style( 'intercessor-notices' );
	}

	if ( intercessor_is_listing_page() ) {
		wp_register_style( 'intercessor-prayers', $css_dir . 'intercessor-listing' . $suffix . '.css', [], INTERCESSOR_VERSION, 'all' );
		wp_enqueue_style( 'intercessor-prayers' );
		wp_enqueue_style( 'intercessor-notices' );
	}

	if ( intercessor_is_prayer_history_page() ) {
		wp_register_style( 'intercessor-history', $css_dir . 'intercessor-history' . $suffix . '.css', [], INTERCESSOR_VERSION, 'all' );
		wp_enqueue_style( 'intercessor-history' );
		wp_enqueue_style( 'intercessor-notices' );
	}
}
add_action( 'wp_enqueue_scripts', 'intercessor_load_styles' );

/**
 * Load Admin Styles and Scripts
 *
 * Enqueues the required styles and scripts.
 *
 * @since 0.9.5
 * @global $post
 * @return void
 */
function intercessor_load_admin_scripts_styles( $hook ) {
	global $post;

	$js_dir     = INTERCESSOR_URL . 'assets/js/';
	$css_dir    = INTERCESSOR_URL . 'assets/css/';
	$admin_deps = array( 'jquery', 'jquery-form', 'underscore' );
	$version    = INTERCESSOR_VERSION;

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Register Intercessor styles.
	wp_register_style( 'intercessor-admin', $css_dir . 'intercessor-admin' . $suffix . '.css', [], $version );
	wp_enqueue_style( 'intercessor-admin' );
	wp_register_style( 'intercessor-reports', $css_dir . 'intercessor-admin-reports' . $suffix . '.css', [], $version );

	// Bail if not on intercessor admin pages.
	if ( ! apply_filters( 'intercessor_load_admin_scripts', intercessor_is_admin_page(), $hook ) ) {
		return;
	}

	// These have to be global.
	wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', [], $version );
	wp_enqueue_style( 'jquery-chosen' );

	wp_register_script( 'jquery-chosen', $js_dir . 'vendor/chosen.jquery' . $suffix . '.js', array( 'jquery' ), $version, true );
	wp_enqueue_script( 'jquery-chosen' );

	wp_enqueue_script( 'jquery-form' );

	// Color picker.
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	// Media manager.
	wp_enqueue_media();

	wp_register_style( 'colorbox', $css_dir . 'colorbox' . $suffix . '.css', [], '1.3.20' );
	wp_enqueue_style( 'colorbox' );

	wp_register_script( 'colorbox', $js_dir . 'vendor/jquery.colorbox-min.js', array( 'jquery' ), '1.3.20', true );
	wp_enqueue_script( 'colorbox' );

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-tooltip' );

	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

	$scripts = array( 'jquery', 'jquery-form', 'inline-edit-post' );

//	wp_register_script( 'intercessor-admin-scripts', $js_dir . 'admin/intercessor-admin' . $suffix . '.js', $scripts, $version, false );

//	wp_enqueue_script( 'intercessor-admin-scripts' );

	wp_register_script( 'intercessor-admin-prayers', $js_dir . 'admin/admin-prayers' . $suffix . '.js', 'jquery-chosen', $version, false );

	if ( 'intercessor-prayers' === intercessor_is_admin_page() ) {
		wp_enqueue_script( 'intercessor-admin-prayers' );
	}

	wp_localize_script(
		'intercessor-admin-prayers',
		'intercessor_vars',
		array(
			'intercessor_version'     => $version,
			'ajaxurl'                 => intercessor_get_ajax_url(),
			'add_new_prayer'          => esc_html__( 'Add New Prayer', 'intercessor' ),
			'delete_prayer'           => esc_html__( 'Are you sure you wish to delete this prayer?', 'intercessor' ),
			'delete_prayer_note'      => esc_html__( 'Are you sure you wish to delete this note?', 'intercessor' ),
			'resend_notification'     => esc_html__( 'Are you sure you wish to resend the prayer notification?', 'intercessor' ),
			'delete_prayer_request'   => sprintf(
				/* translators: %s: prayer request */
				esc_html__( 'Are you sure you wish to delete this %s?', 'intercessor' ),
				'Prayer Request'
			),
			'one_field_min'           => esc_html__( 'You must have at least one field', 'intercessor' ),
			'one_option'              => sprintf(
				/* translators: %s: prayer request */
				esc_html__( 'Choose a %s', 'intercessor' ),
				'Prayer Request'
			),
			'one_or_more_option'      => sprintf(
				/* translators: %s: prayer request */
				esc_html__( 'Choose one or more %s', 'intercessor' ),
				'Prayer Requests'
			),
			'new_media_ui'            => apply_filters( 'intercessor_use_35_media_ui', 1 ),
			'remove_text'             => esc_html__( 'Remove', 'intercessor' ),
			'type_to_search'          => sprintf( esc_html__( 'Type to search %s', 'intercessor' ), 'Prayer Requests' ),
			'show_advanced_settings'  => esc_html__( 'Show advanced settings', 'intercessor' ),
			'hide_advanced_settings'  => esc_html__( 'Hide advanced settings', 'intercessor' ),
			'chosen'                  => array(
				'no_results_msg'  => esc_html__( 'No results match {search_term}', 'intercessor' ),
				'ajax_search_msg' => esc_html__( 'Searching results for match {search_term}', 'intercessor' ),
			),
			'unlock_requester_fields' => esc_html__( 'To edit first name and last name, please go to user profile of the requester.', 'intercessor' ),
			'remove_from_bulk_delete' => esc_html__( 'Remove from Bulk Delete', 'intercessor' ),
			'requesters_bulk_action'  => array(
				'no_requester_selected' => esc_html__( 'You must choose at least one or more Requesters to delete.', 'intercessor' ),
				'no_action_selected'    => esc_html__( 'You must select a bulk action to proceed.', 'intercessor' ),
			),
			'prayers_bulk_action'     => array(
				'delete'              => array(
					'zero'     => esc_html__( 'You must choose at least one or more prayers to delete.', 'intercessor' ),
					'single'   => esc_html__( 'Are you sure you want to permanently delete this prayer?', 'intercessor' ),
					'multiple' => esc_html__( 'Are you sure you want to permanently delete the selected {prayer_count} prayers?', 'intercessor' ),
				),
				'resend_notification' => array(
					'zero'     => esc_html__( 'You must choose at least one or more recipients to resend the email notification.', 'intercessor' ),
					'single'   => esc_html__( 'Are you sure you want to resend the email notification to this recipient?', 'intercessor' ),
					'multiple' => esc_html__( 'Are you sure you want to resend the emails notification to {prayer_count} recipients?', 'intercessor' ),
				),
			)
		)
	);

	// Register import and export scripts and styles.
	wp_register_script( 'intercessor-export', $js_dir . 'admin/export/export' . $suffix . '.js', 'jquery', $version, false );
	wp_register_script( 'intercessor-import', $js_dir . 'admin/import/import' . $suffix . '.js', 'jquery', $version, false );
	wp_register_script( 'intercessor-settings', $js_dir . 'admin/settings/index' . $suffix . '.js', 'jquery', $version, false );
	wp_register_script( 'intercessor-reports', $js_dir . 'admin/reports/index' . $suffix . '.js', 'jquery', $version, false );



    // Individual admin pages.
	$admin_pages = array(
		'requesters' => array(
			'intercessor-admin-export',
		),
		'tools-export' => [],
		'tools-import' => [],
		'notes'        => [],
		'prayers'      => array(
			'intercessor-admin-notes',
			'wp-util',
			'wp-backbone',
		),
		'settings'     => [],
		'tools'        => array(
			'intercessor-admin-tools-export'
		),
	);

	foreach ( $admin_pages as $page => $deps ) {
		wp_register_script(
			'intercessor-admin-' . $page,
			$js_dir . 'intercessor-admin-' . $page . '.js',
			array_merge( $admin_deps, $deps ),
			$version,
			false
		);
	}
}
add_action( 'admin_enqueue_scripts', 'intercessor_load_admin_scripts_styles', 100 );
