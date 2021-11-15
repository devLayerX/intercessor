<?php
/**
 * Prayer Import Class
 *
 * This class handles importing prayers with the batch processing API
 *
 * @package     Intercessor
 * @subpackage  Admin/Import
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       0.9.5
 */

namespace Intercessor\Admin\Tools\Import;

use Intercessor\Prayer;
use Intercessor\Requester;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Batch_Import Class
 *
 * @since 0.9.5
 */
class Prayers extends Batch {

	/**
	 * Set up our import configuration.
	 *
	 * @since 0.9.5
	 * @return void
	 */
	public function init() {

		$this->per_step = 5;

		// Set up default field map values.
		$this->field_mapping = [
			'requester_id' => '',
			'user_id'      => '',
			'email'        => '',
			'first_name'   => '',
			'last_name'    => '',
			'title'        => '',
			'message'      => '',
			'status'       => '',
			'prayer_key'   => '',
			'share'        => '',
			'notify'       => '',
			'date_created' => '',
			'date_active'  => '',
			'end_date'     => '',
		];
	}

	/**
	 * Process a step
	 *
	 * @return bool
	 * @since 0.9.5
	 */
	public function process_step() {

		$process = false;

		if ( ! $this->can_import() ) {
			wp_die(
				esc_html__( 'You do not have permission to import data.', 'intercessor' ),
				esc_html__( 'Error', 'intercessor' ),
				[ 'response' => 403 ]
			);
		}

		// Remove certain actions to ensure they don't fire when creating the prayers.
		remove_action( 'intercessor_activate_prayer', 'intercessor_activate_prayer' );
		remove_action( 'intercessor_admin_prayer_notification', 'intercessor_admin_email_notice', 10, 2 );

		$i      = 1;
		$offset = $this->step > 1
			? ( $this->per_step * ( $this->step - 1 ) )
			: 0;

		if ( $offset > $this->total ) {
			$this->done = true;

			// Clean up the temporary records in the prayer import process.
			global $wpdb;
			$sql = "DELETE FROM {$wpdb->prefix}ipr_requestermeta WHERE meta_key = '_canonical_import_id'";
			$wpdb->query( $sql );
		}

		if ( ! $this->done && $this->csv->data ) {

			$process = true;

			foreach ( $this->csv->data as $key => $row ) {

				// Skip all rows until we pass our offset.
				if ( $key + 1 <= $offset ) {
					continue;
				}

				// Done with this batch.
				if ( $i > $this->per_step ) {
					break;
				}

				// Import prayer.
				$this->create_prayer( $row );

				$i++;
			}
		}

		return $process;
	}

	/**
	 * Set up and store a prayer record from a CSV row
	 *
	 * @param array $row Column row values.
	 * @since 0.9.5
	 * @return void
	 */
	public function create_prayer( $row = [] ) {
		// Create prayer request.
		$prayer_args = [];

		// Setup prayer date created.
		if ( ! empty( $this->field_mapping['date_created'] ) && ! empty( $row[ $this->field_mapping['date_created'] ] ) ) {

			$date = sanitize_text_field( $row[ $this->field_mapping['date_created'] ] );

			if ( ! strtotime( $date ) ) {
				$date = intercessor_date_i18n( time(), 'mysql' );
			} else {
				$date = date( 'Y-m-d H:i:s', strtotime( $date ) );
			}

			$prayer_args['date_created'] = $date;
		}

		// Setup requester ID.$prayer_args['']
		$prayer_args['requester_id'] = $this->set_requester( $row );
		$requester                   = new Requester( $prayer_args['requester_id'] );

		// Setup user ID.
		if ( ! empty( $this->field_mapping['user_id'] ) && ! empty( $row[ $this->field_mapping['user_id'] ] ) ) {
			$user_id = sanitize_text_field( $row[ $this->field_mapping['user_id'] ] );

			if ( is_numeric( $user_id ) ) {
				$user_id = absint( $row[ $this->field_mapping['user_id'] ] );
				$user    = get_userdata( $user_id );
			} elseif ( is_email( $user_id ) ) {
				$user = get_user_by( 'email', $user_id );
			} else {
				$user = get_user_by( 'login', $user_id );
			}

			if ( $user ) {
				$prayer_args['user_id'] = $user->ID;

				if ( empty( $requester->user_id ) ) {
					$requester->update(
						[
							'user_id' => $user->ID,
						]
					);
				}
			}
		}

		// Setup email.
		if ( ! empty( $this->field_mapping['email'] ) && ! empty( $row[ $this->field_mapping['email'] ] ) ) {
			$prayer_args['email'] = sanitize_text_field( $row[ $this->field_mapping['email'] ] );
		}

		// Setup first name.
		if ( ! empty( $this->field_mapping['first_name'] ) && ! empty( $row[ $this->field_mapping['first_name'] ] ) ) {
			$requester->first_name = sanitize_text_field( $row[ $this->field_mapping['first_name'] ] );
		}

		// Setup last name.
		if ( ! empty( $this->field_mapping['last_name'] ) && ! empty( $row[ $this->field_mapping['last_name'] ] ) ) {
			$requester->last_name = sanitize_text_field( $row[ $this->field_mapping['last_name'] ] );
		}

		// Setup prayer title.
		if ( ! empty( $this->field_mapping['title'] ) && ! empty( $row[ $this->field_mapping['title'] ] ) ) {
			$prayer_args['title'] = sanitize_text_field( $row[ $this->field_mapping['title'] ] );
		}

		// Setup prayer message.
		if ( ! empty( $this->field_mapping['message'] ) && ! empty( $row[ $this->field_mapping['message'] ] ) ) {
			$prayer_args['title'] = \intercessor_sanitize_textarea( $row[ $this->field_mapping['message'] ] );
		}

		// Setup prayer key.
		if ( ! empty( $this->field_mapping['prayer_key'] ) && ! empty( $row[ $this->field_mapping['prayer_key'] ] ) ) {
			$prayer_args['prayer_key'] = sanitize_text_field( $row[ $this->field_mapping['prayer_key'] ] );
		}

		// Setup prayer share option.
		if ( ! empty( $this->field_mapping['share'] ) && ! empty( $row[ $this->field_mapping['share'] ] ) ) {
			$prayer_args['share'] = sanitize_text_field( $row[ $this->field_mapping['share'] ] );
		}

		// Setup prayer notify option.
		if ( ! empty( $this->field_mapping['notify'] ) && ! empty( $row[ $this->field_mapping['notify'] ] ) ) {
			$prayer_args['notify'] = absint( $row[ $this->field_mapping['notify'] ] );
		}

		// The status has to be set after prayer is created to ensure status update properly
		if ( ! empty( $this->field_mapping['status'] ) && ! empty( $row[ $this->field_mapping['status'] ] ) ) {
			$prayer_args['status'] = strtolower( sanitize_text_field( $row[ $this->field_mapping['status'] ] ) );
		} elseif ( \intercessor_hold_prayers() ) {
			$prayer_args['status'] = 'pending';
		} elseif ( 'personal' === $this->field_mapping['share'] ) {
			$prayer_args['status'] = 'personal';
		} else {
			$prayer_args['status'] = 'active';
		}

		// Create prayer request.
		$prayer_id = intercessor_add_item( 'prayer', $prayer_args );

		// Attach the prayer request to the requester.
		$requester->attach_prayer( $prayer_id, true );

		// Return created prayer ID.
		return $prayer_id;
	}

