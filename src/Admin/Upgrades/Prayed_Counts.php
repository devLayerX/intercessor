<?php
/**
 * Intercessor Prayed Counts Upgrade
 *
 * @subpackage  Admin/Upgrades/Prayed_Counts
 * @copyright   Copyright (c) 2021, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0.0
 */

namespace Intercessor\Admin\Upgrades;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Prayed_Counts Update Class.
 *
 * @since 1.1.0
 */
class Prayed_Counts extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
     *
     * @access public
     * @since 1.1.0
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = esc_html__( 'Prayed counts migration completed successfully.', 'intercessor' );
		$this->upgrade           = 'prayed_counts';
	}

	/**
	 * Retrieve the data pertaining to the current step and migrate as necessary.
	 *
	 * @since 1.1.0
	 *
	 * @return bool True if data was migrated, false otherwise.
	 */
	public function get_data() : bool {
		$offset = ( $this->step - 1 ) * $this->per_step;

		$results = $this->get_db()->get_results( $this->get_db()->prepare(
			"SELECT *
			 FROM {$this->get_db()->ipr_prayermeta}
			 WHERE meta_key = %s
			 ORDER BY ipr_prayer_id ASC
			 LIMIT %d, %d",
			esc_sql( 'prayed_counts' ), $offset, $this->per_step
		) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$result->object_id = $result->comment_post_ID;
				Migrator::prayed_counts( $result );
			}

			return true;
		}

		return false;
	}

	/**
	 * Calculate the percentage completed.
	 *
	 * @since 1.1.0
	 *
	 * @return float Percentage.
	 */
	public function get_percentage_complete() {
		$total = $this->get_db()->get_var(
			$this->get_db()->prepare(
				"SELECT COUNT(ipr_prayer_id) AS count
				FROM {$this->get_db()->ipr_prayer_meta} 
				WHERE meta_key = %s",
				esc_sql( 'prayed_counts' )
			)
		);

        // Set counter to null if nothing exists in the database.
		if ( empty( $total ) ) {
			$total = 0;
		}

		// Set up percentage value.
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		// Set percentage to 100.
		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}
}
