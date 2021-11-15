<?php
/**
 * Settings Export Class
 *
 * This class handles Intercessor settings export
 *
 * @package     Intercessor
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools\Export;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Settings export Class
 *
 * @since 0.9.5
 */
class Settings extends Base {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 0.9.5
	 */
	public $export_type = 'settings';

	/**
	 * Set the export headers
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function headers() {
		intercessor_set_time_limit();

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . apply_filters( 'intercessor_settings_export_filename', 'intercessor-export-' . $this->export_type . '-' . date( 'm-d-Y' ) ) . '.json"' );
		header( 'Expires: 0' );
	}
	
	/**
	 * Check if user can export settings.
	 *
	 * @since 0.9.5
	 *
	 * @return bool True if exporting is allowed, false otherwise.
	 */
	public function can_export() {
		return (bool) apply_filters( 'intercessor_export_capability', current_user_can( 'manage_prayer_settings' ) );
	}
	/**
	 * Get the Export Data
	 *
	 * @since 0.9.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The settings data for the JSON file
	 */
	public function get_data() {
		$data = get_option( 'intercessor_settings' );
		$data = apply_filters( 'intercessor_export_get_data', $data );
		$data = apply_filters( 'intercessor_export_get_data_' . $this->export_type, $data );

		return $data;
	}
	
	
	/**
	 * Handles outputting the settings as a json file.
	 *
	 * @access public
	 * @since  0.9.5
	 *
	 * @return void
	 */
	public function export() {
		if ( ! $this->can_export() ) {
			wp_die( 
				esc_html__( 'You do not have permission to export data.', 'intercessor' ),
				esc_html__( 'Error', 'intercessor' ),
				array( 'response' => 403 )
			);
		}

		$this->headers();

		echo wp_json_encode( $this->get_data() );
	}
}
