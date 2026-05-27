<?php
/**
 * By Status report view.
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
 * Renders the status breakdown report with a visual bar chart and data table.
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class By_Status_View {

	/**
	 * Render the by-status report content.
	 *
	 * @since  1.0.0
	 * @param  string $period Active period slug.
	 * @return void
	 */
	public function render( string $period ): void {
		$stats     = new Prayer_Request_Stats();
		$breakdown = $stats->get_status_breakdown( $period );
		$total     = max( 1, array_sum( $breakdown ) );

		$status_meta = array(
			'pending'  => array( 'label' => __( 'Pending',  'intercessor' ), 'color' => '#d97706', 'bg' => '#fef3c7' ),
			'approved' => array( 'label' => __( 'Approved', 'intercessor' ), 'color' => '#16a34a', 'bg' => '#dcfce7' ),
			'rejected' => array( 'label' => __( 'Rejected', 'intercessor' ), 'color' => '#dc2626', 'bg' => '#fee2e2' ),
			'archived' => array( 'label' => __( 'Archived', 'intercessor' ), 'color' => '#6b7280', 'bg' => '#f3f4f6' ),
			'private'  => array( 'label' => __( 'Private',  'intercessor' ), 'color' => '#7c3aed', 'bg' => '#ede9fe' ),
		);
		?>

		<?php // ── Stacked bar chart ─────────────────────────────────── ?>
		<div class="ipr-report-section">
			<h3 class="ipr-report-section__title"><?php esc_html_e( 'Status Distribution', 'intercessor' ); ?></h3>

			<div class="ipr-stacked-bar" role="img" aria-label="<?php esc_attr_e( 'Prayer status distribution bar chart', 'intercessor' ); ?>">
				<?php foreach ( $status_meta as $status => $meta ) :
					$count = $breakdown[ $status ];
					$pct   = round( ( $count / $total ) * 100, 1 );
					if ( $pct <= 0 ) continue;
				?>
					<div class="ipr-stacked-bar__segment"
					     style="width:<?php echo esc_attr( $pct ); ?>%;background:<?php echo esc_attr( $meta['color'] ); ?>;"
					     title="<?php echo esc_attr( "{$meta['label']}: {$count} ({$pct}%)" ); ?>">
					</div>
				<?php endforeach; ?>
			</div>

			<?php // Legend ?>
			<div class="ipr-stacked-bar__legend">
				<?php foreach ( $status_meta as $status => $meta ) :
					$count = $breakdown[ $status ];
					$pct   = round( ( $count / $total ) * 100, 1 );
				?>
					<span class="ipr-legend-item">
						<span class="ipr-legend-dot" style="background:<?php echo esc_attr( $meta['color'] ); ?>;"></span>
						<?php echo esc_html( $meta['label'] ); ?>
						<strong><?php echo esc_html( number_format_i18n( $count ) ); ?></strong>
						<span class="ipr-legend-pct"><?php echo esc_html( $pct . '%' ); ?></span>
					</span>
				<?php endforeach; ?>
			</div>
		</div>

		<?php // ── Data table ────────────────────────────────────────── ?>
		<div class="ipr-report-section">
			<h3 class="ipr-report-section__title"><?php esc_html_e( 'Status Breakdown', 'intercessor' ); ?></h3>
			<table class="wp-list-table widefat striped ipr-report-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Status', 'intercessor' ); ?></th>
						<th class="ipr-col-num"><?php esc_html_e( 'Count', 'intercessor' ); ?></th>
						<th class="ipr-col-num"><?php esc_html_e( '% of Total', 'intercessor' ); ?></th>
						<th><?php esc_html_e( 'Quick Filter', 'intercessor' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $status_meta as $status => $meta ) :
						$count   = $breakdown[ $status ];
						$pct     = round( ( $count / $total ) * 100, 1 );
						$filter_url = add_query_arg(
							array( 'page' => 'intercessor-requests', 'status' => $status ),
							admin_url( 'admin.php' )
						);
					?>
						<tr>
							<td>
								<span class="intercessor-status <?php echo esc_attr( $status ); ?>"
								      style="background:<?php echo esc_attr( $meta['bg'] ); ?>;color:<?php echo esc_attr( $meta['color'] ); ?>;">
									<?php echo esc_html( $meta['label'] ); ?>
								</span>
							</td>
							<td class="ipr-col-num"><strong><?php echo esc_html( number_format_i18n( $count ) ); ?></strong></td>
							<td class="ipr-col-num"><?php echo esc_html( $pct . '%' ); ?></td>
							<td>
								<?php if ( $count > 0 ) : ?>
									<a href="<?php echo esc_url( $filter_url ); ?>" class="button button-small">
										<?php esc_html_e( 'View', 'intercessor' ); ?>
									</a>
								<?php else : ?>
									<span class="ipr-muted">—</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr class="ipr-report-total-row">
						<td><strong><?php esc_html_e( 'Total', 'intercessor' ); ?></strong></td>
						<td class="ipr-col-num"><strong><?php echo esc_html( number_format_i18n( array_sum( $breakdown ) ) ); ?></strong></td>
						<td class="ipr-col-num">100%</td>
						<td></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
