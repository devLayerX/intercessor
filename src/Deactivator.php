<?php
/**
 * Plugin deactivation handler.
 *
 * @package Intercessor
 * @since   1.0.0
 */
declare(strict_types=1);

namespace Intercessor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\Util\Cron_Handler;

/**
 * Handles tasks that run on plugin deactivation.
 *
 * Tables and data are intentionally retained on deactivation.
 * Data removal happens only on uninstall (see uninstall.php) and only
 * when the administrator has enabled the "Delete All Data on Uninstall"
 * setting.
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class Deactivator {

	/**
	 * Execute all deactivation tasks.
	 *
	 * Currently flushes rewrite rules so any endpoints registered by
	 * the plugin are removed from the rewrite table immediately.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function deactivate(): void {
		// Remove the scheduled prayer-count notification event.
		Cron_Handler::unschedule();

		flush_rewrite_rules();
	}
}
