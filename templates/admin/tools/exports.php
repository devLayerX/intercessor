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
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables included via require, not true globals

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

use Intercessor\Tools\Tools_Admin_Page;

$export_descriptors = Tools_Admin_Page::get_export_descriptors();
$import_descriptors = Tools_Admin_Page::get_import_descriptors();

$export_url = admin_url( 'admin.php?page=intercessor-tools&tab=export' );
$import_url = admin_url( 'admin.php?page=intercessor-tools&tab=import' );
?>
<div class="wrap intercessor-tools">

	<h1><?php esc_html_e( 'Intercessor — Tools', 'intercessor' ); ?></h1>

	<?php // ── Tab nav ──────────────────────────────────────────────────── ?>
	<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Tools tabs', 'intercessor' ); ?>">
		<a href="<?php echo esc_url( $export_url ); ?>"
		   class="nav-tab <?php echo $active_tab === 'export' ? 'nav-tab-active' : ''; ?>"
		   <?php echo $active_tab === 'export' ? 'aria-current="page"' : ''; ?>>
			<span class="dashicons dashicons-download" aria-hidden="true"
			      style="font-size:1em;vertical-align:text-bottom;margin-right:4px;"></span>
			<?php esc_html_e( 'Export', 'intercessor' ); ?>
		</a>
		<a href="<?php echo esc_url( $import_url ); ?>"
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
			<?php foreach ( $export_descriptors as $descriptor ) :
				/** @var \Intercessor\Tools\Abstract_Exporter $exporter */
				$exporter = $descriptor['exporter'];
				$slug     = sanitize_key( $descriptor['slug'] );
				$label    = $descriptor['label'];
				$desc     = $descriptor['description'];
				$action   = "intercessor_export_{$slug}";
			?>
				<div class="intercessor-export-card">
					<div class="intercessor-export-card__header">
						<h2><?php echo esc_html( $label ); ?></h2>
					</div>
					<div class="intercessor-export-card__body">
						<p><?php echo esc_html( $desc ); ?></p>
					</div>
					<div class="intercessor-export-card__footer">
						<form method="post"
						      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
						      class="intercessor-export-form">
							<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
							<?php echo $exporter->nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<button type="submit" class="button button-primary intercessor-export-btn">
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
			$imp  = absint( $_GET['import_imported'] );
			$skip = absint( $_GET['import_skipped'] ?? 0 );
			$fail = absint( $_GET['import_failed']  ?? 0 );
			$errs = array();
			if ( ! empty( $_GET['import_errors'] ) ) {
				$decoded = json_decode( rawurldecode( sanitize_text_field( wp_unslash( $_GET['import_errors'] ) ) ), true );
				$errs    = is_array( $decoded ) ? $decoded : array();
			}
		?>
			<div class="notice notice-success is-dismissible">
				<p>
					<strong><?php esc_html_e( 'Import complete.', 'intercessor' ); ?></strong>
					<?php printf(
						/* translators: 1: imported count, 2: skipped count, 3: failed count */
						esc_html__( '%1$d imported, %2$d skipped, %3$d failed.', 'intercessor' ),
						absint( $imp ), absint( $skip ), absint( $fail )
					); ?>
				</p>
				<?php if ( ! empty( $errs ) ) : ?>
					<ul style="margin:.25rem 0 .5rem 1.25rem;list-style:disc;">
						<?php foreach ( $errs as $err ) : ?>
							<li><?php echo esc_html( $err ); ?></li>
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
			<?php foreach ( $import_descriptors as $descriptor ) :
				/** @var \Intercessor\Tools\Abstract_Importer $importer */
				$importer = $descriptor['importer'];
				$slug     = sanitize_key( $descriptor['slug'] );
				$label    = $descriptor['label'];
				$desc     = $descriptor['description'];
				$columns  = $descriptor['columns'];
				$action   = "intercessor_import_{$slug}";
			?>
				<div class="intercessor-import-card">
					<div class="intercessor-import-card__header">
						<h2><?php echo esc_html( $label ); ?></h2>
					</div>

					<div class="intercessor-import-card__body">
						<p><?php echo esc_html( $desc ); ?></p>

						<details class="intercessor-import-columns">
							<summary>
								<?php esc_html_e( 'Expected CSV columns', 'intercessor' ); ?>
							</summary>
							<ul>
								<?php foreach ( $columns as $col ) : ?>
									<li><code><?php echo esc_html( $col ); ?></code></li>
								<?php endforeach; ?>
							</ul>
						</details>
					</div>

					<div class="intercessor-import-card__footer">
						<form method="post"
						      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
						      enctype="multipart/form-data"
						      class="intercessor-import-form">
							<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
							<?php echo $importer->nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

							<div class="intercessor-import-file-row">
								<label for="import_file_<?php echo esc_attr( $slug ); ?>" class="screen-reader-text">
									<?php printf(
										/* translators: %s: import type label */
										esc_html__( 'Select CSV file for %s import', 'intercessor' ),
										esc_html( $label )
									); ?>
								</label>
								<input type="file"
								       id="import_file_<?php echo esc_attr( $slug ); ?>"
								       name="import_file"
								       accept=".csv,text/csv"
								       required
								       class="intercessor-import-file-input">
								<button type="submit" class="button button-primary intercessor-import-btn">
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
								<?php if ( $slug === 'prayer_requests' ) : ?>
									<?php esc_html_e( 'Rows are always inserted as new records — existing prayers are not updated.', 'intercessor' ); ?>
								<?php elseif ( $slug === 'settings' ) : ?>
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
