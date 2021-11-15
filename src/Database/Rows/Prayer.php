<?php
/**
 * Prayer Row class.
 *
 * @package     Intercessor
 * @subpackage  Database/Row/Prayers
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Database\Rows;

use Intercessor\Database\Row;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Prayer database row class.
 *
 * This class exists solely to encapsulate database schema changes, to help
 * separate the needs of the application layer from the requirements of the
 * database layer.
 *
 * For example, if a database column is renamed or a return value needs to be
 * formatted differently, this class will make sure old values are still
 * supported and new values do not conflict.
 *
 * @since 1.0.0
 */
class Prayer extends Row {

}
