<?php
/**
 * Prayed Counts Schema class.
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
 * Prayed Counts Schema Class.
 *
 * @since 1.0.0
 */
class Prayed_Counts extends Schema {

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

        // Prayer ID.
        [
            'name'     => 'prayer_id',
            'type'     => 'bigint',
            'length'   => '20',
            'unsigned' => true,
            'default'  => '0',
            'sortable' => true,
        ],

        // Prayed counts.
        [
            'name'     => 'prayed_for',
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

        // UUID.
        [
            'uuid' => true,
        ],
    ];
}