	/**
	 * Setup the prayer requester
	 *
	 * @param array $row The requester row value.
	 *
	 * @since  0.9.5
	 * @access private
	 *
	 * @return int $requester_id The ID of the requester.
	 */
	private function set_requester( $row ) {
		global $wpdb;

		if ( ! empty( $this->field_mapping['email'] )
			&& ! empty( $row[ $this->field_mapping['email'] ] ) ) {

			$email = sanitize_text_field( $row[ $this->field_mapping['email'] ] );

		}

		// Search for a requester from the canonical source, if any.
		if ( ! empty( $this->field_mapping['requester_id'] )
			&& ! empty( $row[ $this->field_mapping['requester_id'] ] ) ) {

			$canonical_id = absint( $row[ $this->field_mapping['requester_id'] ] );
			$mapped_id    = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT requester_id
					FROM $wpdb->requestermeta
					WHERE meta_key = '_canonical_import_id'
					AND meta_value = %d LIMIT 1",
					$canonical_id
				)
			);
		}

		$requester = '';
		if ( ! empty( $mapped_id ) ) {
			$requester = new Requester( $mapped_id );
		}

		if ( empty( $mapped_id ) || ! $requester->id > 0 ) {

			// Look for a requester based on provided ID, if any.
			if ( ! empty( $this->field_mapping['requester_id'] )
				&& ! empty( $row[ $this->field_mapping['requester_id'] ] ) ) {

				$requester_id = absint( $row[ $this->field_mapping['requester_id'] ] );

				$requester_by_id = new Requester( $requester_id );

			}

			// Check for a requester through email provided.
			$requester_by_email = '';
			if ( ! empty( $email ) ) {
				$requester_by_email = new Requester( $email );
			}

			// Now compare requester records. If they don't match, requester_id
			// will be stored in meta and use the requester that matches.

			if ( ( empty( $requester_by_id )
				|| $requester_by_id->id !== $requester_by_email->id )
				&& ! empty( $requester_by_email ) ) {
				$requester = $requester_by_email;
			} elseif ( ! empty( $requester_by_id ) ) {
				$requester = $requester_by_id;

				if ( ! empty( $email ) ) {
					$requester->add_email( $email );
				}
			}

			// Create one if not already existing.
			if ( empty( $requester->id ) ) {

				if ( ! $requester instanceof Requester ) {
					$requester = new Requester;
				}

				$first_name = '';
				$last_name  = '';

				if ( ! empty( $this->field_mapping['first_name'] )
					&& ! empty( $row[ $this->field_mapping['first_name'] ] ) ) {

					$first_name = sanitize_text_field( $row[ $this->field_mapping['first_name'] ] );
				}

				if ( ! empty( $this->field_mapping['last_name'] )
					&& ! empty( $row[ $this->field_mapping['last_name'] ] ) ) {

					$last_name = sanitize_text_field( $row[ $this->field_mapping['last_name'] ] );
				}

				$requester->create(
					[
						'name'  => $first_name . ' ' . $last_name,
						'email' => $email,
					]
				);

				if ( ! empty( $canonical_id ) && (int) $canonical_id !== (int) $requester->id ) {
					\intercessor_update_item_meta( 'requester', $requester->id, '_canonical_import_id', $canonical_id );
				}
			}
		}

		// Add a new email address to requester if email does not exist.
		if ( $email && $email !== $requester->email ) {
			$requester->add_email( $email );
		}

		// Return the requester ID.
		return $requester->id;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 0.9.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$total = count( $this->csv->data );

		$percentage = 0;
		if ( $total > 0 ) {
			$percentage = ( $this->step * $this->per_step / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Retrieve the URL to the prayers list table
	 *
	 * @since 0.9.5
	 * @return string
	 */
	public function get_list_table_url() {
		return admin_url( 'admin.php?page=intercessor-prayers' );
	}

	/**
	 * Retrieve the prayers labels
	 *
	 * @since 0.9.5
	 * @return string
	 */
	public function get_import_type_label() {
		return esc_html__( 'Prayers', 'intercessor' );
	}
}
