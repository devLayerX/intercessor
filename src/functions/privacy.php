<?php
/**
 * Privacy Functions
 *
 * @package     IPR
 * @subpackage  Functions
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/GPL-3.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register the IPR default template for a privacy policy.
 *
 * @since 0.9.5
 */
function intercessor_register_privacy_policy_template() {

	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	$content = wp_kses_post( apply_filters( 'intercessor_privacy_policy_content', __( '
		We collect information about you during the prayer submission process. This information may include, but is not limited to, your name, email address, and any other details that might be requested from you for the purpose of processing your requests.
		Handling this data also allows us to:
		- Send you important account/prayer information.
		- Set up and administer your account, provide technical and/or requester support, and to verify your identity.
		', 'intercessor' 
	) ) );

	$content .= "\n\n";

	$additional_collection = array(
		__( 'Account email/password to allow you to access your account, if you have one.', 'intercessor' ),
		__( 'If you choose to create an account with us, your name, address, and email address, which will be used to populate the prayer form for future requests.', 'intercessor' ),
	);

	$additional_collection = apply_filters( 'intercessor_privacy_policy_additional_collection', $additional_collection );

	$content .= __( 'Additionally we may also collect the following information:', 'intercessor' ) . "\n";
	if ( ! empty( $additional_collection ) ) {
		foreach ( $additional_collection as $item ) {
			$content .= '- ' . $item . "\n";
		}
	}

	wp_add_privacy_policy_content( 'Intercessor', wpautop( $content ) );
}
add_action( 'admin_init', 'intercessor_register_privacy_policy_template' );

/** Helper Functions */

/**
 * Given a string, mask it with the * character.
 *
 * First and last character will remain with the filling characters being changed to *. One Character will
 * be left in tact as is. Two character strings will have the first character remain and the second be a *.
 *
 * @since 0.9.5
 * @param string $string
 *
 * @return string
 */
function intercessor_mask_string( $string = '' ) {

	if ( empty( $string ) ) {
		return '';
	}

	$first_char  = substr( $string, 0, 1 );
	$last_char   = substr( $string, -1, 1 );

	$masked_string = $string;

	if ( strlen( $string ) > 2 ) {
		$total_stars = strlen( $string ) - 2;
		$masked_string = $first_char . str_repeat( '*', $total_stars ) . $last_char;

	} elseif ( strlen( $string ) === 2 ) {
		$masked_string = $first_char . '*';
	}

	return $masked_string;
}

/**
 * Given a domain, mask it with the * character.
 *
 * TLD parts will remain intact (.com, .co.uk, etc). All subdomains will be masked t**t.e*****e.co.uk.
 *
 * @since 0.9.5
 * @param string $domain
 *
 * @return string
 */
function intercessor_mask_domain( $domain = '' ) {

	if ( empty( $domain ) ) {
		return '';
	}

	$domain_parts = explode( '.', $domain );

	if ( count( $domain_parts ) === 2 ) {

		// We have a single entry tld like .org or .com
		$domain_parts[0] = intercessor_mask_string( $domain_parts[0] );

	} else {

		$part_count     = count( $domain_parts );
		$possible_cctld = strlen( $domain_parts[ $part_count - 2 ] ) <= 3 ? true : false;

		$mask_parts = $possible_cctld ? array_slice( $domain_parts, 0, $part_count - 2 ) : array_slice( $domain_parts, 0, $part_count - 1 );

		$i = 0;
		while ( $i < count( $mask_parts ) ) {
			$domain_parts[ $i ] = intercessor_mask_string( $domain_parts[ $i ]);
			$i++;
		}
	}

	return implode( '.', $domain_parts );
}

/**
 * Given an email address, mask the name and domain according to domain and string masking functions.
 *
 * Will result in an email address like a***n@e*****e.org for admin@example.org.
 *
 * @since 0.9.5
 * @param $email_address
 *
 * @return string
 */
function intercessor_pseudo_mask_email( $email_address ) {
	if ( ! is_email( $email_address ) ) {
		return $email_address;
	}

	$email_parts = explode( '@', $email_address );
	$name        = intercessor_mask_string( $email_parts[0] );
	$domain      = intercessor_mask_domain( $email_parts[1] );

	$email_address = $name . '@' . $domain;

	return $email_address;
}

