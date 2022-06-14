<?php
/**
 * Prayed Counts Query class.
 *
 * @package     Intercessor
 * @subpackage  Database/Queries/Prayed_Counts
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
class Prayed_Counts extends Query {

    /** Table Properties ******************************************************/

    /**
     * Name of the database table to query.
     *
     * @since 1.0.0
     * @access public
     * @var string
     */
    protected $table_name = 'prayed_counts';

    /**
     * String used to alias the database table in MySQL statement.
     *
     * @since 1.0.0
     * @access public
     * @var string
     */
    protected $table_alias = 'c';

    /**
     * Name of class used to setup the database schema
     *
     * @since 1.0.0
     * @access public
     * @var string
     */
    protected $table_schema = '\\Intercessor\\Database\\Schemas\\Prayed_Counts';

    /** Item ******************************************************************/

    /**
     * Name for a single item
     *
     * @since 1.0.0
     * @access public
     * @var string
     */
    protected $item_name = 'prayed_count';

    /**
     * Plural version for a group of items.
     *
     * @since 1.0.0
     * @access public
     * @var string
     */
    protected $item_name_plural = 'prayed_counts';

    /**
     * Callback function for turning IDs into objects
     *
     * @since 1.0.0
     * @access public
     * @var mixed
     */
    protected $item_shape = '\\Intercessor\\Prayed_Counts';

    /** Cache *****************************************************************/

    /**
     * Group to cache queries and queried items in.
     *
     * @since 1.0.0
     * @access public
     * @var string
     */
    protected $cache_group = 'prayed_counts';

    /** Methods ***************************************************************/

    /**
     * Sets up the prayer query, based on the query vars passed.
     *
     * @since 1.0.0
     * @access public
     *
     * @param string|array $query {
     *     Optional. Array or query string of prayer query parameters. Default empty.
     *
     *     @type int          $id                 An prayer ID to only return that prayer. Default empty.
     *     @type array        $id__in             Array of prayer IDs to include. Default empty.
     *     @type array        $id__not_in         Array of prayer IDs to exclude. Default empty.
     *     @type int          $prayer_id          A prayer ID to only return that object. Default empty.
     *     @type array        $prayer_id__in      Array of prayer IDs to include. Default empty.
     *     @type array        $prayer_id__not_in  Array of prayer IDs to exclude. Default empty.
     *     @type int          $number             A user ID to only return that object. Default empty.
     *     @type array        $number__in         Array of user IDs to include. Default empty.
     *     @type array        $number__not_in     Array of user IDs to exclude. Default empty.
     *     @type int          $prayed_for         Limit results to those affiliated with a given counts. Default empty.
     *     @type array        $prayed_for__in     Array of counts to include affiliated prayers for. Default empty.
     *     @type array        $prayed_for__not_in Array of counts to exclude affiliated prayers for. Default empty.
     *     @type array        $date_query         Query all datetime columns together. See WP_Date_Query.
     *     @type string       $fields             Item fields to return. Accepts any column known names
     *                                            or empty (returns an array of complete prayer objects). Default empty.
     *     @type string       $date_created       Date prayer request was prayed for.
     *     @type int          $offset             Number of prayers to offset the query. Used to build LIMIT clause.
     *                                            Default 0.
     *     @type bool         $no_found_rows      Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
     *     @type string|array $orderby              Accepts 'id', 'number', 'status', 'number', 'prayer_id', 'counts', 'message'
     *                                              'prayer_key', 'date_created', 'date_active', 'number__in', 'prayer_id__in'.
     *                                              'counts__in', 'message__in', 'prayer_key__in'.
     *                                              Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
     *                                              Default 'id'.
     *     @type string       $order                How to order retrieved prayers. Accepts 'ASC', 'DESC'. Default 'DESC'.
     *     @type string       $search               Search term(s) to retrieve matching prayers for. Default empty.
     *     @type bool         $update_cache         Whether to prime the cache for found prayers. Default false.
     * }
     */
    public function __construct( $query = [] ) {
        parent::__construct( $query );
    }

}
