<?php
/**
 * Prayed Counts Object
 *
 * @package     Intercessor
 * @subpackage  Classes/Prayed_Counts
 * @copyright   Copyright (c) 2020, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

namespace Intercessor;

use Intercessor\Database\Rows as Rows;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Prayed Counts Class.
 *
 * @since 1.0.0
 *
 * @property int $id
 * @property int $prayer_id
 * @property string $number
 * @property string $counts
 */
class Prayed_Counts extends Rows\Prayed_Counts {
    /**
     * Order ID.
     *
     * @since 1.0.0
     * @var   int
     */
    protected $id;

    /**
     * Prayed for.
     *
     * @since 1.0.0
     * @var   int
     */
    protected $prayed_for = 0;

    /**
     * Order status.
     *
     * @since 1.0.0
     * @var   int
     */
    protected $prayer_id;

    /**
     * Date created.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $date_created;

}
