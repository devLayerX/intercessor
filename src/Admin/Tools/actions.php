<?php
/**
 * Exports Actions
 *
 * These are actions related to exporting data from Intercessor.
 *
 * @package     Intercessor
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register the recount batch processor
 * @since  0.9.5
 */
function intercessor_register_batch_recount_prayers_tool() {
	add_action( 'intercessor_batch_export_class_include', 'intercessor_include_recount_prayers_tool_batch_processer', 10, 1 );
}
add_action( 'intercessor_register_batch_exporter', 'intercessor_register_batch_recount_prayers_tool', 10 );

/**
 * Loads the tools batch processing class for recounting prayers
 *
 * @since  0.9.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function intercessor_include_recount_prayers_tool_batch_processer( $class ) {

	if ( 'IPR_Tools_Recount_Prayer_Requests' === $class ) {
		require_once INTERCESSOR_DIR . 'src/admin/tools/class-ipr-tools-recount-prayers.php';
	}

}

/**
 * Register the recount all stats batch processor
 * @since  0.9.5
 */
function intercessor_register_batch_recount_all_tool() {
	add_action( 'intercessor_batch_export_class_include', 'intercessor_include_recount_all_tool_batch_processer', 10, 1 );
}
add_action( 'intercessor_register_batch_exporter', 'intercessor_register_batch_recount_all_tool', 10 );

/**
 * Loads the tools batch processing class for recounting all stats
 *
 * @since  0.9.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function intercessor_include_recount_all_tool_batch_processer( $class ) {

	if ( 'IPR_Tools_Recount_All_Stats' === $class ) {
		require_once INTERCESSOR_DIR . 'src/admin/tools/class-ipr-tools-recount-all-stats.php';
	}

}

/**
 * Register the reset stats batch processor
 * @since  0.9.5
 */
function intercessor_register_batch_reset_tool() {
	add_action( 'intercessor_batch_export_class_include', 'intercessor_include_reset_tool_batch_processer', 10, 1 );
}
add_action( 'intercessor_register_batch_exporter', 'intercessor_register_batch_reset_tool', 10 );

/**
 * Loads the tools batch processing class for resetting store and product earnings
 *
 * @since  0.9.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function intercessor_include_reset_tool_batch_processer( $class ) {

	if ( 'IPR_Tools_Reset_Stats' === $class ) {
		require_once INTERCESSOR_DIR . 'src/admin/tools/class-intercessor-tools-reset-stats.php';
	}

}

/**
 * Register the reset requester stats batch processor
 * @since  0.9.5
 */
function intercessor_register_batch_requester_recount_tool() {
	add_action( 'intercessor_batch_export_class_include', 'intercessor_include_requester_recount_tool_batch_processer', 10, 1 );
}
add_action( 'intercessor_register_batch_exporter', 'intercessor_register_batch_requester_recount_tool', 10 );

/**
 * Loads the tools batch processing class for resetting all requester stats
 *
 * @since  0.9.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function intercessor_include_requester_recount_tool_batch_processer( $class ) {

	if ( 'IPR_Tools_Recount_Requester_Stats' === $class ) {
		require_once INTERCESSOR_DIR . 'src/admin/tools/class-intercessor-tools-recount-requester-stats.php';
	}

}