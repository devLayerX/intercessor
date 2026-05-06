<?php
/**
 * Admin template: Reports page.
 *
 * Variables in scope (set by Reports_Page::render()):
 *   @var array  $views        Registered view definitions.
 *   @var string $active_view  Active view slug.
 *   @var string $period       Active period slug.
 *
 * @package Intercessor
 * @since   1.0.2
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

use Intercessor\Reports\Reports_Page;

$intercessor_period_labels = array(
	'today'      => __( 'Today',       'intercessor' ),
	'yesterday'  => __( 'Yesterday',   'intercessor' ),
	'week'       => __( 'This Week',   'intercessor' ),
	'last_week'  => __( 'Last Week',   'intercessor' ),
	'month'      => __( 'This Month',  'intercessor' ),
	'last_month' => __( 'Last Month',  'intercessor' ),
	'year'       => __( 'This Year',   'intercessor' ),
	'last_year'  => __( 'Last Year',   'intercessor' ),
	'all_time'   => __( 'All Time',    'intercessor' ),
);
?>
<div class="wrap intercessor-reports">

	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Prayer Reports', 'intercessor' ); ?>
	</h1>
	<hr class="wp-header-end">

	<?php // ── Report view tabs ─────────────────────────────────────────── ?>
	<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Report views', 'intercessor' ); ?>">
		<?php foreach ( $views as $intercessor_slug => $intercessor_view ) :
			$intercessor_tab_url = add_query_arg(
				array( 'page' => Reports_Page::PAGE_SLUG, 'view' => $intercessor_slug, 'period' => $period ),
				admin_url( 'admin.php' )
			);
			$intercessor_active  = $intercessor_slug === $active_view;
		?>
			<a href="<?php echo esc_url( $intercessor_tab_url ); ?>"
			   class="nav-tab <?php echo $intercessor_active ? 'nav-tab-active' : ''; ?>"
			   <?php echo $intercessor_active ? 'aria-current="page"' : ''; ?>>
				<?php echo esc_html( $intercessor_view['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php // ── Period selector ──────────────────────────────────────────── ?>
	<div class="ipr-reports-toolbar">
		<form method="get" class="ipr-period-form">
			<input type="hidden" name="page" value="<?php echo esc_attr( Reports_Page::PAGE_SLUG ); ?>">
			<input type="hidden" name="view" value="<?php echo esc_attr( $active_view ); ?>">

			<label for="ipr-period-select" class="ipr-period-label">
				<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
				<?php esc_html_e( 'Period:', 'intercessor' ); ?>
			</label>

			<select name="period" id="ipr-period-select" onchange="this.form.submit()">
				<?php foreach ( $intercessor_period_labels as $intercessor_val => $intercessor_lbl ) : ?>
					<option value="<?php echo esc_attr( $intercessor_val ); ?>"
					        <?php selected( $period, $intercessor_val ); ?>>
						<?php echo esc_html( $intercessor_lbl ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<noscript>
				<button type="submit" class="button">
					<?php esc_html_e( 'Apply', 'intercessor' ); ?>
				</button>
			</noscript>

			<span class="ipr-period-current">
				// translators: %s: human-readable name of the active period, e.g. "This Month"
				<?php printf(
					/* translators: %s: period label */
					esc_html__( 'Showing: %s', 'intercessor' ),
					'<strong>' . esc_html( $intercessor_period_labels[ $period ] ?? $period ) . '</strong>'
				); ?>
			</span>
		</form>

		<div class="ipr-reports-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=intercessor-tools&tab=export' ) ); ?>"
			   class="button button-secondary">
				<span class="dashicons dashicons-download" aria-hidden="true" style="margin-top:3px;"></span>
				<?php esc_html_e( 'Export Data', 'intercessor' ); ?>
			</a>
		</div>
	</div>

	<?php // ── Active view content ──────────────────────────────────────── ?>
	<div class="ipr-report-content" style="margin-top:1rem;">
		<?php
		$intercessor_renderer = $views[ $active_view ]['renderer'] ?? null;

		if ( $intercessor_renderer && is_object( $intercessor_renderer ) && method_exists( $intercessor_renderer, 'render' ) ) {
			$intercessor_renderer->render( $period );
		} elseif ( is_callable( $intercessor_renderer ) ) {
			$intercessor_renderer( $period );
		} else {
			echo '<p class="ipr-report-empty">' . esc_html__( 'No report available.', 'intercessor' ) . '</p>';
		}
		?>
	</div>

</div>
