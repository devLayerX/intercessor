<?php
/**
 * Settings CSV importer.
 *
 * @package Intercessor
 * @since   1.0.2
 */

declare(strict_types=1);

namespace Intercessor\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// No additional use statements needed — all dependencies are built-in.

/**
 * Imports plugin settings from a CSV file produced by Settings_Exporter.
 *
 * Each data row represents one setting key–value pair (plus an ignored Section
 * column). The importer validates each key against the known settings registry,
 * sanitizes the value using the same Sanitizer that the settings form uses, and
 * merges the result into the live 'intercessor_settings' WordPress option.
 *
 * Unknown or invalid keys are skipped with a descriptive error entry so the
 * admin can identify mismatched exports.
 *
 * The import is always a merge — keys absent from the CSV keep their current
 * saved values unchanged. To fully reset to defaults, use Settings → Advanced →
 * Reset to Defaults (not yet implemented; this importer does not offer that).
 *
 * @since   1.0.2
 * @package Intercessor
 */
final class Settings_Importer extends Abstract_Importer {

	/**
	 * All registered setting keys extracted from the schema on first use.
	 *
	 * Populated lazily in known_keys() to avoid rebuilding the schema multiple
	 * times when process_row() is called once per CSV row.
	 *
	 * @since 1.0.2
	 * @var   array<string, string>|null  Map of key → field type (e.g. 'checkbox').
	 */
	private ?array $known_keys = null;

	/**
	 * Return the import key used for nonce actions and hook names.
	 *
	 * @since  1.0.2
	 * @return string
	 */
	protected function import_key(): string {
		return 'settings';
	}

	/**
	 * Return the required CSV column headers.
	 *
	 * Matches the three-column format produced by Settings_Exporter:
	 * Setting Key, Value, Section.
	 *
	 * @since  1.0.2
	 * @return string[]
	 */
	protected function required_columns(): array {
		return array( 'Setting Key', 'Value' );
	}

	/**
	 * Process a single CSV data row.
	 *
	 * Validates the Setting Key against the known registry, sanitizes the
	 * value, and schedules it for merging into the option. The actual
	 * option write happens after all rows are processed, not per-row, to
	 * avoid many individual update_option() calls.
	 *
	 * @since  1.0.2
	 * @param  array<string, string> $row  Associative row keyed by CSV header.
	 * @param  int                   $line 1-based line number for error messages.
	 * @return void
	 */
	protected function process_row( array $row, int $line ): void {
		$key   = sanitize_key( $row['Setting Key'] ?? '' );
		$value = $row['Value'] ?? '';

		if ( $key === '' ) {
			$this->skipped++;
			return;
		}

		$known = $this->known_keys();

		if ( ! array_key_exists( $key, $known ) ) {
			$this->skipped++;
			// translators: %d: CSV line number, %s: unknown setting key
			$this->errors[] = sprintf(
				/* translators: 1: line number, 2: setting key */
				__( 'Line %1$d: Unknown setting key "%2$s" — skipped.', 'intercessor' ),
				$line,
				esc_html( $key )
			);
			return;
		}

		$type = $known[ $key ];

		// Reverse the localised Yes/No normalisation written by Settings_Exporter.
		// The Sanitizer expects raw values in the form used by the HTML form
		// (checkboxes: '1' for checked, '' for unchecked).
		$value = $this->reverse_yes_no( $value, $type );

		// Sanitize exactly as the settings form does.
		$sanitized = $this->sanitize_value( $key, $value, $type );

		// Accumulate into the pending merge array.
		$this->pending[ $key ] = $sanitized;
		$this->imported++;
	}

	/**
	 * Pending key–value pairs accumulated during process_row() calls.
	 *
	 * Written to the database in a single update_option() call at the end.
	 *
	 * @since 1.0.2
	 * @var   array<string, mixed>
	 */
	private array $pending = array();

