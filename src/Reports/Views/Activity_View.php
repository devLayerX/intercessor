<?php
/**
 * Activity report view.
 *
 * @package Intercessor
 * @since   1.0.2
 */

declare(strict_types=1);

namespace Intercessor\Reports\Views;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\Database\Query\Prayer_Request_Query;
use Intercessor\Database\Query\Requester_Query;
use Intercessor\Database\Queries\Date;
use Intercessor\Loader;

/**
 * Renders a paginated, filterable activity log of recent prayer requests.
 *
 * @since   1.0.2
 * @package Intercessor
 */
final class Activity_View {

	/** @var int Rows per page. */
	private const PER_PAGE = 25;

	/** @var \wpdb WordPress database object, for direct queries. */
	public $db;

	/**
	 * Render the activity log.
	 *
	 * @since  1.0.2
	 * @param  string $period Active period slug.
	 * @return void
	 */
	public function render( string $period ): void {

		$this->db = Loader::instance()->get_db();
		$table   = esc_sql( $this->db->prefix . 'intercessor_prayer_requests' ); // Safe identifier.
		$req_tbl = esc_sql( $this->db->prefix . 'intercessor_requesters' ); // Safe identifier.

		// Normalize input — these are read-only pagination/filter params; no state is modified.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$paged = isset( $_GET['paged'] )
			? max( 1, (int) $_GET['paged'] )
			: 1;

		$filter_status = isset( $_GET['filter_status'] )
			? sanitize_key( wp_unslash( $_GET['filter_status'] ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Build date clause.
		$date_sql = '';
		if ( $period !== 'all_time' ) {
			$date_obj = Date::for_period( $period === 'all_time' ? 'year' : $period );
			$date_sql = $date_obj->get_sql_clauses()['where'] ?? '';
		}

		// Build main WHERE.
		$where = "WHERE 1=1 {$date_sql}";

		if ( $filter_status !== '' ) {
			$where .= $this->db->prepare( ' AND pr.status = %s', $filter_status );
		}

		// Total count query (no limits).
		$sql_total = "
			SELECT COUNT(*)
			FROM {$table} pr
			{$where}
		";

		$total = (int) $this->db->get_var( $sql_total );

		// PAGINATED QUERY.
		$offset = ( $paged - 1 ) * self::PER_PAGE;

		$sql_rows = "
			SELECT
				pr.id,
				pr.subject,
				pr.status,
				pr.is_anonymous,
				pr.date_created,
				r.first_name,
				r.last_name,
				r.name AS requester_name_legacy,
				r.email AS requester_email
			FROM {$table} pr
			LEFT JOIN {$req_tbl} r ON r.id = pr.requester_id
			{$where}
			ORDER BY pr.date_created DESC
		";

		$sql_rows .= $this->db->prepare( ' LIMIT %d OFFSET %d', self::PER_PAGE, $offset );

		$rows = $this->db->get_results( $sql_rows, ARRAY_A );

		// ─────────────────────────────────────────
		// META
		// ─────────────────────────────────────────

		$total_pages = (int) ceil( $total / self::PER_PAGE );

		$date_fmt = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		$base_url = add_query_arg(
			array(
				'page'   => 'intercessor-reports',
				'view'   => 'activity',
				'period' => $period,
			),
			admin_url( 'admin.php' )
		);

		$statuses      = array( '', 'pending', 'approved', 'rejected', 'archived', 'private' );
		$status_labels = array(
			''         => esc_html__( 'All statuses', 'intercessor' ),
			'pending'  => esc_html__( 'Pending',  'intercessor' ),
			'approved' => esc_html__( 'Approved', 'intercessor' ),
			'rejected' => esc_html__( 'Rejected', 'intercessor' ),
			'archived' => esc_html__( 'Archived', 'intercessor' ),
			'private'  => esc_html__( 'Private',  'intercessor' ),
		);
		?>

		<div class="ipr-report-section">
			<div class="ipr-report-toolbar">
				<h3 class="ipr-report-section__title" style="margin:0;">
					// translators: %d: total number of prayer requests matching current filter
					<?php printf(
						/* translators: %d: total count */
						esc_html( _n( '%d Prayer Request', '%d Prayer Requests', $total, 'intercessor' ) ),
						esc_attr( $total )
					); ?>
				</h3>

				<?php // ── Status filter select ────────────────── ?>
				<form method="get" class="ipr-report-filter-form">
					<input type="hidden" name="page"   value="intercessor-reports">
					<input type="hidden" name="view"   value="activity">
					<input type="hidden" name="period" value="<?php echo esc_attr( $period ); ?>">

					<label for="filter_status" class="screen-reader-text">
						<?php esc_html_e( 'Filter by status', 'intercessor' ); ?>
					</label>
					<select name="filter_status" id="filter_status" onchange="this.form.submit()">
						<?php foreach ( $status_labels as $val => $lbl ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>"
							        <?php selected( $filter_status, $val ); ?>>
								<?php echo esc_html( $lbl ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</form>
			</div>

			<?php if ( empty( $rows ) ) : ?>
				<p class="ipr-report-empty"><?php esc_html_e( 'No prayer requests found for this period.', 'intercessor' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat striped ipr-report-table">
					<thead>
						<tr>
							<th style="width:40px;">ID</th>
							<th><?php esc_html_e( 'Subject', 'intercessor' ); ?></th>
							<th><?php esc_html_e( 'Requester', 'intercessor' ); ?></th>
							<th><?php esc_html_e( 'Status', 'intercessor' ); ?></th>
							<th><?php esc_html_e( 'Submitted', 'intercessor' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'intercessor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row ) :
							$detail_url = add_query_arg(
								array( 'page' => 'intercessor-requests', 'view' => $row['id'] ),
								admin_url( 'admin.php' )
							);
							$anon = (int) $row['is_anonymous'];
						?>
							<tr>
								<td><code>#<?php echo absint( $row['id'] ); ?></code></td>
								<td>
									<a href="<?php echo esc_url( $detail_url ); ?>">
										<?php echo esc_html( $row['subject'] ); ?>
									</a>
								</td>
								<td>
									<?php if ( $anon ) : ?>
										<em class="ipr-muted"><?php esc_html_e( 'Anonymous', 'intercessor' ); ?></em>
									<?php else : ?>
										<?php
											$display = trim( ( $row['first_name'] ?? '' ) . ' ' . ( $row['last_name'] ?? '' ) )
												?: ( $row['requester_name_legacy'] ?: esc_html__( '(unnamed)', 'intercessor' ) );
											echo esc_html( $display );
										?>
										<?php if ( $row['requester_email'] ) : ?>
											<span class="ipr-muted">&lt;<?php echo esc_html( $row['requester_email'] ); ?>&gt;</span>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<td>
									<span class="intercessor-status <?php echo esc_attr( $row['status'] ); ?>">
										<?php echo esc_html( $status_labels[ $row['status'] ] ?? ucfirst( $row['status'] ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( mysql2date( $date_fmt, $row['date_created'] ) ); ?></td>
								<td>
									<a href="<?php echo esc_url( $detail_url ); ?>" class="button button-small">
										<?php esc_html_e( 'View', 'intercessor' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav bottom" style="padding:.5rem 0;">
						<div class="tablenav-pages">
							<?php echo esc_url( paginate_links( array(
								'base'      => add_query_arg( 'paged', '%#%', $base_url ),
								'format'    => '',
								'current'   => $paged,
								'total'     => $total_pages,
								'prev_text' => '&laquo;',
								'next_text' => '&raquo;',
							) ) ); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}
}
