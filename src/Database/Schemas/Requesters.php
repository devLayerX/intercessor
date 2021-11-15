<?php
/**
 * Requesters Schema class.
 *
 * @package     Intercessor
 * @subpackage  Database/Schema/Requesters
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Database\Schemas;

use Intercessor\Database\Schema;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Requesters Schema Class.
 *
 * @since 1.0.0
 */
class Requesters extends Schema {

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

		// User ID.
		[
			'name'      => 'user_id',
			'type'      => 'bigint',
			'length'    => '20',
			'unsigned'  => true,
			'default'   => '0',
			'cache_key' => true,
		],

		// Name.
		[
			'name'       => 'name',
			'type'       => 'varchar',
			'length'     => '255',
			'searchable' => true,
			'sortable'   => true,
		],

		// Email.
		[
			'name'       => 'email',
			'type'       => 'varchar',
			'length'     => '100',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// status
		[
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '20',
			'default'    => 'active',
			'sortable'   => true,
			'transition' => true,
		],

		// Prayer count.
		[
			'name'     => 'prayer_count',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true,
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

		// uuid.
		[
			'uuid' => true,
		],
	];
}