/*
 * Return an anonymized email address.
 *
 * While WP Core supports anonymizing email addresses with the wp_privacy_anonymize_data function,
 * it turns every email address into deleted@site.invalid, which does not work when some prayer/requester records
 * are still needed for legal and regulatory reasons.
 *
 * This function will anonymize the email with an MD5 that is salted
 * and given a randomized uniqid prefixed with the store URL in order to prevent connecting a single requester across
 * multiple stores, as well as the timestamp at the time of anonymization (so it trying the same email again will not be
 * repeatable and therefore connected), and return the email address as <hash>@site.invalid.
 *
 * @since 0.9.5
 *
 * @param string $email_address
 *
 * @return string
 */
function intercessor_anonymize_email( $email_address ) {

	// Bail if no email address is passed.
	if ( empty( $email_address ) ) {
		return $email_address;
	}

	$email_address    = strtolower( $email_address );
	$email_parts      = explode( '@', $email_address );
	$anonymized_email = wp_hash( uniqid( get_option( 'site_url' ), true ) . $email_parts[0] . current_time( 'timestamp' ), 'nonce' );

	return $anonymized_email . '@site.invalid';
}

/**
 * Given a requester ID, anonymize the data related to that requester.
 *
 * Only the requester record is affected in this function. The data that is changed:
 * - The name is changed to 'Anonymized Requester'
 * - The email address is anonymized, but kept in a format that passes is_email checks
 * - The date created is set to the timestamp of 0 (January 1, 1970)
 * - Notes are fully cleared
 * - Any additional email addresses are removed
 *
 * Once start, a note is left stating when the requester was anonymized.
 *
 * @param int $requester_id Requester ID.
 *
 * @return array
 */
function _intercessor_anonymize_requester( $requester_id = 0 ) {

//	$requester = new \Intercessor\Requester( $requester_id );
	$requester = intercessor_get_requester( $requester_id );
	
	if ( empty( $requester->id ) ) {
		$message = array(
			'success' => false,
			'message' => sprintf(
				__( 'No requester with ID %d', 'intercessor' ),
				$requester_id
			)
		);
		
		return $message;
	}

	/**
	 * Determines if this requester should be allowed to be anonymized.
	 *
	 * @since 0.9.5
	 *
	 * @param array {
	 *     Contains data related to if the anonymization should take place
	 *
	 *     @type bool   $should_anonymize If the requester should be anonymized.
	 *     @type string $message          A message to display if the requester could not be anonymized.
	 * }
	 */
	$should_anonymize_requester = apply_filters( 'intercessor_should_anonymize_requester',
		array(
			'should_anonymize' => true, 
			'message'          => array(),
		),
		$requester
	);

	if ( empty( $should_anonymize_requester['should_anonymize'] ) ) {
		$message = array(
			'success' => false,
			'message' => $should_anonymize_requester['message'],
		);

		return $message;
	}

	// Now we should look at prayers this requester has associated, and if there are any prayers that should not be modified,
	// do not modify the requester.
	$prayers = intercessor_get_items(
		'prayer',
		array(
			'requester' => $requester->id,
			'output'    => 'prayers',
			'number'    => -1,
		)
	);

	foreach ( $prayers as $prayer ) {
		$action = _intercessor_privacy_get_prayer_action( $prayer );
		if ( 'none' === $action ) {
			return array(
				'success' => false,
				'message' => esc_html__( 'Requester could not be anonymized due to prayers that could not be anonymized or deleted.', 'intercessor' )
			);
		}
	}

	// Loop through all their email addresses, and remove any additional email addresses.
	foreach ( $requester->emails as $email ) {
		$requester->remove_email( $email );
	}

	$requester->update(
		array(
			'name'         => esc_html__( 'Anonymized Requester', 'intercessor' ),
			'email'        => intercessor_anonymize_email( $requester->email ),
			'date_created' => gmdate( 'Y-m-d H:i:s', 0 ),
			'notes'        => '',
			'user_id'      => 0,
		)
	);

	/**
	 * Run further anonymization on a requester
	 *
	 * Developers and extensions can use the \Intercessor\Requester object passed into the intercessor_anonymize_requester action
	 * to complete further anonymization.
	 *
	 * @since 0.9.5
	 *
	 * @param \Intercessor\Requester $requester The \Intercessor\Requester object that was found.
	 */
	do_action( 'intercessor_anonymize_requester', $requester );

	$requester->add_note( esc_html__( 'Requester anonymized successfully', 'intercessor' ) );

	$anonimized = array(
		'success' => true,
		'message' => sprintf(
			esc_html__( 'Requester ID %d successfully anonymized.', 'intercessor' ),
			$requester_id
		)
	);
	
	return $anonimized;
}

