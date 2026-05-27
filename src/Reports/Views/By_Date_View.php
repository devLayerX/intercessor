<?php
/**
 * By Date report view.
 *
 * @package Intercessor
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Intercessor\Reports\Views;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\Reports\Prayer_Request_Stats;

/**
 * Renders the time-series report with an inline SVG bar chart and data table.
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class By_Date_View {

	/**
	 * Render the by-date report content.
	 *
	 * @since  1.0.0
	 * @param  string $period Active period slug.
	 * @return void
	 */
	public function render( string $period ): void {
		$stats = new Prayer_Request_Stats();

		// Use weekly grouping for year/last_year, daily otherwise.
		$use_weekly = in_array( $period, array( 'year', 'last_year', 'all_time' ), true );
		$series     = $use_weekly
			? $stats->get_weekly_series( $period )
			: $stats->get_daily_series( $period );

		$date_fmt = get_option( 'date_format' );
		?>

		<?php // ── Inline SVG bar chart ──────────────────────────────── ?>
		<div class="ipr-report-section">
			<h3 class="ipr-report-section__title">
				<?php echo $use_weekly
					? esc_html__( 'Submissions by Week', 'intercessor' )
					: esc_html__( 'Submissions by Day', 'intercessor' ); ?>
			</h3>

			<?php if ( empty( $series ) || max( array_column( $series, 'count' ) ) === 0 ) : ?>
				<p class="ipr-report-empty"><?php esc_html_e( 'No submissions in this period.', 'intercessor' ); ?></p>
			<?php else :
				$this->render_bar_chart( $series );
			endif; ?>
		</div>

		<?php // ── Data table ────────────────────────────────────────── ?>
		<div class="ipr-report-section">
			<h3 class="ipr-report-section__title"><?php esc_html_e( 'Submission Counts', 'intercessor' ); ?></h3>

			<?php if ( empty( $series ) ) : ?>
				<p class="ipr-report-empty"><?php esc_html_e( 'No data for this period.', 'intercessor' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat striped ipr-report-table">
					<thead>
						<tr>
							<th><?php echo $use_weekly ? esc_html__( 'Week', 'intercessor' ) : esc_html__( 'Date', 'intercessor' ); ?></th>
							<th class="ipr-col-num"><?php esc_html_e( 'Submissions', 'intercessor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$non_zero = array_filter( $series, static fn( $r ) => $r['count'] > 0 );
						$display  = $use_weekly ? $series : $non_zero;

						foreach ( $display as $row ) :
							$label = isset( $row['date'] )
								? mysql2date( $date_fmt, $row['date'] . ' 00:00:00' )
								: ( $row['label'] ?? '' );
						?>
							<tr<?php echo $row['count'] === 0 ? ' class="ipr-row-zero"' : ''; ?>>
								<td><?php echo esc_html( $label ); ?></td>
								<td class="ipr-col-num">
									<?php if ( $row['count'] > 0 ) : ?>
										<strong><?php echo absint( $row['count'] ); ?></strong>
									<?php else : ?>
										<span class="ipr-muted">0</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php if ( ! $use_weekly && count( $series ) !== count( $non_zero ) ) : ?>
					<p class="description" style="padding:.4rem 0;">
						<?php printf(
							/* translators: %d: number of days with zero submissions hidden */
							esc_html__( '%d day(s) with zero submissions are hidden.', 'intercessor' ),
							count( $series ) - count( $non_zero )
						); ?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render an inline SVG bar chart for the given series.
	 *
	 * @since  1.0.0
	 * @param  array<int, array{count: int}> $series Data series.
	 * @return void
	 */
	private function render_bar_chart( array $series ): void {
		$max    = max( array_column( $series, 'count' ) );
		$max    = max( 1, $max );
		$n      = count( $series );
		$w      = 800;
		$h      = 200;
		$pad_l  = 40;
		$pad_b  = 30;
		$pad_t  = 10;
		$inner_w = $w - $pad_l;
		$inner_h = $h - $pad_b - $pad_t;
		$bar_w   = max( 2, floor( $inner_w / max( 1, $n ) ) - 1 );
		$date_fmt = get_option( 'date_format' );
		?>
		<div class="ipr-chart-wrap">
			<svg viewBox="0 0 <?php echo esc_attr( $w ); ?> <?php echo esc_attr( $h ); ?>"
			     xmlns="http://www.w3.org/2000/svg"
			     aria-label="<?php esc_attr_e( 'Submissions bar chart', 'intercessor' ); ?>"
			     class="ipr-bar-chart">

				<?php // Y-axis guide lines ?>
				<?php for ( $i = 0; $i <= 4; $i++ ) :
					$y = $pad_t + ( $inner_h * ( 1 - $i / 4 ) );
					$v = round( $max * $i / 4 );
				?>
					<line x1="<?php echo esc_attr( $pad_l ); ?>" y1="<?php echo esc_attr( $y ); ?>"
					      x2="<?php echo esc_attr( $w ); ?>"      y2="<?php echo esc_attr( $y ); ?>"
					      class="ipr-chart-guideline"/>
					<text x="<?php echo esc_attr( $pad_l - 4 ); ?>" y="<?php echo esc_attr( $y + 4 ); ?>"
					      class="ipr-chart-label" text-anchor="end"><?php echo absint( $v ); ?></text>
				<?php endfor; ?>

				<?php // Bars ?>
				<?php foreach ( $series as $i => $row ) :
					$x      = $pad_l + ( $i * ( $inner_w / $n ) );
					$bar_h  = $inner_h * ( $row['count'] / $max );
					$y      = $pad_t + $inner_h - $bar_h;
					$label  = isset( $row['date'] )
						? mysql2date( $date_fmt, $row['date'] . ' 00:00:00' )
						: ( $row['label'] ?? '' );
					$title  = esc_attr( $label . ': ' . $row['count'] );
				?>
					<g class="ipr-bar" role="graphics-symbol">
						<title><?php echo esc_html( $title ); ?></title>
						<rect x="<?php echo esc_attr( round( $x ) ); ?>"
						      y="<?php echo esc_attr( round( $y ) ); ?>"
						      width="<?php echo esc_attr( $bar_w ); ?>"
						      height="<?php echo esc_attr( round( $bar_h ) ); ?>"
						      class="ipr-bar__rect <?php echo $row['count'] === 0 ? 'ipr-bar__rect--zero' : ''; ?>"/>
					</g>
				<?php endforeach; ?>

			</svg>
		</div>
		<?php
	}
}
