<?php
/**
 * Intercessor Deactivator Class
 *
 * @package    Intercessor
 * @subpackage classes/Deactivator
 * @copyright  Copyright (c) 2019, Victor Aigbeghian
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      0.9.5
 */

namespace Intercessor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Deactivator Class
 *
 * This class handles actions when the plugin is deactivated.
 *
 * @since 0.9.5
 */
class Deactivator {

	/**
	 * Runs the actions and filters during plugin deactivation.
	 *
	 * @since 0.9.5
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Deactivate our cron job.
		$timestamp = wp_next_scheduled( 'intercessor_notify_requester' );
		\wp_unschedule_event( $timestamp, 'intercessor_notify_requester' );

		// Flush rewrite rules.
		\flush_rewrite_rules( true );
	}

}