/**
 * Given a prayer ID, anonymize the data related to that prayer.
 *
 * Only the prayer record is affected in this function. The data that is changed:
 * - First Name is made blank
 * - Last  Name is made blank
 * - All email addresses are converted to the anonymized email address on the requester
 *
 * @param int $prayer_id
 *
 * @return array
 */
function _intercessor_anonymize_prayer( $prayer_id = 0 ) {

	$prayer = intercessor_get_prayer( $prayer_id );
	if ( ! $prayer ) {
		return array( 
			'success' => false, 
			'message' => sprintf( 
				__( 'No prayer with ID %d.', 'intercessor' ), 
				$prayer_id 
			) 
		);
	}

	/**
	 * Determines if this prayer should be allowed to be anonymized.
	 *
	 * Developers and extensions can use this filter to make it possible to not anonymize a prayer. A sample use case
	 * would be if the prayer is pending requests, and the prayer requires shipping, anonymizing the prayer may
	 * not be ideal.
	 *
	 * @since 0.9.5
	 *
	 * @param array {
	 *     Contains data related to if the anonymization should take place
	 *
	 *     @type bool   $should_anonymize If the prayer should be anonymized.
	 *     @type string $message          A message to display if the requester could not be anonymized.
	 * }
	 */
	$should_anonymize_prayer = apply_filters( 'intercessor_should_anonymize_prayer', array( 
		'should_anonymize' => true, 
		'message' => array() 
	), $prayer );

	if ( ! $should_anonymize_prayer['should_anonymize'] ) {
		return array( 
			'success' => false, 
			'message' => $should_anonymize_prayer['message'] 
		);
	}

	$action = _intercessor_privacy_get_prayer_action( $prayer );

	switch( $action ) {

		case 'none':
		default:
			$return = array(
				'success' => false,
				'message' => sprintf( __( 'Prayer not modified, due to status: %s.', 'intercessor' ), $prayer->status )
			);
			break;

		case 'delete':
			intercessor_delete_prayer( $prayer->ID, true, true );

			$return = array(
				'success' => true,
				'message' => sprintf( __( 'Prayer %d with status %s deleted.', 'intercessor' ), $prayer->ID, $prayer->status )
			);
			break;

		case 'anonymize':
			$requester 			= new \Intercessor\Requester( $prayer->requester_id );
			$prayer->email 		= $requester->email;
			$prayer->first_name = '';
			$prayer->last_name  = '';


			/**
			 * Run further anonymization on a prayer
			 *
			 * Developers and extensions can use the \Intercessor\Prayer object passed into the intercessor_anonymize_prayer action
			 * to complete further anonymization.
			 *
			 * @since 0.9.5
			 *
			 * @param \Intercessor\Prayer $prayer The \Intercessor\Prayer object that was found.
			 */
			do_action( 'intercessor_anonymize_prayer', $prayer );

			$prayer->save();
			$return = array(
				'success' => true,
				'message' => sprintf( __( 'Prayer ID %d successfully anonymized.', 'intercessor' ), $prayer_id )
			);
			break;
	}

	return $return;
}

/**
 * Given an \Intercessor\Prayer, determine what action should be taken during the eraser processes.
 *
 * @since 0.9.5
 *
 * @param \Intercessor\Prayer $prayer
 *
 * @return string
 */
function _intercessor_privacy_get_prayer_action( \Intercessor\Prayer $prayer ) {

	$action = intercessor_get_option( 'prayer_privacy_status_action_' . $prayer->status, false );

	// Use defaults, if the Admin has not saved any special settings for the actions to be taken.
	if ( empty( $action ) ) {

		switch ( $prayer->status ) {

			case 'active':
			case 'personal':
				$action = 'anonymize';
				break;
				
			case 'pending':
			case 'archived':
			default:
				$action = 'none';
				break;

		}

	}

	/**
	 * Allow filtering of what type of action should be taken for a prayer.
	 *
	 * Developers and extensions can use this filter to modify how Intercessor will treat a prayer
	 * that has been requested to be deleted or anonymized.
	 *
	 * @since 0.9.5
	 *
	 * @param string      $action  What action will be performed (none, delete, anonymize)
	 * @param \Intercessor\Prayer $prayer The \Intercessor\Prayer object that has been requested to be anonymized or deleted.
	 */
	$action = apply_filters( 'intercessor_privacy_prayer_status_action_' . $action, $action, $prayer );

	return $action;

}

