<?php
/**
 * Dashboard Widgets
 *
 * @package     Intercessor
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-0.9.5.php GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Registers the dashboard widgets
 *
 * @since  0.9.5
 * @return void
 */
function intercessor_register_dashboard_widgets() {
	if ( current_user_can( apply_filters( 'intercessor_dashboard_stats_cap', 'edit_prayers' ) ) ) {
		wp_add_dashboard_widget(
			'intercessor_request_summary',
			esc_html__( 'Intercessor Requests Summary', 'intercessor' ),
			'intercessor_requests_dashboard_widget'
		);
	}
}
add_action( 'wp_dashboard_setup', 'intercessor_register_dashboard_widgets', 10 );

/**
 * Gets the sales earnings/count data for the dashboard widget.
 *
 * @since 1.0.0
 * @return array
 */
function intercessor_dashboard_widget_data() {
	$data   = [];
	$ranges = [ 'this_month', 'last_month', 'today', 'total' ];
	foreach ( $ranges as $range ) {
		$args = [
			'range'  => $range,
			'output' => 'formatted',
		];

		if ( 'total' === $range ) {
			unset( $args['range'] );
		}

		// Remove filters so that deprecation notices are not unnecessarily logged outside of reports.
		remove_all_filters( 'intercessor_report_views' );

		$stats          = new Intercessor\Stats( $args );
		$data[ $range ] = [
			'count' => $stats->get_prayer_count(),
		];
	}

	return $data;
}

/**
 * Load the prayer request dashboard widget
 *
 * @since 0.9.5
 * @return void
 */
function intercessor_requests_dashboard_widget() {
	if ( ! current_user_can( apply_filters( 'intercessor_dashboard_stats_cap', 'view_prayer_reports' ) ) ) {
		die();
	}

	$stats = new Intercessor\Stats();
	$data  = intercessor_dashboard_widget_data();

	// Weekly count.
	$weekly       = $stats->get_prayer_count( 0, 'this_week' );
	$weekly_count = ! empty( $weekly ) ? $weekly : 0;

	// Monthly counts.
	$monthly       = $stats->get_prayer_requests( 0, 'this_month' );
	$monthly_count = ! empty( $monthly ) ? $monthly : 0;

	// Last month prayer count.
    $last_month       = $stats->get_prayer_requests( 0, 'last_month' );
	$last_month_count = ! empty( $last_month ) ? $last_month : 0;

	// Yearly count.
    $this_year    = $stats->get_prayer_requests( 0, 'this_year' );
	$yearly_count = ! empty( $this_year ) ? $this_year : 0;
	?>

	<ul class="intercessor_dashboard_list">

		<div class="intercessor-dashboard-daily intercessor-clearfix">
			<h3 class="intercessor-dashboard-date-today">
				<?php echo esc_attr( date_i18n( esc_html_x( 'F j, Y', 'dashboard widget', 'intercessor' ) ) ); ?>
			</h3>

			<p class="intercessor-dashboard-blessed-day">
			<?php
				printf(
				/* translators: %s: day of the week */
					esc_html__( 'Happy %s!', 'intercessor' ),
					esc_attr( date_i18n( 'l', current_time( 'timestamp' ) ) )
				);
			?>
			</p>

			<li class="intercessor-dashboard-today-prayers">
			<?php
                $today        = $stats->get_prayer_requests( 0, 'today', false, 'active' );
				$active_today = ! empty( $today ) ? $today : 0;

				esc_html(
					printf(
						/* translators: %s: daily prayer request count */
						_n( '%s active prayer today', '%s active prayers today', 'intercessor' ),
						$active_today
					)
				);
			?>
			</li>

			<li class="intercessor-dashboard-today-prayers">
				
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=intercessor-prayers&status=pending' ) ); ?>">
                    <?php
                    $pending       = $stats->get_prayer_requests( 0, 'today', false, 'pending' );
                    $pending_today = ! empty( $pending ) ? $pending : 0;

                    esc_html(
                        printf(
                            /* translators: %s: daily prayer request count */
                            _n( '%s pending prayer today', '%s pending prayers today', 'intercessor' ),
                            '<strong>' . $pending_today . '</strong>'
                        )
                    );
                ?>
                </a>
			</li>

		</div>

		<li class="weekly-prayers">
			<span>
			<?php
				esc_html(
					printf(
						/* translators: %s: prayer count */
						_n( '<strong>%s active prayer</strong> This Week', '<strong>%s active prayers</strong> This Week', 'intercessor' ),
						$weekly_count
					)
				);
			?>
			</span>
		</li>

		<li class="monthly-prayers">
			<span>
			<?php
				esc_html(
					printf(
						/* translators: %s: prayer count */
						_n( '<strong>%s active prayer</strong> This Month', '<strong>%s active prayers</strong> This Month', 'intercessor' ),
						$monthly_count
					)
				);
			?>
			</span>
		</li>

		<li class="last-month-prayers">
			<span>
			<?php
				esc_html(
					printf(
						/* translators: %s: prayer count */
						_n( '<strong>%s active prayer</strong> Last Month', '<strong>%s active prayers</strong> Last Month', 'intercessor' ),
						$last_month_count
					)
				);
			?>
			</span>
		</li>

		<li class="yearly-prayers">
			<span>
			<?php
				esc_html(
					printf(
						/* translators: %s: prayer count */
						_n( '<strong>%s active prayer</strong> This Year', '<strong>%s active prayers</strong> This Year', 'intercessor' ),
						$yearly_count
					)
				);
			?>
			</span>
		</li>

	</ul>
	<?php
}
