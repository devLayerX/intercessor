<?php
/**
 * Prayer Meta Database class
 *
 * This class is for interacting with the Prayer' Meta database table.
 *
 * @package     Intercessor
 * @subpackage  Database/Prayer_Meta Table
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Database\Tables;

use Intercessor\Database\Table;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Setup the global "intercessor_prayermeta" database table.
 *
 * @since 1.0.0
 */
final class Prayer_Meta extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var string
	 */
	protected $name = 'prayermeta';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var int
	 */
	protected $version = 202001101;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $upgrades = [];

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "meta_id bigint(20) unsigned NOT NULL auto_increment,
        ipr_prayer_id bigint(20) unsigned NOT NULL default '0',
        meta_key varchar(255) DEFAULT NULL,
        meta_value longtext DEFAULT NULL,
        PRIMARY KEY (meta_id),
        KEY ipr_prayer_id (ipr_prayer_id),
        KEY meta_key (meta_key({$max_index_length}))";
	}

}
