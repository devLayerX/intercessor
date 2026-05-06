<?php
/**
 * Reports admin page controller.
 *
 * @package Intercessor
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Intercessor\Reports;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\Reports\Views\Overview_View;
use Intercessor\Reports\Views\By_Status_View;
use Intercessor\Reports\Views\By_Date_View;
use Intercessor\Reports\Views\Activity_View;

/**
 * Registers the Reports submenu page and dispatches rendering to report views.
 *
 * Modelled views are registered in a filterable
 * array and dispatched by a URL 'view' parameter. Each view is a callable
 * (or an instance implementing render()) registered under a slug. Third-party
 * code can add custom views via the 'intercessor_report_views' filter.
 *
 * URL scheme:
 *   ?page=intercessor-reports                   → default view (overview)
 *   ?page=intercessor-reports&view=by_status    → status breakdown
 *   ?page=intercessor-reports&view=by_date      → time-series table
 *   ?page=intercessor-reports&view=activity     → recent activity log
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class Reports_Page {

	/** @var string Default view slug. */
	public const DEFAULT_VIEW = 'overview';

	/** @var string Admin page slug. */
	public const PAGE_SLUG = 'intercessor-reports';

	// ── Registration ──────────────────────────────────────────────────────────

	/**
	 * Register the admin_menu callback for the Reports submenu.
	 *
	 * Called from Admin_Loader::register(). Returns the add_submenu_page
	 * hook suffix so callers can conditionally enqueue assets.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 15 );
	}

	/**
	 * Add the Reports submenu under the Intercessor parent menu.
	 *
	 * Priority 15 ensures it appears after Prayer Requests (default 10) but
	 * before Tools (default 10, registered later in the same callback).
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function add_submenu(): void {
		add_submenu_page(
			'intercessor',
			__( 'Prayer Reports', 'intercessor' ),
			__( 'Reports', 'intercessor' ),
			'view_prayer_reports',
			self::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	// ── View registry ─────────────────────────────────────────────────────────

	/**
	 * Return the registered report views.
	 *
	 * Each entry maps a slug to a display label and a renderer. Renderers
	 * may be any callable or an object with a render() method. Third-party
	 * code extends this via the 'intercessor_report_views' filter.
	 *
	 * @since  1.0.0
	 * @return array<string, array{label: string, renderer: callable|object}>
	 */
	public static function get_views(): array {
		$views = array(
			'overview'  => array(
				'label'    => __( 'Overview', 'intercessor' ),
				'renderer' => new Overview_View(),
			),
			'by_status' => array(
				'label'    => __( 'By Status', 'intercessor' ),
				'renderer' => new By_Status_View(),
			),
			'by_date'   => array(
				'label'    => __( 'By Date', 'intercessor' ),
				'renderer' => new By_Date_View(),
			),
			'activity'  => array(
				'label'    => __( 'Activity', 'intercessor' ),
				'renderer' => new Activity_View(),
			),
		);

		/**
		 * Filter the registered report views.
		 *
		 * @since 1.0.0
		 * @param array $views Map of slug → ['label', 'renderer'].
		 */
		return (array) apply_filters( 'intercessor_report_views', $views );
	}

	// ── Rendering ─────────────────────────────────────────────────────────────

	/**
	 * Render the full Reports admin page.
	 *
	 * Validates the current view, renders the nav-tab-wrapper, period
	 * selector, and then delegates to the active view's renderer.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'view_prayer_reports' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'intercessor' ) );
		}

		$views       = self::get_views();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : self::DEFAULT_VIEW;

		if ( ! array_key_exists( $active_view, $views ) ) {
			$active_view = self::DEFAULT_VIEW;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$period       = isset( $_GET['period'] ) ? sanitize_key( $_GET['period'] ) : 'month';
		$valid_periods = array( 'today', 'yesterday', 'week', 'last_week', 'month', 'last_month', 'year', 'last_year', 'all_time' );

		if ( ! in_array( $period, $valid_periods, true ) ) {
			$period = 'month';
		}

		require INTERCESSOR_DIR . 'templates/admin/reports.php';
	}
}
