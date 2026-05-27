<?php
/**
 * WordPress dashboard widget for Intercessor prayer stats.
 *
 * @package Intercessor
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Intercessor\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\Database\Query\Prayer_Request_Query;

/**
 * Registers and renders the Intercessor dashboard widget.
 *
 * The widget shows:
 *   - A day-of-week greeting personalised with the current user's name.
 *   - Total pending prayers (linked to the moderation queue when > 0).
 *   - Approved prayer counts broken down by today / week / month / year.
 *   - A compact progress bar showing today's share of this year's total.
 *   - Quick-action links to the prayer requests and pending queues.
 *
 * Counts are cached in a short-lived transient (5 minutes) so that
 * repeated dashboard loads do not hammer the database.
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class Dashboard_Widget {

	/**
	 * Widget ID used by wp_add_dashboard_widget().
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private const WIDGET_ID = 'intercessor_prayer_stats';

	/**
	 * Transient key for cached stats.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private const TRANSIENT_KEY = 'intercessor_dashboard_stats';

	/**
	 * Transient TTL in seconds (5 minutes).
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	private const TRANSIENT_TTL = 300;

	// ── Registration ─────────────────────────────────────────────────────────

	/**
	 * Register the dashboard widget and the asset enqueue hook.
	 *
	 * Called from Admin_Loader::register(). Hooks are only added when the
	 * current user holds the `edit_prayers` capability.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		if ( ! current_user_can( 'edit_prayers' ) ) {
			return;
		}

		add_action( 'wp_dashboard_setup',   array( $this, 'add_widget' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// Bust the stats transient whenever a prayer status changes so the
		// widget reflects moderation actions without waiting for TTL expiry.
		add_action( 'intercessor_prayer_status_updated', array( $this, 'bust_cache' ) );
	}

	/**
	 * Register the widget with the WordPress dashboard.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function add_widget(): void {
		wp_add_dashboard_widget(
			self::WIDGET_ID,
			__( 'Intercessor — Prayer Overview', 'intercessor' ),
			array( $this, 'render' )
		);
	}

	/**
	 * Enqueue the widget stylesheet on the WordPress dashboard only.
	 *
	 * @since  1.0.0
	 * @param  string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_styles( string $hook ): void {
		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'intercessor-dashboard',
			INTERCESSOR_URL . 'assets/css/admin.css',
			array(),
			INTERCESSOR_VERSION
		);
	}

	// ── Cache management ─────────────────────────────────────────────────────

	/**
	 * Delete the cached stats transient.
	 *
	 * Hooked to `intercessor_prayer_status_updated` so the widget reflects
	 * moderation actions immediately rather than waiting for the TTL.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function bust_cache(): void {
		delete_transient( self::TRANSIENT_KEY );
	}

	// ── Data layer ────────────────────────────────────────────────────────────

	/**
	 * Fetch all stat counts, reading from a transient cache where possible.
	 *
	 * Returns an associative array with the keys:
	 *   pending      int   Total prayers awaiting moderation.
	 *   today        int   Approved prayers created today.
	 *   week         int   Approved prayers created this calendar week.
	 *   month        int   Approved prayers created this calendar month.
	 *   year         int   Approved prayers created this calendar year.
	 *   total        int   All approved prayers (no date filter).
	 *
	 * @since  1.0.0
	 * @return array{pending:int,today:int,week:int,month:int,year:int,total:int}
	 */
	private function get_stats(): array {
		$cached = get_transient( self::TRANSIENT_KEY );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$query = new Prayer_Request_Query();

		$stats = array(
			'pending' => $query->count_pending(),
			'today'   => $query->count_approved_for_period( 'today' ),
			'week'    => $query->count_approved_for_period( 'week' ),
			'month'   => $query->count_approved_for_period( 'month' ),
			'year'    => $query->count_approved_for_period( 'year' ),
			'total'   => $query->count_items( array( 'status' => 'approved' ) ),
		);

		set_transient( self::TRANSIENT_KEY, $stats, self::TRANSIENT_TTL );

		return $stats;
	}

	// ── Rendering ─────────────────────────────────────────────────────────────

	/**
	 * Output the dashboard widget HTML.
	 *
	 * Called by WordPress as the widget callback. All output is escaped.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render(): void {
		$stats     = $this->get_stats();
		$user      = wp_get_current_user();
		$day_name  = wp_date( 'l' ); // Day name in site timezone.
		$all_url   = admin_url( 'admin.php?page=intercessor-requests' );
		$pend_url  = admin_url( 'admin.php?page=intercessor-requests&status=pending' );

		// Progress bar: today as a share of this year's total.
		$year_total   = max( 1, $stats['year'] ); // avoid division by zero.
		$today_pct    = min( 100, (int) round( ( $stats['today'] / $year_total ) * 100 ) );
		?>
		<div class="ipr-widget">

			<?php // ── Greeting row ─────────────────────────────────────── ?>
			<div class="ipr-widget__greeting">
				<span class="ipr-widget__day">
					<?php
					printf(
						/* translators: %s: day of the week, e.g. "Monday" */
						esc_html__( 'Happy %s', 'intercessor' ),
						'<strong>' . esc_html( $day_name ) . '</strong>'
					);
					?>
				</span>
				<span class="ipr-widget__user">
					<?php echo esc_html( $user->display_name ); ?>
				</span>
			</div>

			<?php // ── Pending banner ───────────────────────────────────── ?>
			<?php if ( $stats['pending'] > 0 ) : ?>
				<div class="ipr-widget__pending ipr-widget__pending--has-items">
					<span class="ipr-widget__pending-icon dashicons dashicons-clock"></span>
					<span class="ipr-widget__pending-label">
						<?php esc_html_e( 'Pending review', 'intercessor' ); ?>
					</span>
					<a href="<?php echo esc_url( $pend_url ); ?>"
					   class="ipr-widget__pending-count"
					   aria-label="<?php echo esc_attr(
					   		sprintf(
					   			/* translators: %d: number of pending prayers */
					   			_n( '%d prayer pending', '%d prayers pending', $stats['pending'], 'intercessor' ),
					   			$stats['pending']
					   		)
					   ); ?>">
						<?php echo absint( $stats['pending'] ); ?>
					</a>
				</div>
			<?php else : ?>
				<div class="ipr-widget__pending ipr-widget__pending--clear">
					<span class="ipr-widget__pending-icon dashicons dashicons-yes-alt"></span>
					<span class="ipr-widget__pending-label">
						<?php esc_html_e( 'All caught up — no pending prayers', 'intercessor' ); ?>
					</span>
				</div>
			<?php endif; ?>

			<?php // ── Approved stats grid ──────────────────────────────── ?>
			<div class="ipr-widget__stats">

				<div class="ipr-widget__stat ipr-widget__stat--today">
					<span class="ipr-widget__stat-number"><?php echo absint( $stats['today'] ); ?></span>
					<span class="ipr-widget__stat-label"><?php esc_html_e( 'Today', 'intercessor' ); ?></span>
				</div>

				<div class="ipr-widget__stat ipr-widget__stat--week">
					<span class="ipr-widget__stat-number"><?php echo absint( $stats['week'] ); ?></span>
					<span class="ipr-widget__stat-label"><?php esc_html_e( 'This week', 'intercessor' ); ?></span>
				</div>

				<div class="ipr-widget__stat ipr-widget__stat--month">
					<span class="ipr-widget__stat-number"><?php echo absint( $stats['month'] ); ?></span>
					<span class="ipr-widget__stat-label"><?php esc_html_e( 'This month', 'intercessor' ); ?></span>
				</div>

				<div class="ipr-widget__stat ipr-widget__stat--year">
					<span class="ipr-widget__stat-number"><?php echo absint( $stats['year'] ); ?></span>
					<span class="ipr-widget__stat-label"><?php esc_html_e( 'This year', 'intercessor' ); ?></span>
				</div>

			</div>

			<?php // ── Today-vs-year progress bar ───────────────────────── ?>
			<div class="ipr-widget__progress" title="<?php echo esc_attr(
				sprintf(
					/* translators: 1: today count, 2: year count */
					__( '%1$d of %2$d approved this year received today', 'intercessor' ),
					$stats['today'],
					$stats['year']
				)
			); ?>">
				<div class="ipr-widget__progress-track">
					<div class="ipr-widget__progress-fill"
					     style="width:<?php echo absint( $today_pct ); ?>%;"
					     role="progressbar"
					     aria-valuenow="<?php echo absint( $today_pct ); ?>"
					     aria-valuemin="0"
					     aria-valuemax="100">
					</div>
				</div>
				<span class="ipr-widget__progress-label">
					<?php
					printf(
						/* translators: %d: percentage of year's prayers received today */
						esc_html__( '%d%% of this year\'s approved prayers are from today', 'intercessor' ),
						absint( $today_pct )
					);
					?>
				</span>
			</div>

			<?php // ── Footer links ─────────────────────────────────────── ?>
			<div class="ipr-widget__footer">
				<a href="<?php echo esc_url( $all_url ); ?>" class="ipr-widget__link">
					<?php esc_html_e( 'View all prayers', 'intercessor' ); ?>
					<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
				</a>

				<?php if ( $stats['pending'] > 0 ) : ?>
					<a href="<?php echo esc_url( $pend_url ); ?>" class="ipr-widget__link ipr-widget__link--pending">
						<?php
						printf(
							/* translators: %d: number of pending prayers */
							esc_html( _n( 'Review %d pending', 'Review %d pending', $stats['pending'], 'intercessor' ) ),
							absint( $stats['pending'] )
						);
						?>
						<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
					</a>
				<?php endif; ?>
			</div>

		</div>
		<?php
	}
}
