<?php
/**
 * Schema definition for the requester_notes table.
 *
 * @package Intercessor
 * @since   1.0.1
 */

declare(strict_types=1);

namespace Intercessor\Database\Schema;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use Intercessor\BerlinDB\Schema;

/**
 * Defines the column set for the `{prefix}intercessor_requester_notes` table.
 *
 * Requester notes are private annotations attached directly to a requester
 * record by an administrator. They are distinct from prayer notes, which are
 * attached to individual prayer requests. Requester notes are never shown
 * on the front end.
 *
 * @since   1.0.1
 * @package Intercessor
 */
final class Requester_Notes_Schema extends Schema {

	/**
	 * Column definitions for the requester_notes table.
	 *
	 * @since 1.0.1
	 * @var   array<int, array<string, mixed>>
	 */
	public array $columns = array(
		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		),
		array(
			'name'     => 'requester_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		),
		array(
			'name'     => 'author_user_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		),
		array(
			'name'    => 'content',
			'type'    => 'text',
			'default' => '',
			// text cannot carry a plain KEY index in MySQL/MariaDB without a
			// prefix length. Omitting 'searchable' prevents buildCreateSql()
			// from emitting "KEY content (content)", which causes dbDelta to fail.
		),
		array(
			'name'    => 'is_private',
			'type'    => 'tinyint',
			'length'  => '1',
			'default' => '1',
		),
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		),
	);
}
