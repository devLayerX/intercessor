<?php
/**
 * Admin template: Tools page — Export and Import tabs.
 *
 * Variables in scope (set by Tools_Admin_Page::render()):
 *   @var string $active_tab  'export' or 'import'.
 *
 * @package Intercessor
 * @since   1.0.2
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

use Intercessor\Tools\Tools_Admin_Page;

$intercessor_export_descriptors = Tools_Admin_Page::get_export_descriptors();
$intercessor_import_descriptors = Tools_Admin_Page::get_import_descriptors();

$intercessor_export_url = admin_url( 'admin.php?page=intercessor-tools&tab=export' );
$intercessor_import_url = admin_url( 'admin.php?page=intercessor-tools&tab=import' );
?>
<div class="wrap intercessor-tools">

	<h1><?php esc_html_e( 'Intercessor — Tools', 'intercessor' ); ?></h1>

	<?php // ── Tab nav ──────────────────────────────────────────────────── ?>
	<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Tools tabs', 'intercessor' ); ?>">
		<a href="<?php echo esc_url( $intercessor_export_url ); ?>"
		   class="nav-tab <?php echo $active_tab === 'export' ? 'nav-tab-active' : ''; ?>"
		   <?php echo $active_tab === 'export' ? 'aria-current="page"' : ''; ?>>
			<span class="dashicons dashicons-download" aria-hidden="true"
			      style="font-size:1em;vertical-align:text-bottom;margin-right:4px;"></span>
			<?php esc_html_e( 'Export', 'intercessor' ); ?>
		</a>
		<a href="<?php echo esc_url( $intercessor_import_url ); ?>"
		   class="nav-tab <?php echo $active_tab === 'import' ? 'nav-tab-active' : ''; ?>"
		   <?php echo $active_tab === 'import' ? 'aria-current="page"' : ''; ?>>
			<span class="dashicons dashicons-upload" aria-hidden="true"
			      style="font-size:1em;vertical-align:text-bottom;margin-right:4px;"></span>
			<?php esc_html_e( 'Import', 'intercessor' ); ?>
		</a>
	</nav>

	<?php if ( $active_tab === 'export' ) : ?>

	<?php // ── Export tab ────────────────────────────────────────────────── ?>
	<div class="intercessor-tab-content" style="margin-top:1.25rem;">

		<p class="description">
			<?php esc_html_e(
				'Download your Intercessor data as CSV files. Export options (status filter, content inclusion, etc.) are configured on the Settings → Export tab.',
				'intercessor'
			); ?>
		</p>

		<div class="intercessor-export-grid">
			<?php foreach ( $intercessor_export_descriptors as $intercessor_descriptor ) :
				/** @var \Intercessor\Tools\Abstract_Exporter $intercessor_exporter */
				$intercessor_exporter = $intercessor_descriptor['exporter'];
				$intercessor_slug     = sanitize_key( $intercessor_descriptor['slug'] );
				$intercessor_label    = $intercessor_descriptor['label'];
				$intercessor_desc     = $intercessor_descriptor['description'];
				$intercessor_action   = "intercessor_export_{$intercessor_slug}";
			?>
				<div class="intercessor-export-card">
					<div class="intercessor-export-card__header">
						<h2><?php echo esc_html( $intercessor_label ); ?></h2>
					</div>
					<div class="intercessor-export-card__body">
						<p><?php echo esc_html( $intercessor_desc ); ?></p>
					</div>
					<div class="intercessor-export-card__footer">
						<form method="post"
						      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
						      class="intercessor-export-form">
							<input type="hidden" name="action" value="<?php echo esc_attr( $intercessor_action ); ?>">
							<?php echo $intercessor_exporter->nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<button type="submit" class="button button-primary intercessor-export-btn">
								// translators: %s: export type label, e.g. "Prayer Requests"
								<?php printf(
									/* translators: %s: export type label */
									esc_html__( 'Download %s CSV', 'intercessor' ),
									esc_html( $label )
								); ?>
							</button>
						</form>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<hr>

		<div class="intercessor-tools-notice">
			<p>
				<span class="dashicons dashicons-info" aria-hidden="true"></span>
				<?php esc_html_e(
					'Exports run synchronously. Very large datasets may take a few seconds. All exports are formatted as UTF-8 CSV with a BOM so Excel opens them correctly.',
					'intercessor'
				); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=intercessor-settings&tab=export' ) ); ?>">
					<?php esc_html_e( 'Configure export options →', 'intercessor' ); ?>
				</a>
			</p>
		</div>

	</div>

	<?php else : ?>

	<?php // ── Import tab ────────────────────────────────────────────────── ?>
	<div class="intercessor-tab-content" style="margin-top:1.25rem;">

		<?php // ── Flash notices ──────────────────────────────────────── ?>
		<?php // phpcs:disable WordPress.Security.NonceVerification.Recommended ?>
		<?php if ( isset( $_GET['import_imported'] ) ) :
			$intercessor_imp  = absint( $_GET['import_imported'] );
			$intercessor_skip = absint( $_GET['import_skipped'] ?? 0 );
			$intercessor_fail = absint( $_GET['import_failed']  ?? 0 );
			$intercessor_errs = array();
			if ( ! empty( $_GET['import_errors'] ) ) {
				$intercessor_decoded = json_decode( rawurldecode( sanitize_text_field( wp_unslash( $_GET['import_errors'] ) ) ), true );
				$intercessor_errs    = is_array( $intercessor_decoded ) ? $intercessor_decoded : array();
			}
		?>
			<div class="notice notice-success is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Import complete.', 'intercessor' ); ?></strong>
					// translators: %s: import type label, e.g. "Prayer Requests"
					<?php printf(
						/* translators: 1: imported count, 2: skipped count, 3: failed count */
						esc_html__( '%1$d imported, %2$d skipped, %3$d failed.', 'intercessor' ),
						esc_attr( $intercessor_imp ), esc_attr( $intercessor_skip ), esc_attr( $intercessor_fail )
					); ?>
				</p>
				<?php if ( ! empty( $intercessor_errs ) ) : ?>
					<ul style="margin:.25rem 0 .5rem 1.25rem;list-style:disc;">
						<?php foreach ( $intercessor_errs as $intercessor_err ) : ?>
							<li><?php echo esc_html( $intercessor_err ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php // phpcs:enable WordPress.Security.NonceVerification.Recommended ?>

		<p class="description">
			<?php esc_html_e(
				'Upload a CSV file to import data into Intercessor. The file must match the column format described on each card below. Download a CSV export first to see the expected format.',
				'intercessor'
			); ?>
		</p>

		<div class="intercessor-import-grid">
			<?php foreach ( $intercessor_import_descriptors as $intercessor_descriptor ) :
				/** @var \Intercessor\Tools\Abstract_Importer $intercessor_importer */
				$intercessor_importer = $intercessor_descriptor['importer'];
				$intercessor_slug     = sanitize_key( $intercessor_descriptor['slug'] );
				$intercessor_label    = $intercessor_descriptor['label'];
				$intercessor_desc     = $intercessor_descriptor['description'];
				$columns  = $descriptor['columns'];
				$intercessor_action   = "intercessor_import_{$intercessor_slug}";
			?>
				<div class="intercessor-import-card">
					<div class="intercessor-import-card__header">
						<h2><?php echo esc_html( $intercessor_label ); ?></h2>
					</div>

					<div class="intercessor-import-card__body">
						<p><?php echo esc_html( $intercessor_desc ); ?></p>

						<details class="intercessor-import-columns">
							<summary>
								<?php esc_html_e( 'Expected CSV columns', 'intercessor' ); ?>
							</summary>
							<ul>
								<?php foreach ( $columns as $col ) : ?>
									<li><code><?php echo esc_html( $intercessor_col ); ?></code></li>
								<?php endforeach; ?>
							</ul>
						</details>
					</div>

					<div class="intercessor-import-card__footer">
						<form method="post"
						      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
						      enctype="multipart/form-data"
						      class="intercessor-import-form">
							<input type="hidden" name="action" value="<?php echo esc_attr( $intercessor_action ); ?>">
							<?php echo $intercessor_importer->nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

							<div class="intercessor-import-file-row">
								<label for="import_file_<?php echo esc_attr( $intercessor_slug ); ?>" class="screen-reader-text">
									// translators: %s: import type label
									<?php printf(
										/* translators: %s: import type label */
										esc_html__( 'Select CSV file for %s import', 'intercessor' ),
										esc_html( $label )
									); ?>
								</label>
								<input type="file"
								       id="import_file_<?php echo esc_attr( $intercessor_slug ); ?>"
								       name="import_file"
								       accept=".csv,text/csv"
								       required
								       class="intercessor-import-file-input">
								<button type="submit" class="button button-primary intercessor-import-btn">
									// translators: %d: number of days with zero submissions hidden from count display
									<?php printf(
										/* translators: %s: import type label */
										esc_html__( 'Import %s', 'intercessor' ),
										esc_html( $label )
									); ?>
								</button>
							</div>

							<p class="intercessor-import-hint">
								<span class="dashicons dashicons-warning" aria-hidden="true"></span>
								<?php esc_html_e( 'Max 5 MB. UTF-8 CSV only.', 'intercessor' ); ?>
								<?php if ( $intercessor_slug === 'prayer_requests' ) : ?>
									<?php esc_html_e( 'Rows are always inserted as new records — existing prayers are not updated.', 'intercessor' ); ?>
								<?php elseif ( $intercessor_slug === 'settings' ) : ?>
									<?php esc_html_e( 'Only known setting keys are applied. Existing settings not in the file are preserved.', 'intercessor' ); ?>
								<?php endif; ?>
							</p>
						</form>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<hr>

		<div class="intercessor-tools-notice">
			<p>
				<span class="dashicons dashicons-info" aria-hidden="true"></span>
				<?php esc_html_e(
					'To get the correct CSV format, use the Export tab to download existing data first, then use that file as a template.',
					'intercessor'
				); ?>
			</p>
		</div>

	</div>

	<?php endif; ?>

</div><!-- .wrap.intercessor-tools -->
