<?php
/**
 * Prayers Schema class.
 *
 * @package     Intercessor
 * @subpackage  Database/Schema/Prayers
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Database\Schemas;

use Intercessor\Database\Schema;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Prayers Schema Class.
 *
 * @since 1.0.0
 */
class Prayers extends Schema {

	/**
	 * Array of database column objects.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $columns = [

		// ID.
		[
			'name'       => 'id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'extra'      => 'auto_increment',
			'primary'    => true,
			'sortable'   => true,
			'searchable' => true,
		],

		// Requester ID.
		[
			'name'     => 'requester_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		],

		// User ID.
		[
			'name'     => 'user_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		],

		// Email.
		[
			'name'       => 'email',
			'type'       => 'varchar',
			'length'     => '100',
			'searchable' => true,
			'sortable'   => true,
		],

		// Title.
		[
			'name'       => 'title',
			'type'       => 'varchar',
			'length'     => '64',
			'searchable' => true,
			'sortable'   => true,
		],

		// Message.
		[
			'name'       => 'message',
			'type'       => 'longtext',
			'length'     => '20',
			'searchable' => true,
		],

		// status.
		[
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => 'pending',
			'searchable' => true,
			'sortable'   => true,
			'transition' => true,
		],

		// Prayer Key.
		[
			'name'   => 'prayer_key',
			'type'   => 'varchar',
			'length' => '64',
		],

		// Share.
		[
			'name'    => 'share',
			'type'    => 'varchar',
			'length'  => '64',
			'default' => 'freely',
		],

		// Notify.
		[
			'name'    => 'notify',
			'type'    => 'tinyint',
            'length'  => '20',
			'default' => '0',
		],

		// Date created.
		[
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		],

		// Date active.
		[
			'name'       => 'date_active',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
			'sortable'   => true,
		],

		// End date.
		[
			'name'       => 'end_date',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
			'sortable'   => true,
		],

		// UUID.
		[
			'uuid' => true,
		],
	];
}