/**
 * Since our eraser callbacks need to look up a stored requester ID by hashed email address, developers can use this
 * to retrieve the requester ID associated with an email address that's being requested to be deleted even after the
 * requester has been anonymized.
 *
 * @since 0.9.5
 *
 * @param $email_address
 *
 * @return \Intercessor\Requester
 */
function _intercessor_privacy_get_requester_id_for_email( $email_address ) {
	$requester_id = get_option( 'intercessor_priv_' . md5( $email_address ), true );
	$requester    = new \Intercessor\Requester( $requester_id );

	return $requester;
}

/** Exporter Functions */

/**
 * Register any of our Privacy Data Exporters
 *
 * @since 0.9.5
 *
 * @param $exporters
 *
 * @return array
 */
function intercessor_register_privacy_exporters( $exporters = array() ) {

	$exporters[] = array(
		'exporter_friendly_name' => __( 'Requester Record', 'intercessor' ),
		'callback'               => 'intercessor_privacy_requester_record_exporter',
	);

	$exporters[] = array(
		'exporter_friendly_name' => __( 'Prayer Information', 'intercessor' ),
		'callback'               => 'intercessor_privacy_prayer_information_exporter',
	);

	return $exporters;

}
add_filter( 'wp_privacy_personal_data_exporters', 'intercessor_register_privacy_exporters' );

/**
 * Retrieves the Requester record for the Privacy Data Exporter
 *
 * @since 0.9.5
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function intercessor_privacy_requester_record_exporter( $email_address = '', $page = 1 ) {

	$requester    = new \Intercessor\Requester( $email_address );

	if ( empty( $requester->id ) ) {
		return array( 'data' => array(), 'done' => true );
	}

	$export_data = array(
		'group_id'    => 'intercessor-requester-record',
		'group_label' => __( 'Requester Record', 'intercessor' ),
		'item_id'     => "intercessor-requester-record-{$requester->id}",
		'data'        => array(
			array(
				'name'  => __( 'Requester ID', 'intercessor' ),
				'value' => $requester->id
			),
			array(
				'name'  => __( 'Primary Email', 'intercessor' ),
				'value' => $requester->email
			),
			array(
				'name'  => __( 'Name', 'intercessor' ),
				'value' => $requester->name
			),
			array(
				'name'  => __( 'Date Created', 'intercessor' ),
				'value' => $requester->date_created
			),
			array(
				'name'  => __( 'All Email Addresses', 'intercessor' ),
				'value' => implode( ', ', $requester->emails )
			),
		)
	);

	$agree_to_terms_time = $requester->get_meta( 'agree_to_terms_time', false );
	if ( ! empty( $agree_to_terms_time ) ) {
		foreach ( $agree_to_terms_time as $timestamp ) {
			$export_data['data'][] = array(
				'name' => __( 'Agreed to Terms', 'intercessor' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp )
			);
		}
	}

	$agree_to_privacy_time = $requester->get_meta( 'agree_to_privacy_time', false );
	if ( ! empty( $agree_to_privacy_time ) ) {
		foreach ( $agree_to_privacy_time as $timestamp ) {
			$export_data['data'][] = array(
				'name' => __( 'Agreed to Privacy Policy', 'intercessor' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', $timestamp )
			);
		}
	}

	return array( 
		'data' => array( $export_data ), 
		'done' => true 
	);
}

/**
 * Retrieves the billing information for the Privacy Exporter
 *
 * @since 0.9.5
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function intercessor_privacy_prayer_information_exporter( $email_address = '', $page = 1 ) {

	$requester = new \Intercessor\Requester( $email_address );
	
	$prayers = intercessor_get_prayers( array(
		'requester_id' => $requester->id,
		'number'       => 30,
		'offset'       => ( 30 * $page ) - 30,
	) );

	// Bail if we haven't found any prayers for this page.
	if ( empty( $prayers ) ) {
		return array( 
			'data' => array(), 
			'done' => true 
		);
	}

	$export_items = array();
	foreach ( $prayers as $prayer ) {

		$prayer_items 	= array();
		$prayer_request = implode( ', ', $prayer_items );
		$prayer_name 	= array();
		
		if ( ! empty( $prayer->first_name ) ) {
			$prayer_name[] = $prayer->first_name;
		}

		if ( ! empty( $prayer->last_name ) ) {
			$prayer_name[] = $prayer->last_name;
		}
		$prayer_name = implode( ' ', array_values( $prayer_name ) );

		$data_points = array(
			array(
				'name'  => __( 'Prayer ID / Number', 'intercessor' ),
				'value' => $prayer->id,
			),
			array(
				'name' => __( 'Prayer Date', 'intercessor' ),
				'value' => date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $prayer->date ) ),
			),
			array(
				'name' => __( 'Prayer Activated Date', 'intercessor' ),
				'value' =>  ! empty( $prayer->start_date )
					? date_i18n( get_option( 'date_format' ) . ' H:i:s', strtotime( $prayer->start_date ) )
					: '',
			),
			array(
				'name' => __( 'Prayer Requests', 'intercessor' ),
				'value' => $prayer_request,
			),
			array(
				'name'  => __( 'Email Address', 'intercessor' ),
				'value' => ! empty( $prayer->email ) ? $prayer->email : '',
			),
			array(
				'name'  => __( 'Status', 'intercessor' ),
				'value' => intercessor_get_prayer_status_label( $prayer->status ),
			),
		);

		$data_points = apply_filters( 'intercessor_privacy_order_details_item', $data_points, $prayer );

		$export_items[] = array(
			'group_id'    => 'intercessor-prayer-details',
			'group_label' => __( 'Requester Prayers', 'intercessor' ),
			'item_id'     => "intercessor-prayer-details-{$prayer->ID}",
			'data'        => $data_points,
		);
	}

	// Add the data to the list, and tell the exporter to come back for the next page of prayers.
	return array(
		'data' => $export_items,
		'done' => false,
	);
}

/** Anonymization Functions */

