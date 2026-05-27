<?php
/**
 * Abstract CSV importer base class.
 *
 * @package Intercessor
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Intercessor\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\Http\Request;

/**
 * Abstract base class for all Intercessor CSV importers.
 *
 * Mirrors the Template Method pattern used by Abstract_Exporter. Concrete
 * subclasses implement required_columns(), process_row(), and import_key()
 * while this class handles all shared concerns:
 *
 *   - Capability enforcement.
 *   - Nonce verification.
 *   - File upload validation (MIME type, size, extension).
 *   - UTF-8 BOM stripping.
 *   - CSV parsing into associative rows keyed by the header row.
 *   - Per-row delegation to process_row().
 *   - Structured result collection (imported / skipped / failed counts).
 *   - Redirect back to the Tools page with a flash-notice query string.
 *
 * Each importer is paired with an admin_post_intercessor_import_{key} handler
 * registered by Tools_Admin_Page::register(). dispatch() is the single entry
 * point called from that handler.
 *
 * @since   1.0.0
 * @package Intercessor
 */
abstract class Abstract_Importer {

	// ── Constants ─────────────────────────────────────────────────────────────

	/** @var int Maximum upload size in bytes (5 MB). */
	protected const MAX_BYTES = 5 * 1024 * 1024;

	// ── Result counters ───────────────────────────────────────────────────────

	/** @var int Rows successfully imported. */
	protected int $imported = 0;

	/** @var int Rows skipped (duplicate, not applicable, etc.). */
	protected int $skipped = 0;

	/** @var int Rows that could not be processed. */
	protected int $failed = 0;

	/** @var string[] Per-row error messages collected during import. */
	protected array $errors = array();

	// ── Subclass contract ─────────────────────────────────────────────────────

	/**
	 * Return the short slug identifying this importer.
	 *
	 * Used to build the nonce action and the admin_post hook name. Must match
	 * the key used in Tools_Admin_Page::IMPORTERS.
	 *
	 * @since  1.0.0
	 * @return string  e.g. 'prayer_requests' or 'settings'.
	 */
	abstract protected function import_key(): string;

	/**
	 * Return the ordered list of CSV column header names this importer expects.
	 *
	 * The import is aborted and an error shown when the uploaded file's first
	 * row does not contain all of these values (extra columns are ignored).
	 *
	 * @since  1.0.0
	 * @return string[] Expected header values, case-sensitive.
	 */
	abstract protected function required_columns(): array;

	/**
	 * Process a single data row from the CSV.
	 *
	 * Called once for every data row (not the header). $row is an associative
	 * array keyed by the header values from the uploaded file. Implementations
	 * should increment $this->imported, $this->skipped, or $this->failed and
	 * optionally push to $this->errors to explain why a row was skipped/failed.
	 *
	 * @since  1.0.0
	 * @param  array<string, string> $row  Associative row from the parsed CSV.
	 * @param  int                   $line 1-based line number (including header).
	 * @return void
	 */
	abstract protected function process_row( array $row, int $line ): void;

	// ── Security helpers ──────────────────────────────────────────────────────

	/**
	 * Return the nonce action string for this importer.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	protected function nonce_action(): string {
		return 'intercessor_import_' . $this->import_key();
	}

	/**
	 * Return the WordPress capability required to run this import.
	 *
	 * Defaults to 'manage_prayer_settings'. Override to restrict to a different
	 * capability.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	protected function required_capability(): string {
		return 'manage_prayer_settings';
	}

	// ── Public entry point ────────────────────────────────────────────────────

	/**
	 * Verify security, parse the upload, run per-row processing, then redirect.
	 *
	 * Called from the admin_post handler registered by Tools_Admin_Page. Never
	 * returns — always ends with wp_safe_redirect() + exit.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function dispatch(): void {
		$this->check_capability();
		$this->verify_nonce();

		$file   = $this->validate_upload();
		$rows   = $this->parse_csv( $file );
		$result = $this->run( $rows );

		// Give subclasses a chance to act after all rows are processed but
		// before the redirect fires (e.g. Settings_Importer flushes here).
		$this->after_run( $result );

		$this->redirect_with_result( $result );
	}

	/**
	 * Called after all rows have been processed, before the redirect fires.
	 *
	 * Override in a subclass to perform finalisation work that must happen
	 * after process_row() has been called for every row but before the browser
	 * is redirected back to the Tools page. The default implementation is a
	 * no-op.
	 *
	 * @since  1.0.0
	 * @param  array{imported:int, skipped:int, failed:int, errors:string[]} $result
	 * @return void
	 */
	protected function after_run( array $result ): void {
		// No-op by default. Subclasses may override.
	}

	/**
	 * Return an HTML nonce hidden-input field for embedding in import forms.
	 *
	 * @since  1.0.0
	 * @return string HTML <input type="hidden"> element.
	 */
	public function nonce_field(): string {
		return wp_nonce_field( $this->nonce_action(), '_wpnonce', true, false );
	}

	// ── Security enforcement ──────────────────────────────────────────────────

