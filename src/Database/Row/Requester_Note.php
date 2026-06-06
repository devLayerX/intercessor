<?php
/**
 * Requester note row value object.
 *
 * @package Intercessor
 * @since   1.0.1
 */

declare(strict_types=1);

namespace Intercessor\Database\Row;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\BerlinDB\Row;

/**
 * Represents a single row from the intercessor_requester_notes table.
 *
 * Requester notes are private internal annotations attached to a requester
 * record by an administrator. They are separate from prayer notes, which
 * belong to individual prayer requests. Requester notes are never shown
 * on the front end or to the requester themselves.
 *
 * @since   1.0.1
 * @package Intercessor
 *
 * @property int    $id              Primary key.
 * @property int    $requester_id    Foreign key to the requesters table.
 * @property int    $author_user_id  WordPress user ID of the note author.
 * @property string $content         Note body text.
 * @property int    $is_private      1 = private (admin only), 0 = general note.
 * @property string $date_created    UTC datetime of note creation.
 * @property string $date_modified   UTC datetime of most recent edit.
 */
final class Requester_Note extends Row {

	/**
	 * Primary key.
	 *
	 * @since 1.0.1
	 * @var   int
	 */
	public int $id = 0;

	/**
	 * Foreign key referencing the parent requester record.
	 *
	 * @since 1.0.1
	 * @var   int
	 */
	public int $requester_id = 0;

	/**
	 * WordPress user ID of the administrator who authored the note.
	 *
	 * @since 1.0.1
	 * @var   int
	 */
	public int $author_user_id = 0;

	/**
	 * Body text of the internal note.
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	public string $content = '';

	/**
	 * Whether this note is private (admin-only).
	 *
	 * 1 = visible only to administrators.
	 * 0 = general note (stored but no front-end exposure).
	 *
	 * @since 1.0.1
	 * @var   int
	 */
	public int $is_private = 1;

	/**
	 * UTC datetime string of note creation (format: Y-m-d H:i:s).
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	public string $date_created = '';

	/**
	 * UTC datetime string of the most recent edit (format: Y-m-d H:i:s).
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	public string $date_modified = '';

	/**
	 * Hydrate from the raw database object and cast integer columns.
	 *
	 * @since  1.0.1
	 * @param  object $item Raw stdClass returned by $wpdb.
	 */
	public function __construct( object $item ) {
		parent::__construct( $item );

		$this->id             = (int) $this->id;
		$this->requester_id   = (int) $this->requester_id;
		$this->author_user_id = (int) $this->author_user_id;
		$this->is_private     = (int) $this->is_private;
	}

	/**
	 * Return true when this note is restricted to administrators only.
	 *
	 * @since  1.0.1
	 * @return bool True when is_private equals 1.
	 */
	public function is_private(): bool {
		return $this->is_private === 1;
	}
}
