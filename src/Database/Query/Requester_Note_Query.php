<?php
/**
 * Query class for the requester_notes table.
 *
 * @package Intercessor
 * @since   1.0.1
 */

declare(strict_types=1);

namespace Intercessor\Database\Query;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use BerlinDB\Database\Query;
use Intercessor\Database\Row\Requester_Note;
use Intercessor\Database\Schema\Requester_Notes_Schema;

/**
 * Provides CRUD and domain-specific query methods for the requester_notes table.
 *
 * Requester notes are internal annotations attached to a requester record by
 * administrators. They are distinct from prayer notes (attached to individual
 * prayer requests) and are never surfaced in front-end templates.
 *
 * @since   1.0.1
 * @package Intercessor
 *
 * @method Requester_Note|false get_item( int $id )
 * @method Requester_Note[]     get_items( array $args = [] )
 * @method int|false            add_item( array $data )
 * @method bool                 update_item( int $id, array $data )
 * @method bool                 delete_item( int $id )
 * @method int                  count_items( array $args = [] )
 */
final class Requester_Note_Query extends Query {

	/**
	 * Shared prefix for all Intercessor table names.
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $prefix = 'intercessor';

	/**
	 * Table name segment (appended to prefix + $wpdb->prefix).
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $table_name = 'requester_notes';

	/**
	 * Short SQL alias used in query fragments.
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $table_alias = 'rn';

	/**
	 * Fully-qualified Schema subclass name.
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $table_schema = Requester_Notes_Schema::class;

	/**
	 * Singular item label for cache keys and hook names.
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $item_name = 'requester_note';

	/**
	 * Plural item label for cache keys and hook names.
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $item_name_plural = 'requester_notes';

	/**
	 * Fully-qualified Row subclass instantiated for each result.
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $item_shape = Requester_Note::class;

	// -------------------------------------------------------------------------
	// Domain-specific methods
	// -------------------------------------------------------------------------

	/**
	 * Retrieve all notes for a given requester, newest first.
	 *
	 * @since  1.0.1
	 * @param  int               $requester_id Primary key of the parent requester.
	 * @return Requester_Note[]                Notes ordered newest-first.
	 */
	public function get_for_requester( int $requester_id ): array {
		return $this->get_items( array(
			'requester_id' => $requester_id,
			'orderby'      => 'date_created',
			'order'        => 'DESC',
			'number'       => 0,
		) );
	}

	/**
	 * Count all notes belonging to a given requester.
	 *
	 * @since  1.0.1
	 * @param  int $requester_id Primary key of the requester.
	 * @return int               Total note count.
	 */
	public function count_for_requester( int $requester_id ): int {
		return $this->count_items( array( 'requester_id' => $requester_id ) );
	}

	/**
	 * Add a new note authored by the current WordPress user.
	 *
	 * Automatically sets author_user_id to the currently logged-in admin.
	 *
	 * @since  1.0.1
	 * @param  int    $requester_id Primary key of the parent requester.
	 * @param  string $content      Note body text (already sanitized).
	 * @param  bool   $private      Whether the note is admin-only. Default true.
	 * @return int|false            New note row ID on success; false on failure.
	 */
	public function add_note( int $requester_id, string $content, bool $private = true ): int|false {
		return $this->add_item( array(
			'requester_id'   => $requester_id,
			'author_user_id' => get_current_user_id(),
			'content'        => $content,
			'is_private'     => $private ? 1 : 0,
		) );
	}

	/**
	 * Delete all notes associated with a given requester.
	 *
	 * Called when a requester is hard-deleted to maintain consistency.
	 *
	 * @since  1.0.1
	 * @param  int  $requester_id Primary key of the requester being deleted.
	 * @return bool               True on success; false on DB error.
	 */
	public function delete_all_for_requester( int $requester_id ): bool {
		global $wpdb;

		$result = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$this->fq_table_name,
			array( 'requester_id' => $requester_id ),
			array( '%d' )
		);

		return $result !== false;
	}
}