	/**
	 * Halt with wp_die() when the current user lacks the required capability.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function check_capability(): void {
		if ( ! current_user_can( $this->required_capability() ) ) {
			wp_die(
				esc_html__( 'You do not have permission to import data.', 'intercessor' ),
				403
			);
		}
	}

	/**
	 * Halt with wp_die() when the posted nonce is absent or invalid.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function verify_nonce(): void {
		( Request::capture() )->require_nonce( $this->nonce_action() );
	}

	// ── File validation ───────────────────────────────────────────────────────

	/**
	 * Validate the uploaded CSV file and return its temporary path.
	 *
	 * Checks that a file was uploaded, that no upload error occurred, that the
	 * file is within the size limit, that its extension is .csv, and that its
	 * MIME type is text-based. Calls wp_die() with a descriptive message on any
	 * failure.
	 *
	 * @since  1.0.0
	 * @return string  Validated temporary file path.
	 */
	private function validate_upload(): string {
		$key = 'import_file';

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Upload handlers call verify_nonce() before validating the file.
		if ( empty( $_FILES[ $key ] ) || ! is_array( $_FILES[ $key ] ) ) {
			wp_die( esc_html__( 'No file was uploaded.', 'intercessor' ) );
		}

		$file = $_FILES[ $key ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( (int) $file['error'] !== UPLOAD_ERR_OK ) {
			wp_die(
				// translators: %d: PHP upload error code
				sprintf(
					/* translators: %d: PHP upload error code */
					esc_html__( 'Upload failed with error code %d.', 'intercessor' ),
					(int) $file['error']
				)
			);
		}

		if ( (int) $file['size'] > self::MAX_BYTES ) {
			wp_die( esc_html__( 'The uploaded file exceeds the 5 MB size limit.', 'intercessor' ) );
		}

		$ext = strtolower( pathinfo( sanitize_file_name( $file['name'] ), PATHINFO_EXTENSION ) );
		if ( $ext !== 'csv' ) {
			wp_die( esc_html__( 'Only .csv files are accepted.', 'intercessor' ) );
		}

		// wp_check_filetype_and_ext() may return 'text/plain' for CSV — allow both.
		$check     = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		$mime_type = $check['type'] ?? mime_content_type( $file['tmp_name'] );

		$allowed_mimes = array( 'text/csv', 'text/plain', 'application/csv', 'text/comma-separated-values' );
		if ( ! in_array( (string) $mime_type, $allowed_mimes, true ) ) {
			wp_die(
				// translators: %s: detected MIME type string
				sprintf(
					/* translators: %s: detected MIME type */
					esc_html__( 'Invalid file type: %s. Only CSV files are accepted.', 'intercessor' ),
					esc_html( (string) $mime_type )
				)
			);
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing
		return (string) $file['tmp_name'];
	}

	// ── CSV parsing ───────────────────────────────────────────────────────────

	/**
	 * Open a validated CSV file and parse it into associative rows.
	 *
	 * Strips a leading UTF-8 BOM if present (added by Excel on export), reads
	 * the first row as headers, validates that all required_columns() are present,
	 * then returns every subsequent row as an associative array keyed by header.
	 *
	 * @since  1.0.0
	 * @param  string                       $path  Validated temporary file path.
	 * @return array<int, array<string, string>>    Associative data rows.
	 */
	private function parse_csv( string $path ): array {
		$handle = fopen( $path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		if ( $handle === false ) {
			wp_die( esc_html__( 'Could not open the uploaded file for reading.', 'intercessor' ) );
		}

		// Strip UTF-8 BOM (EF BB BF) that Excel adds on export.
		$bom = fread( $handle, 3 ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( $bom !== "\xEF\xBB\xBF" ) {
			rewind( $handle );
		}

		// First row = headers.
		$raw_headers = fgetcsv( $handle );
		if ( ! is_array( $raw_headers ) ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			wp_die( esc_html__( 'The CSV file is empty or unreadable.', 'intercessor' ) );
		}

		$headers = array_map( 'trim', $raw_headers );

		// Validate that all required columns exist.
		$required = $this->required_columns();
		$missing  = array_diff( $required, $headers );

		if ( ! empty( $missing ) ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			wp_die(
				// translators: %s: comma-separated list of missing CSV column names
				sprintf(
					/* translators: %s: comma-separated list of missing column names */
					esc_html__( 'The CSV is missing required columns: %s.', 'intercessor' ),
					esc_html( implode( ', ', $missing ) )
				)
			);
		}

		$rows = array();

		while ( ( $values = fgetcsv( $handle ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			if ( count( $values ) !== count( $headers ) ) {
				continue; // Skip malformed rows silently.
			}
			$rows[] = array_combine( $headers, array_map( 'trim', $values ) );
		}

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		return $rows;
	}

	// ── Row dispatch ──────────────────────────────────────────────────────────

	/**
	 * Iterate all parsed rows and dispatch each one to process_row().
	 *
	 * @since  1.0.0
	 * @param  array<int, array<string, string>> $rows Parsed associative rows.
	 * @return array{imported:int, skipped:int, failed:int, errors:string[]}
	 */
	private function run( array $rows ): array {
		foreach ( $rows as $index => $row ) {
			$this->process_row( $row, $index + 2 ); // +2: 1-based + skip header.
		}

		return array(
			'imported' => $this->imported,
			'skipped'  => $this->skipped,
			'failed'   => $this->failed,
			'errors'   => $this->errors,
		);
	}

	// ── Result redirect ───────────────────────────────────────────────────────

	/**
	 * Redirect back to the Tools import tab with result counts in the query string.
	 *
	 * The flash notice is read and rendered by the imports template on the next
	 * page load. Errors are passed as a JSON-encoded, URL-encoded string so
	 * multiple messages survive the redirect without requiring a transient.
	 *
	 * @since  1.0.0
	 * @param  array{imported:int, skipped:int, failed:int, errors:string[]} $result
	 * @return void
	 */
	private function redirect_with_result( array $result ): void {
		$args = array(
			'page'             => 'intercessor-tools',
			'tab'              => 'import',
			'import_key'       => $this->import_key(),
			'import_imported'  => $result['imported'],
			'import_skipped'   => $result['skipped'],
			'import_failed'    => $result['failed'],
		);

		if ( ! empty( $result['errors'] ) ) {
			// Limit to first 10 errors to keep the URL manageable.
			$args['import_errors'] = rawurlencode(
				wp_json_encode( array_slice( $result['errors'], 0, 10 ) )
			);
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
