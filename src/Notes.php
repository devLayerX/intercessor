<?php
/**
 * Intercessor Note Object
 *
 * @package    Intercessor
 * @subpackage  Classes/Notes
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

namespace Intercessor;

use Intercessor\Base;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Notes Class.
 *
 * @since 1.0.0
 */
class Notes extends Base {

	/**
	 * Note ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $id;

	/**
	 * Object ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $object_id;

	/**
	 * Object Type.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $object_type = '';

	/**
	 * Note content.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $content = '';

	/**
	 * User ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $user_id;

	/**
	 * Date created.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $date_created = '0000-00-00 00:00:00';

	/**
	 * Date modified.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $date_modified = '0000-00-00 00:00:00';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param \object $note Note data from the database.
	 */
	public function __construct( $note = null ) {
		parent::__construct( $note );

		if ( is_object( $note ) ) {
		}
	}
}
