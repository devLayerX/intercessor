<?php
/**
 * Batch Import Class
 *
 * This is the base class for all batch import methods. Each data import type (Requesters, prayers, etc) extend this class
 *
 * @package     Intercessor
 * @subpackage  Admin/Import
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools\Import;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * IPR_Batch_Import Class
 *
 * @since 0.9.5
 */
class Batch {

	/**
	 * The file being imported
	 *
	 * @since 0.9.5
	 */
	public $file;

	/**
	 * The parsed CSV file being imported
	 *
	 * @since 0.9.5
	 */
	public $csv;

	/**
	 * Total rows in the CSV file
	 *
	 * @since 0.9.5
	 */
	public $total;

	/**
	 * The current step being processed
	 *
	 * @since 0.9.5
	 */
	public $step;

    /**
     * The processes are finished.
     *
     * @since 0.9.5
     */
    public $done;

	/**
	 * The number of items to process per step
	 *
	 * @since 0.9.5
	 */
	public $per_step = 20;

	/**
	 * The capability required to import data
	 *
	 * @since 0.9.5
	 */
	public $capability_type = 'manage_prayer_settings';

	/**
	 * Is the import file empty
	 *
	 * @since 0.9.5
	 */
	public $is_empty = false;

	/**
	 * Map of CSV columns > database fields
	 *
	 * @since 0.9.5
	 */
	public $field_mapping = [];

	/**
	 * Get things started
	 *
	 * @param string $_file The file to import.
	 * @param int    $_step The step to process.
	 *
	 * @since 0.9.5
	 */
	public function __construct( $_file = '', $_step = 1 ) {

		if ( ! class_exists( 'parseCSV' ) ) {
			require_once INTERCESSOR_DIR . 'libraries/parsecsv.lib.php';
		}

		$this->step  = $_step;
		$this->file  = $_file;
		$this->done  = false;
		$this->csv   = new \parseCSV();
		$this->csv->auto( $this->file );
		$this->total = count( $this->csv->data );
		$this->init();

	}

	/**
	 * Initialize the updater. Runs after import file is loaded but before any processing is done.
	 *
	 * @since  0.9.5
	 * @return void
	 */
	public function init() {}

	/**
	 * Can we import?
	 *
	 * @since  0.9.5
	 * @return bool Whether we can iport or not
	 */
	public function can_import() {
		return (bool) apply_filters( 'intercessor_import_capability', current_user_can( $this->capability_type ) );
	}

	/**
	 * Get the CSV columns
	 *
	 * @since  0.9.5
	 * @return array The columns in the CSV
	 */
	public function get_columns() {

		return $this->csv->titles;
	}

	/**
	 * Get the first row of the CSV
	 *
	 * This is used for showing an example of what the import will look like
	 *
	 * @since  0.9.5
	 * @return array The first row after the header of the CSV
	 */
	public function get_first_row() {

		return array_map(
			[ $this, 'trim_preview' ],
			current( $this->csv->data )
		);
	}

	/**
	 * Process a step
	 *
	 * @since  0.9.5
	 * @return bool
	 */
	public function process_step() {

		$process = false;

		if ( ! $this->can_import() ) {
			wp_die(
				esc_html__( 'You do not have permission to import data.', 'intercessor' ),
                esc_html__( 'Error', 'intercessor' ),
                [ 'response' => 403 ]
			);
		}

		return $process;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since  0.9.5
	 * @return int
	 */
	public function get_percentage_complete() {
		return 100;
	}

	/**
	 * Map CSV columns to import fields
	 *
	 * @param array $import_fields The fields to import.
	 *
	 * @since  0.9.5
	 * @return void
	 */
	public function map_fields( $import_fields = [] ) {
		$this->field_mapping = $import_fields;
	}

	/**
	 * Retrieve the URL to the list table for the import data type
	 *
	 * @since 0.9.5
	 */
	public function get_list_table_url() {}

	/**
	 * Retrieve the label for the import type. Example: Prayers
	 *
	 * @since 0.9.5
	 */
	public function get_import_type_label() {}

	/**
	 * Convert a string containing delimiters to an array
	 *
	 * @param string $str Input to convert to an array
	 *
	 * @since 0.9.5
	 * @return array
	 */
	public function str_to_array( $str = '' ) {

		$array = [];

		if ( is_array( $str ) ) {
			return array_map( 'trim', $str );
		}

		// Look for standard delimiters.
		if ( false !== strpos( $str, '|' ) ) {

			$delimiter = '|';

		} elseif ( false !== strpos( $str, ',' ) ) {

			$delimiter = ',';

		} elseif ( false !== strpos( $str, ';' ) ) {

			$delimiter = ';';

		} elseif ( false !== strpos( $str, '/' ) && ! filter_var( str_replace( ' ', '%20', $str ), FILTER_VALIDATE_URL ) && '/' !== substr( $str, 0, 1 ) ) {

			$delimiter = '/';

		}

		if ( ! empty( $delimiter ) ) {

			$array = (array) explode( $delimiter, $str );

		} else {

			$array[] = $str;
		}

		return array_map( 'trim', $array );

	}

	/**
	 * Convert a files string containing delimiters to an array.
	 *
	 * This is identical to str_to_array() except it ignores all / characters.
	 *
	 * @since 1.0.0
	 * @param $str Input string to convert to an array
	 * @return array
	 */
	 public function convert_file_string_to_array( $str = '' ) {

		$array = array();

		if( is_array( $str ) ) {
			return array_map( 'trim', $str );
		}

		// Look for standard delimiters
		if( false !== strpos( $str, '|' ) ) {

			$delimiter = '|';

		} elseif( false !== strpos( $str, ',' ) ) {

			$delimiter = ',';

		} elseif( false !== strpos( $str, ';' ) ) {

			$delimiter = ';';

		}

		if( ! empty( $delimiter ) ) {

			$array = (array) explode( $delimiter, $str );

		} else {

			$array[] = $str;
		}

		return array_map( 'trim', $array );

	}

	/**
	 * Trims a column value for preview
	 *
	 * @param string $str Input to trim down
	 *
	 * @since  0.9.5
	 * @return string
	 */
	public function trim_preview( $str = '' ) {

		if ( ! is_numeric( $str ) ) {

			$long = strlen( $str ) >= 30;
			$str  = substr( $str, 0, 30 );
			$str  = $long ? $str . '...' : $str;

		}

		return $str;
	}
}
