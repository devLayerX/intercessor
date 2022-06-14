<?php
/**
 * Prayer Object
 *
 * @package     Intercessor
 * @subpackage  Classes/Prayers
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.9.5
 */

namespace Intercessor;

use Intercessor\Database\Rows as Rows;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Prayer Class
 *
 * @property int $id
 * @property int $requester_id
 * @property int $user_id
 * @property string $email
 * @property string $title
 * @property string $message
 * @property string $status
 * @property string $prayer_key
 * @property string $share
 * @property int $notify
 * @property string $date_created
 * @property string $date_active
 * @property string $end_date
 * @since 0.9.5
 */
class Prayer extends Rows\Prayer {
    /**
     * Prayer ID.
     *
     * @since 0.9.5
     * @access protected
     * @var int
     */
    protected $id;

    /**
     * Prayer Requester ID.
     *
     * @since 0.9.5
     * @access protected
     * @var int
     */
    protected $requester_id;

    /**
     * Prayer User ID.
     *
     * @since 0.9.5
     * @access protected
     * @var int
     */
    protected $user_id;

    /**
     * Prayer User Email.
     *
     * @since 0.9.5
     * @access protected
     * @var string
     */
    protected $email;

    /**
     * Prayer Request Title.
     *
     * @since 0.9.5
     * @access protected
     * @var string
     */
    protected $title;

    /**
     * Prayer Request Body or Message.
     *
     * @since 0.9.5
     * @access protected
     * @var string
     */
    protected $message;

    /**
     * Prayer Status (Active, Pending, Personal or Archived).
     *
     * @since 0.9.5
     * @access protected
     * @var string
     */
    protected $status;

    /**
     * Prayer Request Key.
     *
     * @since 0.9.5
     * @access protected
     * @var string
     */
    protected $prayer_key;

    /**
     * Prayer Request Share Option.
     *
     * @since 0.9.5
     * @access protected
     * @var string
     */
    protected $share;

    /**
     * Option to tweet prayer
     *
     * @since 1.0.0
     * @access protected
     * @var bool
     */
    protected $tweet;

    /**
     * Notify Prayer.
     *
     * User wishes to be notified when prayer request is prayed for.
     *
     * @since 0.9.5
     * @access protected
     * @var int
     */
    protected $notify;

    /**
     * Created Date.
     *
     * @since 0.9.5
     * @access protected
     * @var string
     */
    protected $date_created;

    /**
     * Start Date.
     *
     * @since 0.9.5
     * @access protected
     * @var string
     */
    protected $date_active;

    /**
     * End Date.
     *
     * @since 0.9.5
     * @access protected
     * @var string
     */
    protected $end_date;

    /**
     * Retrieves the status label of the prayer.
     *
     * @since 0.9.5
     *
     * @return string Status label for the current prayer.
     */
    public function get_status_label() {
        switch ( $this->status ) {
            case 'archived':
                $label = esc_html__( 'Archived', 'intercessor' );
                break;
            case 'pending':
                $label = esc_html__( 'Pending', 'intercessor' );
                break;
            case 'personal':
                $label = esc_html__( 'Private', 'intercessor' );
                break;
            case 'active':
            default:
                $label = esc_html__( 'Active', 'intercessor' );
                break;
        }

        /**
         * Filters the prayer status.
         *
         * @param string $label  Prayer status label.
         * @param string $status Prayer status (active, pending, private, archived).
         * @param int    $id     Prayer ID.
         *
         * @since 0.9.5
         */
        return apply_filters( 'intercessor_get_prayer_status_label', $label, $this->status, $this->id );
    }

	/**
	 * Get the title of a prayer request.
	 *
	 * @since 1.0.0
	 * @return string|void
	 */
	public function get_title() {
    	return esc_attr( $this->title );
    }

	/**
	 * Convert to array.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function array_convert(): array {
		return get_object_vars( $this );
	}
}
