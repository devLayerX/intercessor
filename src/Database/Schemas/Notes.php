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
 * Notes Schema Class.
 *
 * @since 1.0.0
 */
class Notes extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since 1.0.0
	 * @access public
	 * @var array
	 */
	public $columns = [

		// ID.
		[
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		],

		// Object ID.
		[
			'name'     => 'object_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
		],

		// Object type.
		[
			'name'     => 'object_type',
			'type'     => 'varchar',
			'length'   => '20',
			'default'  => '',
			'sortable' => true,
		],

		// User ID.
		[
			'name'     => 'user_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
		],

		// Content.
		[
			'name'       => 'content',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true,
			'in'         => false,
			'not_in'     => false,
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

		// Date modified.
		[
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => '',
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		],

		// UUID.
		[
			'uuid' => true,
		],
	];
}
