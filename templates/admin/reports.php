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
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables included via require, not true globals

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

use Intercessor\Reports\Reports_Page;

$period_labels = array(
	'today'      => esc_html__( 'Today',       'intercessor' ),
	'yesterday'  => esc_html__( 'Yesterday',   'intercessor' ),
	'week'       => esc_html__( 'This Week',   'intercessor' ),
	'last_week'  => esc_html__( 'Last Week',   'intercessor' ),
	'month'      => esc_html__( 'This Month',  'intercessor' ),
	'last_month' => esc_html__( 'Last Month',  'intercessor' ),
	'year'       => esc_html__( 'This Year',   'intercessor' ),
	'last_year'  => esc_html__( 'Last Year',   'intercessor' ),
	'all_time'   => esc_html__( 'All Time',    'intercessor' ),
);
?>
<div class="wrap intercessor-reports">

	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Prayer Reports', 'intercessor' ); ?>
	</h1>
	<hr class="wp-header-end">

	<?php // ── Report view tabs ─────────────────────────────────────────── ?>
	<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Report views', 'intercessor' ); ?>">
		<?php foreach ( $views as $slug => $view ) :
			$tab_url = add_query_arg(
				array( 'page' => Reports_Page::PAGE_SLUG, 'view' => $slug, 'period' => $period ),
				admin_url( 'admin.php' )
			);
			$active  = $slug === $active_view;
		?>
			<a href="<?php echo esc_url( $tab_url ); ?>"
			   class="nav-tab <?php echo $active ? 'nav-tab-active' : ''; ?>"
			   <?php echo $active ? 'aria-current="page"' : ''; ?>>
				<?php echo esc_html( $view['label'] ); ?>
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
				<?php foreach ( $period_labels as $val => $lbl ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>"
					        <?php selected( $period, $val ); ?>>
						<?php echo esc_html( $lbl ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<noscript>
				<button type="submit" class="button">
					<?php esc_html_e( 'Apply', 'intercessor' ); ?>
				</button>
			</noscript>

			<span class="ipr-period-current">
				<?php printf(
					/* translators: %s: period label */
					esc_html__( 'Showing: %s', 'intercessor' ),
					'<strong>' . esc_html( $period_labels[ $period ] ?? $period ) . '</strong>'
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
		$renderer = $views[ $active_view ]['renderer'] ?? null;

		if ( $renderer && is_object( $renderer ) && method_exists( $renderer, 'render' ) ) {
			$renderer->render( $period );
		} elseif ( is_callable( $renderer ) ) {
			$renderer( $period );
		} else {
			echo '<p class="ipr-report-empty">' . esc_html__( 'No report available.', 'intercessor' ) . '</p>';
		}
		?>
	</div>

</div>
