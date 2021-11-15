<?php
/**
 * Notes DB class
 *
 * This class is for interacting with the Notes' database table.
 *
 * @package     Intercessor
 * @subpackage  Database/Notes Table
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Database\Tables;

use Intercessor\Database\Table;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Setup the global "intercessor_notes" database table.
 *
 * @since 1.0.0
 */
final class Notes extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var string
	 */
	protected $name = 'notes';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var int
	 */
	protected $version = 202109011;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $upgrades = [
		'202109011' => 202109011,
	];

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
			object_id bigint(20) unsigned NOT NULL default '0',
			object_type varchar(20) NOT NULL default '',
			user_id bigint(20) unsigned NOT NULL default '0',
			content longtext NOT NULL default '',
			date_created datetime NOT NULL default CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL default CURRENT_TIMESTAMP,
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY object_id_type (object_id,object_type(20)),
			KEY user_id (user_id),
			KEY date_created (date_created)";
	}

	/**
	 * Upgrade to version 202109011
	 * - Change the dafault value for the column `date_created` to `CURRENT_TIMESTAMP`
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	protected function __202109011() {

		// Update `date_created`.
		$result = $this->get_db()->query(
			"ALTER TABLE {$this->table_name} MODIFY COLUMN `date_created` datetime NOT NULL default CURRENT_TIMESTAMP;"
		);

		// Update `date_modified`.
		$result = $this->get_db()->query(
			"ALTER TABLE {$this->table_name} MODIFY COLUMN `date_modified` datetime NOT NULL default CURRENT_TIMESTAMP;"
		);

		return $this->is_success( $result );
	}
}
