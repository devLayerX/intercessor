<?php
/**
 * Requester Query class.
 *
 * @package     Intercessor
 * @subpackage  Database/Query/Requesters
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Database\Queries;

use Intercessor\Database\Query;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class used for querying prayers.
 *
 * @since 1.0.0
 *
 * @see \Intercessor\Database\Query::__construct() for accepted arguments.
 */
class Requester extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'requesters';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'r';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\Intercessor\\Database\\Schemas\\Requesters';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'requester';

	/**
	 * Plural version for a group of items.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'requesters';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since 1.0.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\Intercessor\\Requester';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'requesters';

	/** Methods ***************************************************************/

	/**
	 * Sets up the requester query, based on the query vars passed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of requester query parameters. Default empty.
	 *
	 *     @type int          $id                   An requester ID to only return that requester. Default empty.
	 *     @type array        $id__in               Array of requester IDs to include. Default empty.
	 *     @type array        $id__not_in           Array of requester IDs to exclude. Default empty.
	 *     @type int          $user_id              A user ID to only return that object. Default empty.
	 *     @type array        $user_id__in          Array of user IDs to include. Default empty.
	 *     @type array        $user_id__not_in      Array of user IDs to exclude. Default empty.
	 *     @type string       $email                Limit results to those affiliated with a given email. Default empty.
	 *     @type array        $email__in            Array of email to include affiliated prayers for. Default empty.
	 *     @type array        $email__not_in        Array of email to exclude affiliated prayers for. Default empty.
	 *     @type string       $status               A prayer status to only return that prayer. Default empty.
	 *     @type array        $status__in           Array of prayer statuses to include. Default empty.
	 *     @type array        $status__not_in       Array of prayer statuses to exclude. Default empty.
	 *     @type int          $prayer_count         A numeric value. Default empty.
	 *     @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
	 *     @type array        $date_created_query   Date query clauses to limit requesters by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query.
	 *                                              Default null.
	 *     @type bool         $count                Whether to return a requester count (true) or array of requester objects.
	 *                                              Default false.
	 *     @type string       $fields               Item fields to return. Accepts any column known names
	 *                                              or empty (returns an array of complete requester objects). Default empty.
	 *     @type int          $number               Limit number of requesters to retrieve. Default 100.
	 *     @type int          $offset               Number of requesters to offset the query. Used to build LIMIT clause.
	 *                                              Default 0.
	 *     @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby              Accepts 'id', 'date_created', 'start_date', 'end_date'.
	 *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                              Default 'id'.
	 *     @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
	 *     @type string       $search               Search term(s) to retrieve matching requesters for. Default empty.
	 *     @type bool         $update_cache         Whether to prime the cache for found requesters. Default false.
	 * }
	 */
	public function __construct( $query = [] ) {
		parent::__construct( $query );
	}
}