/**
 * This registers a single eraser _very_ early to avoid any other hook into the IPR data from running first.
 *
 * We are going to set an option of what requester we're currently deleting for what email address, so that after the requester
 * is anonymized we can still find them. Then we'll delete it.
 *
 * @param array $erasers
 */
function intercessor_register_privacy_eraser_requester_id_lookup( $erasers = array() ) {
	$erasers[] = array(
		'eraser_friendly_name' => 'pre-eraser-requester-id-lookup',
		'callback'             => 'intercessor_privacy_prefetch_requester_id',
	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'intercessor_register_privacy_eraser_requester_id_lookup', 5, 1 );

/**
 * Lookup the requester ID for this email address so that we can use it later in the anonymization process.
 *
 * @param     $email_address
 * @param int $page
 *
 * @return array
 */
function intercessor_privacy_prefetch_requester_id( $email_address, $page = 1 ) {
	$requester = new \Intercessor\Requester( $email_address );
	update_option( 'intercessor_priv_' . md5( $email_address ), $requester->id, false );

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * This registers a single eraser _very_ late to remove a requester ID that was found for the erasers.
 *
 * We are now assumed done with our exporters, so we can go ahead and delete the requester ID we found for this eraser.
 *
 * @param array $erasers
 */
function intercessor_register_privacy_eraser_requester_id_removal( $erasers = array() ) {
	$erasers[] = array(
		'eraser_friendly_name' => __( 'Possibly Delete Requester', 'intercessor' ),
		'callback'             => 'intercessor_privacy_maybe_delete_requester_eraser',
	);

	$erasers[] = array(
		'eraser_friendly_name' => 'post-eraser-requester-id-lookup',
		'callback'             => 'intercessor_privacy_remove_requester_id',
	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'intercessor_register_privacy_eraser_requester_id_removal', 9999, 1 );

/**
 * Delete the requester ID for this email address that was found in intercessor_privacy_prefetch_requester_id()
 *
 * @param     $email_address
 * @param int $page
 *
 * @return array
 */
function intercessor_privacy_remove_requester_id( $email_address, $page = 1 ) {
	delete_option( 'intercessor_priv_' . md5( $email_address ) );

	return array(
		'items_removed'  => false,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * If after the prayer anonymization/erasure methods have been run, and there are no longer prayers
 * for the requested requester, go ahead and delete the requester
 *
 * @since 0.9.5
 *
 * @param string $email_address The email address requesting anonymization/erasure
 * @param int    $page          The page (not needed for this query)
 *
 * @return array
 */
function intercessor_privacy_maybe_delete_requester_eraser( $email_address, $page = 1 ) {
	$requester = _intercessor_privacy_get_requester_id_for_email( $email_address );

	if ( empty( $requester->id ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	$prayers = intercessor_get_prayers( array(
		'requester_id' => $requester->id,
		'number'      => 30,
		'offset'      => ( 30 * $page ) - 30,
	) );

	if ( ! empty( $prayers ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(
				sprintf( __( 'Requester for %s not deleted, due to remaining prayers.', 'intercessor' ), $email_address ),
			),
			'done'           => true,
		);
	}

	if ( empty( $prayers ) ) {
		global $wpdb;

		$deleted_requester = intercessor()->requesters->delete( $requester->id );
		if ( $deleted_requester ) {
			$requester_meta_table = intercessor()->requester_meta->table_name;
			$deleted_meta = $wpdb->query( "DELETE FROM {$requester_meta_table} WHERE requester_id = {$requester->id}" );

			return array(
				'items_removed'  => true,
				'items_retained' => false,
				'messages'       => array(
					sprintf( __( 'Requester for %s successfully deleted.', 'intercessor' ), $email_address ),
				),
				'done'           => true,
			);
		}
	}

		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(
				sprintf( __( 'Requester for %s failed to be deleted.', 'intercessor' ), $email_address ),
			),
			'done'           => true,
		);
}

/**
 * Register eraser for IPR Data
 *
 * @since 0.9.5
 *
 * @param array $erasers
 *
 * @return array
 */
function intercessor_register_privacy_erasers( $erasers = array() ) {

	// The order of these matter, requester needs to be anonymized prior to the requester, so that the prayer can adopt
	// properties of the requester like email.

	$erasers[] = array(
		'eraser_friendly_name' => __( 'Requester Record', 'intercessor' ),
		'callback'             => 'intercessor_privacy_requester_anonymizer',
	);

	$erasers[] = array(
		'eraser_friendly_name' => __( 'Prayer Record', 'intercessor' ),
		'callback'             => 'intercessor_privacy_prayer_eraser',
	);

	return $erasers;

}
add_filter( 'wp_privacy_personal_data_erasers', 'intercessor_register_privacy_erasers', 11, 1 );

/**
 * Anonymize a requester record through the WP Core Privacy Data Eraser methods.
 *
 * @since 0.9.5
 *
 * @param     $email_address
 * @param int $page
 *
 * @return array
 */
function intercessor_privacy_requester_anonymizer( $email_address, $page = 1 ) {
	$requester = _intercessor_privacy_get_requester_id_for_email( $email_address );

	$anonymized = _intercessor_anonymize_requester( $requester->id );
	if ( empty( $anonymized['success'] ) ) {
		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( $anonymized['message'] ),
			'done'           => true,
		);
	}

	return array(
		'items_removed'  => true,
		'items_retained' => false,
		'messages'       => array( sprintf( __( 'Requester for %s has been anonymized.', 'intercessor' ), $email_address ) ),
		'done'           => true,
	);
}

/**
 * Anonymize a prayer record through the WP Core Privacy Data Eraser methods.
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function intercessor_privacy_prayer_eraser( $email_address, $page = 1 ) {
	$requester = _intercessor_privacy_get_requester_id_for_email( $email_address );

	$prayers = intercessor_get_prayers( array(
		'requester_id' => $requester->id,
		'number'      => 30,
		'offset'      => ( 30 * $page ) - 30,
	) );

	if ( empty( $prayers ) ) {

		$message = 1 === $page
			? sprintf( __( 'No prayers found for %s.', 'intercessor' ), $email_address )
			: sprintf( __( 'All eligible prayers anonymized or deleted for %s.', 'intercessor' ), $email_address );

		return array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array( $message ),
			'done'           => true,
		);
	}

	$items_removed  = null;
	$items_retained = null;
	$messages       = array();
	
	foreach ( $prayers as $prayer ) {
		$result = _intercessor_anonymize_prayer( $prayer->ID );

		if ( ! is_null( $items_removed ) && $result['success'] ) {
			$items_removed = true;
		}

		if ( ! is_null( $items_removed ) && ! $result['success'] ) {
			$items_retained = true;
		}

		$messages[] = $result['message'];
	}

	return array(
		'items_removed'  => ! is_null( $items_removed ) ? $items_removed : false,
		'items_retained' => ! is_null( $items_retained ) ? $items_retained : false,
		'messages'       => $messages,
		'done'           => false,
	);
}