<?php
/**
 * BerlinDB table definition for requester_notes.
 *
 * @package Intercessor
 * @since   1.0.1
 */

declare(strict_types=1);

namespace Intercessor\Database\Table;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use BerlinDB\Database\Table;
use Intercessor\Database\Schema\Requester_Notes_Schema;

/**
 * BerlinDB Table definition for `{prefix}intercessor_requester_notes`.
 *
 * Stores private moderator annotations attached directly to a requester
 * record. These are distinct from prayer notes (which belong to individual
 * prayer requests) and are only visible to administrators.
 *
 * @since   1.0.1
 * @package Intercessor
 */
final class Requester_Notes_Table extends Table {

	/**
	 * Table name without the global $wpdb->prefix.
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $name = 'intercessor_requester_notes';

	/**
	 * Semver string; bump to trigger upgrade().
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $version = '1.0.1';

	/**
	 * Fully-qualified Schema subclass that defines the column set.
	 *
	 * @since 1.0.1
	 * @var   string
	 */
	protected string $schema = Requester_Notes_Schema::class;

	/**
	 * Run schema migrations and call dbDelta via parent.
	 *
	 * parent::upgrade() MUST be called to run dbDelta and persist the version.
	 *
	 * @since  1.0.1
	 * @return void
	 */
	public function upgrade(): void {
		parent::upgrade();
	}
}
