<?php
/**
 * Overview report view.
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
 * Renders the overview report: summary stat cards and top requesters.
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class Overview_View {

	/**
	 * Render the overview report content.
	 *
	 * @since  1.0.0
	 * @param  string $period Active period slug.
	 * @return void
	 */
	public function render( string $period ): void {
		$stats    = new Prayer_Request_Stats();
		$overview = $stats->get_overview( $period );
		$top      = $stats->get_top_requesters( 5, $period );

		$status_config = array(
			'total'    => array( 'label' => __( 'Total',    'intercessor' ), 'color' => '#2271b1', 'icon' => 'dashicons-list-view'   ),
			'pending'  => array( 'label' => __( 'Pending',  'intercessor' ), 'color' => '#b45309', 'icon' => 'dashicons-clock'       ),
			'approved' => array( 'label' => __( 'Approved', 'intercessor' ), 'color' => '#0a3622', 'icon' => 'dashicons-yes-alt'     ),
			'rejected' => array( 'label' => __( 'Rejected', 'intercessor' ), 'color' => '#6b0000', 'icon' => 'dashicons-dismiss'     ),
			'archived' => array( 'label' => __( 'Archived', 'intercessor' ), 'color' => '#383d41', 'icon' => 'dashicons-archive'     ),
			'private'  => array( 'label' => __( 'Private',  'intercessor' ), 'color' => '#5a1e82', 'icon' => 'dashicons-lock'        ),
		);
		?>

		<?php // ── Summary stat cards ───────────────────────────────── ?>
		<div class="ipr-report-card-grid">
			<?php foreach ( $status_config as $key => $cfg ) : ?>
				<div class="ipr-report-stat-card" style="--card-accent:<?php echo esc_attr( $cfg['color'] ); ?>;">
					<span class="dashicons <?php echo esc_attr( $cfg['icon'] ); ?> ipr-report-stat-icon" aria-hidden="true"></span>
					<span class="ipr-report-stat-number"><?php echo esc_html( number_format_i18n( $overview[ $key ] ) ); ?></span>
					<span class="ipr-report-stat-label"><?php echo esc_html( $cfg['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>

		<?php // ── Top requesters table ─────────────────────────────── ?>
		<div class="ipr-report-section">
			<h3 class="ipr-report-section__title">
				<?php esc_html_e( 'Top Requesters', 'intercessor' ); ?>
			</h3>

			<?php if ( empty( $top ) ) : ?>
				<p class="ipr-report-empty"><?php esc_html_e( 'No data for this period.', 'intercessor' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat striped ipr-report-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Requester', 'intercessor' ); ?></th>
							<th><?php esc_html_e( 'Email', 'intercessor' ); ?></th>
							<th class="ipr-col-num"><?php esc_html_e( 'Requests', 'intercessor' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'intercessor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $top as $row ) :
							$detail_url = add_query_arg(
								array( 'page' => 'intercessor-requesters', 'requester_id' => $row['requester_id'] ),
								admin_url( 'admin.php' )
							);
						?>
							<tr>
								<td>
									<a href="<?php echo esc_url( $detail_url ); ?>">
										<?php echo esc_html( $row['name'] ); ?>
									</a>
								</td>
								<td><?php echo esc_html( $row['email'] ); ?></td>
								<td class="ipr-col-num">
									<strong><?php echo absint( $row['count'] ); ?></strong>
								</td>
								<td>
									<a href="<?php echo esc_url( $detail_url ); ?>" class="button button-small">
										<?php esc_html_e( 'View', 'intercessor' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}
}
