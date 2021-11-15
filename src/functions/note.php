<?php
/**
 * Note Functions
 *
 * @package     Intercessor
 * @subpackage  Notes
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'intercessor_add_note' ) ) {
    /**
     * Add a note.
     *
     * @since 1.0.0
     *
     * @param array $data {
     *     Array of note data. Default empty.
     *
     *     The `date_created` and `date_modified` parameters do not need to be passed.
     *     They will be automatically populated if empty.
     *
     *     @type int    $object_id     Object ID that the note refers to. This would
     *                                 be an ID that corresponds to the object type
     *                                 specified. E.g. an object ID of 25 with object
     *                                 type of `order` refers to order 25 in the
     *                                 `edd_orders` table. Default empty.
     *     @type string $object_type   Object type that the note refers to.
     *                                 E.g. `discount` or `order`. Default empty.
     *     @type int    $user_id       ID of the current WordPress user logged in.
     *                                 Default 0.
     *     @type string $content       Note content. Default empty.
     *     @type string $date_created  Optional. Automatically calculated on add/edit.
     *                                 The date & time the note was inserted.
     *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
     *     @type string $date_modified Optional. Automatically calculated on add/edit.
     *                                 The date & time the note was last modified.
     *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
     * }
     *
     * @return int|false ID of newly created note, false on error.
     */
	function intercessor_add_note( $data = array() ) {

		// An object ID and object type must be supplied for every note that is
		// inserted into the database.
		if ( empty( $data['object_id'] ) || empty( $data['object_type'] ) ) {
			return false;
		}

		// Instantiate the database object.
		$notes = new Intercessor\Database\Queries\Note();
		
		// Add a new note.
		return $notes->add_item( $data );
	}
}

if ( ! function_exists( 'intercessor_delete_note' ) ) {
	/**
	 * Delete a note.
	 *
	 * @param int $note_id Note ID.
	 *
	 * @since  0.9.5
	 * @return bool True if deletion was successful, false otherwise.
	 */
	function intercessor_delete_note( $note_id = 0 ) {
		
		// Instantiate the database object.
        $notes = new Intercessor\Database\Queries\Note();

		return $notes->delete_item( $note_id );
	}
}

if ( ! function_exists( 'intercessor_update_note' ) ) {
    /**
     * Update a note.
     *
     * @since 1.0.0
     *
     * @param int   $note_id Note ID.
     * @param array $data {
     *     Array of note data. Default empty.
     *
     *     @type int    $object_id     Object ID that the note refers to. This would
     *                                 be an ID that corresponds to the object type
     *                                 specified. E.g. an object ID of 25 with object
     *                                 type of `order` refers to order 25 in the
     *                                 `edd_orders` table. Default empty.
     *     @type string $object_type   Object type that the note refers to.
     *                                 E.g. `discount` or `order`. Default empty.
     *     @type int    $user_id       ID of the current WordPress user logged in.
     *                                 Default 0.
     *     @type string $content       Note content. Default empty.
     *     @type string $date_created  Optional. Automatically calculated on add/edit.
     *                                 The date & time the note was inserted.
     *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
     *     @type string $date_modified Optional. Automatically calculated on add/edit.
     *                                 The date & time the note was last modified.
     *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
     * }
     *
     * @return int|false Number of rows updated if successful, false otherwise.
     */
	function intercessor_update_note( $note_id = 0, $data = array() ) {
		
		// Instantiate the database object.
        $notes = new Intercessor\Database\Queries\Note();
		
		return $notes->update_item( $note_id, $data );
	}
}

if ( ! function_exists( 'intercessor_get_note' ) ) {
	/**
	 * Get a note by ID.
	 *
	 * @param int $note_id Note ID.
	 *
	 * @since  0.9.5
	 * @return object Intercessor\Note object if successful, otherwise false.
	 */
	function intercessor_get_note( $note_id = 0 ) {
		return intercessor_get_note_by( 'id', $note_id );
	}
}

if ( ! function_exists( 'intercessor_get_note_by' ) ) {
	/**
	 * Get a note by a specific field's value.
	 *
	 * @param string $field Database table field.
	 * @param string $value Value of the row.
	 *
	 * @since  0.9.5
	 * @return object
	 */
	function intercessor_get_note_by( $field = '', $value = '' ) {
		
		// Instantiate the database object.
        $notes = new Intercessor\Database\Queries\Note();
		
		return $notes->get_item_by( $field, $value );
	}
}

if ( ! function_exists( 'intercessor_get_notes' ) ) {
	/**
	 * Query for notes.
	 *
	 * @param array $args Arguments.
	 *
	 * @since  0.9.5
	 * @return array
	 */
	function intercessor_get_notes( $args = array() ) {

		// Parse args
		$query = wp_parse_args( $args, array(
			'number' => 30
		) );

		// Instantiate note database object.
        $notes = new Intercessor\Database\Queries\Note();

		return $notes->query( $query );
	}
}

if ( ! function_exists( 'intercessor_count_notes' ) ) {
	/**
	 * Count notes.
	 *
	 * @param array $args Arguments.
	 *
	 * @since  0.9.5
	 * @return int
	 */
	function intercessor_count_notes( $args = array() ) {

		// Parse args
		$counts = wp_parse_args( $args, array(
			'count' => true
		) );

		// Instantiate note database class.
        $notes = new Intercessor\Database\Queries\Note( $counts );

        // Return counts.
		return absint( $notes->found_items );
	}
}

if ( ! function_exists( 'intercessor_get_prayer_notes' ) ) {
	/**
	 * Get prayer request notes.
	 *
	 * @param int $prayer_id Prayer ID.
	 *
	 * @return bool|object
	 */
	function intercessor_get_prayer_notes( $prayer_id = 0 ) {

		$notes = new \Intercessor\Notes();

		return $notes->get_by_object_id( $prayer_id );
	}
}