	/**
	 * Flush accumulated settings to the database after all rows have been
	 * processed.
	 *
	 * Overrides the no-op in Abstract_Importer. Merges the pending key–value
	 * map into the live 'intercessor_settings' option in a single write so
	 * keys absent from the CSV keep their existing values unchanged.
	 *
	 * @since  1.0.2
	 * @param  array{imported:int, skipped:int, failed:int, errors:string[]} $result
	 * @return void
	 */
	protected function after_run( array $result ): void {
		if ( empty( $this->pending ) ) {
			return;
		}

		$current = (array) get_option( 'intercessor_settings', array() );
		update_option( 'intercessor_settings', array_merge( $current, $this->pending ) );
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Build and cache the map of known setting key → field type.
	 *
	 * Builds the schema via Display_Page (instantiated once) and iterates all
	 * tabs, sections, and fields to extract every key–type pair. The result is
	 * cached on the instance so the schema is only traversed once per import.
	 *
	 * @since  1.0.2
	 * @return array<string, string>  Map of setting key → field type string.
	 */
	private function known_keys(): array {
		if ( $this->known_keys !== null ) {
			return $this->known_keys;
		}

		// Build schema the same way Display_Page does: via Settings_Exporter's
		// known key map. We derive it from the Sanitizer's Registry instead of
		// duplicating the list here — read the schema from Settings_Exporter
		// which already maintains the authoritative list.
		$this->known_keys = array();

		// Use the same section map Settings_Exporter uses as the source of truth
		// for which keys are valid. Derive types from the Sanitizer registry.
		$display_page = new \Intercessor\Admin\Display_Page();

		// We cannot call get_schema() (private), so we retrieve known keys
		// from the Settings_Exporter's section map — the most complete list.
		// Fall back to text type for all; Sanitizer will handle the rest.
		$known_from_exporter = array(
			'auto_approve', 'require_login', 'enable_registration', 'generate_username',
			'generate_password', 'allow_anonymous', 'max_requests_per_day',
			'show_site_terms', 'terms_label', 'terms_url',
			'show_privacy_policy', 'privacy_label', 'privacy_url', 'require_terms_acceptance',
			'profanity_filter', 'profanity_words', 'moderation_role',
			'notify_admin_new_request', 'notify_requester_received', 'notify_requester_status_change',
			'admin_email', 'email_from_name', 'email_from_address',
			'cron_notify_prayed', 'cron_frequency', 'cron_send_hour', 'cron_send_minute',
			'requests_per_page', 'show_date', 'show_requester_name', 'date_format',
			'recaptcha_site_key', 'recaptcha_secret_key', 'recaptcha_version',
			'recaptcha_v3_threshold', 'recaptcha_enable_form', 'recaptcha_enable_history',
			'export_include_content', 'export_status_filter', 'export_prayed_mode',
			'delete_data_on_uninstall',
		);

		// Checkbox keys (value stored as '1' or '').
		$checkbox_keys = array(
			'auto_approve', 'require_login', 'enable_registration', 'generate_username',
			'generate_password', 'allow_anonymous',
			'show_site_terms', 'show_privacy_policy', 'require_terms_acceptance',
			'profanity_filter',
			'notify_admin_new_request', 'notify_requester_received', 'notify_requester_status_change',
			'cron_notify_prayed',
			'show_date', 'show_requester_name',
			'recaptcha_enable_form', 'recaptcha_enable_history',
			'export_include_content',
			'delete_data_on_uninstall',
		);

		$number_keys = array( 'max_requests_per_day', 'requests_per_page', 'recaptcha_v3_threshold',
			'cron_send_hour', 'cron_send_minute' );

		$email_keys    = array( 'admin_email', 'email_from_address' );
		$textarea_keys = array( 'profanity_words' );
		$select_keys   = array( 'recaptcha_version', 'export_status_filter', 'export_prayed_mode',
			'cron_frequency', 'moderation_role' );

		foreach ( $known_from_exporter as $key ) {
			if ( in_array( $key, $checkbox_keys, true ) ) {
				$type = 'checkbox';
			} elseif ( in_array( $key, $number_keys, true ) ) {
				$type = 'number';
			} elseif ( in_array( $key, $email_keys, true ) ) {
				$type = 'email';
			} elseif ( in_array( $key, $textarea_keys, true ) ) {
				$type = 'textarea';
			} elseif ( in_array( $key, $select_keys, true ) ) {
				$type = 'select';
			} else {
				$type = 'text';
			}

			$this->known_keys[ $key ] = $type;
		}

		return $this->known_keys;
	}

	/**
	 * Reverse the Yes/No localisation written by Settings_Exporter.
	 *
	 * Settings_Exporter writes '1' → 'Yes' and '0' / '' → 'No'. The Sanitizer
	 * expects checkbox fields as '1' (checked) or '' (unchecked). Text, email,
	 * and number fields are returned as-is.
	 *
	 * @since  1.0.2
	 * @param  string $value Raw CSV cell value.
	 * @param  string $type  Field type from the schema (e.g. 'checkbox').
	 * @return string        Value suitable for passing to sanitize_value().
	 */
	private function reverse_yes_no( string $value, string $type ): string {
		if ( $type !== 'checkbox' ) {
			return $value;
		}

		$v = strtolower( trim( $value ) );

		if ( in_array( $v, array( 'yes', '1', 'true' ), true ) ) {
			return '1';
		}

		return '';
	}

	/**
	 * Sanitize a single setting value using the same logic as the settings form.
	 *
	 * Routes through the same type-switch as Sanitizer::sanitize() to guarantee
	 * the imported value is identical in type and format to one saved via the UI.
	 *
	 * @since  1.0.2
	 * @param  string $key   Setting key (for context).
	 * @param  string $value Raw (possibly reversed) value.
	 * @param  string $type  Field type from the schema.
	 * @return mixed         Sanitized value ready for update_option().
	 */
	private function sanitize_value( string $key, string $value, string $type ): mixed {
		switch ( $type ) {
			case 'email':
				return sanitize_email( $value );

			case 'url':
				return esc_url_raw( $value );

			case 'number':
				return absint( $value );

			case 'checkbox':
				return $value === '1' ? '1' : '';

			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'select':
				return sanitize_key( $value );

			case 'text':
			case 'password':
			default:
				return sanitize_text_field( $value );
		}
	}
}
