<?php
/**
 * REST API endpoint registration.
 *
 * @package Intercessor
 * @since   1.0.0
 */
declare(strict_types=1);

namespace Intercessor\Http;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


use Intercessor\Admin\Settings;
use Intercessor\Database\Query\Prayer_History_Query;
use Intercessor\Database\Query\Prayer_Note_Query;
use Intercessor\Database\Query\Prayer_Request_Query;
use Intercessor\Database\Query\Requester_Query;
use Intercessor\Database\Row\Prayer_Note;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Registers and handles all Intercessor REST API endpoints.
 *
 * Base namespace: /wp-json/intercessor/v1/
 *
 * Public endpoints (no auth required):
 *   GET  /requests                    — list approved requests
 *   GET  /requests/{id}               — single request
 *   GET  /requests/{id}/history       — status timeline
 *
 * Authenticated (canSubmit):
 *   POST /requests                    — submit new prayer request
 *
 * Moderator only (canModerate checks edit_prayers capability):
 *   POST  /requests/{id}/status       — update request status
 *   GET   /requesters                 — list all requesters
 *   GET   /requests/{id}/notes        — list notes on a request
 *   POST  /requests/{id}/notes        — create a note
 *   DELETE /requests/{id}/notes/{nid} — delete a note
 *
 * @since   1.0.0
 * @package Intercessor
 */
final class Rest_Api {

	/**
	 * REST API namespace for all Intercessor routes.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private const NAMESPACE = 'intercessor/v1';

	/**
	 * Register the 'rest_api_init' hook.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register all REST routes.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_routes(): void {

		// ── GET  /requests ────────────────────────────────────────────────
		register_rest_route(
			self::NAMESPACE,
			'/requests',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_requests' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'per_page' => array(
						'type'              => 'integer',
						'default'           => 10,
						'minimum'           => 1,
						'maximum'           => 100,
						'sanitize_callback' => 'absint',
					),
					'page'     => array(
						'type'              => 'integer',
						'default'           => 1,
						'minimum'           => 1,
						'sanitize_callback' => 'absint',
					),
					'status'   => array(
						'type'              => 'string',
						'default'           => 'approved',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// ── GET  /requests/{id} ───────────────────────────────────────────
		register_rest_route(
			self::NAMESPACE,
			'/requests/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_request' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// ── POST /requests ────────────────────────────────────────────────
		register_rest_route(
			self::NAMESPACE,
			'/requests',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_request' ),
				'permission_callback' => array( $this, 'can_submit' ),
				'args'                => $this->submission_args(),
			)
		);

		// ── POST /requests/{id}/status ────────────────────────────────────
		register_rest_route(
			self::NAMESPACE,
			'/requests/(?P<id>[\d]+)/status',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_status' ),
				'permission_callback' => array( $this, 'can_moderate' ),
				'args'                => array(
					'id'     => array( 'type' => 'integer', 'required' => true,  'sanitize_callback' => 'absint' ),
					'status' => array( 'type' => 'string',  'required' => true,  'sanitize_callback' => 'sanitize_key' ),
					'note'   => array( 'type' => 'string',  'default'  => '',    'sanitize_callback' => 'sanitize_textarea_field' ),
				),
			)
		);

		// ── GET /requesters ───────────────────────────────────────────────
		register_rest_route(
			self::NAMESPACE,
			'/requesters',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_requesters' ),
				'permission_callback' => array( $this, 'can_moderate' ),
			)
		);

		// ── GET /requests/{id}/history ────────────────────────────────────
		register_rest_route(
			self::NAMESPACE,
			'/requests/(?P<id>[\d]+)/history',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_history' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array( 'type' => 'integer', 'required' => true, 'sanitize_callback' => 'absint' ),
				),
			)
		);

		// ── GET  /requests/{id}/notes ─────────────────────────────────────
		register_rest_route(
			self::NAMESPACE,
			'/requests/(?P<id>[\d]+)/notes',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_notes' ),
				'permission_callback' => array( $this, 'can_moderate' ),
				'args'                => array(
					'id'         => array( 'type' => 'integer', 'required' => true, 'sanitize_callback' => 'absint' ),
					'private'    => array(
						'type'              => 'string',
						'default'           => 'all',
						'enum'              => array( 'all', 'private', 'shared' ),
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// ── POST /requests/{id}/notes ─────────────────────────────────────
		register_rest_route(
			self::NAMESPACE,
			'/requests/(?P<id>[\d]+)/notes',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_note' ),
				'permission_callback' => array( $this, 'can_moderate' ),
				'args'                => array(
					'id'         => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'content'    => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
						'validate_callback' => static fn( $v ) => is_string( $v ) && trim( $v ) !== '',
					),
					'is_private' => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
			)
		);

		// ── DELETE /requests/{id}/notes/{nid} ────────────────────────────
		register_rest_route(
			self::NAMESPACE,
			'/requests/(?P<id>[\d]+)/notes/(?P<nid>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_note' ),
				'permission_callback' => array( $this, 'can_moderate' ),
				'args'                => array(
					'id'  => array( 'type' => 'integer', 'required' => true, 'sanitize_callback' => 'absint' ),
					'nid' => array( 'type' => 'integer', 'required' => true, 'sanitize_callback' => 'absint' ),
				),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Callbacks — requests
	// -------------------------------------------------------------------------

	/**
	 * Return a paginated list of prayer requests.
	 *
	 * Non-admin callers are always forced to status=approved to prevent
	 * information disclosure.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request REST request with per_page, page, status.
	 * @return WP_REST_Response
	 */
	public function get_requests( WP_REST_Request $request ): WP_REST_Response {
		$query   = new Prayer_Request_Query();
		$perPage = $request->get_param( 'per_page' );
		$page    = $request->get_param( 'page' );
		$status  = $request->get_param( 'status' );

		if ( ! current_user_can( 'edit_prayers' ) && $status !== 'approved' ) {
			$status = 'approved';
		}

		// Private requests are never exposed publicly via the REST API.
		if ( $status === 'private' && ! current_user_can( 'edit_prayers' ) ) {
			$status = 'approved';
		}

		$items = $query->get_items( array(
			'status'    => $status,
			'is_public' => 1,
			'number'    => $perPage,
			'offset'    => ( $page - 1 ) * $perPage,
			'orderby'   => 'date_created',
			'order'     => 'DESC',
		) );

		return new WP_REST_Response(
			array_map( array( $this, 'prepare_request_item' ), $items ),
			200
		);
	}

	/**
	 * Return a single prayer request by primary key.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request REST request with id.
	 * @return WP_REST_Response         200, 403, or 404.
	 */
	public function get_request( WP_REST_Request $request ): WP_REST_Response {
		$query = new Prayer_Request_Query();
		$item  = $query->get_item( $request->get_param( 'id' ) );

		if ( ! $item ) {
			return new WP_REST_Response( array( 'message' => __( 'Not found.', 'intercessor' ) ), 404 );
		}

		if ( ! $item->is_public() && ! current_user_can( 'edit_prayers' ) ) {
			return new WP_REST_Response( array( 'message' => __( 'Forbidden.', 'intercessor' ) ), 403 );
		}

		// Private requests are hidden from public REST consumers.
		if ( $item->is_private_status() && ! current_user_can( 'edit_prayers' ) ) {
			return new WP_REST_Response( array( 'message' => __( 'Forbidden.', 'intercessor' ) ), 403 );
		}

		return new WP_REST_Response( $this->prepare_request_item( $item ), 200 );
	}

	/**
	 * Create a new prayer request.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request REST request with name, email, subject, content, is_anonymous.
	 * @return WP_REST_Response         201 with new ID, or 500.
	 */
	public function create_request( WP_REST_Request $request ): WP_REST_Response {
		$requesterQuery = new Requester_Query();
		$requesterId    = $requesterQuery->find_or_create(
			$request->get_param( 'email' ),
			$request->get_param( 'first_name' ) ?? '',
			$request->get_param( 'last_name' )  ?? ''
		);

		if ( ! $requesterId ) {
			return new WP_REST_Response( array( 'message' => __( 'Could not create requester.', 'intercessor' ) ), 500 );
		}

		$autoApprove = Settings::get( 'auto_approve', false );
		$query       = new Prayer_Request_Query();

		$newId = $query->add_item( array(
			'requester_id' => $requesterId,
			'subject'      => $request->get_param( 'subject' ),
			'content'      => $request->get_param( 'content' ),
			'status'       => $autoApprove ? 'approved' : 'pending',
			'is_anonymous' => $request->get_param( 'is_anonymous' ) ? 1 : 0,
			'is_public'    => 1,
		) );

		if ( ! $newId ) {
			return new WP_REST_Response( array( 'message' => __( 'Could not save request.', 'intercessor' ) ), 500 );
		}

		return new WP_REST_Response( array( 'id' => $newId ), 201 );
	}

	/**
	 * Update the status of a prayer request.
	 *
	 * Also writes a PrayerHistory entry via Prayer_Request_Query::update_status().
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request REST request with id, status, note.
	 * @return WP_REST_Response         200 or 500.
	 */
	public function update_status( WP_REST_Request $request ): WP_REST_Response {
		$query   = new Prayer_Request_Query();
		$updated = $query->update_status(
			$request->get_param( 'id' ),
			$request->get_param( 'status' ),
			$request->get_param( 'note' )
		);

		if ( ! $updated ) {
			return new WP_REST_Response( array( 'message' => __( 'Update failed.', 'intercessor' ) ), 500 );
		}

		return new WP_REST_Response( array( 'updated' => true ), 200 );
	}

	/**
	 * Return all requester records (admin only).
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request REST request (no params).
	 * @return WP_REST_Response         200 with requester rows.
	 */
	public function get_requesters( WP_REST_Request $request ): WP_REST_Response {
		$query = new Requester_Query();
		$items = $query->get_items( array( 'orderby' => 'date_created', 'order' => 'DESC' ) );

		return new WP_REST_Response( $items, 200 );
	}

	/**
	 * Return the status-change history timeline for a prayer request.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request REST request with id.
	 * @return WP_REST_Response         200, 403, or 404.
	 */
	public function get_history( WP_REST_Request $request ): WP_REST_Response {
		$pqQuery = new Prayer_Request_Query();
		$parent  = $pqQuery->get_item( $request->get_param( 'id' ) );

		if ( ! $parent ) {
			return new WP_REST_Response( array( 'message' => __( 'Not found.', 'intercessor' ) ), 404 );
		}

		if ( ! $parent->is_public() && ! current_user_can( 'edit_prayers' ) ) {
			return new WP_REST_Response( array( 'message' => __( 'Forbidden.', 'intercessor' ) ), 403 );
		}

		$historyQuery = new Prayer_History_Query();
		$history      = $historyQuery->get_for_request( $request->get_param( 'id' ) );

		return new WP_REST_Response( $history, 200 );
	}

	// -------------------------------------------------------------------------
	// Callbacks — notes
	// -------------------------------------------------------------------------

	/**
	 * Return all notes for a given prayer request.
	 *
	 * Supports an optional 'private' filter: 'all' (default), 'private', 'shared'.
	 * Only moderators can call this endpoint.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request REST request with id and optional private filter.
	 * @return WP_REST_Response         200 with note array, or 404 if request not found.
	 */
	public function get_notes( WP_REST_Request $request ): WP_REST_Response {
		$requestId = $request->get_param( 'id' );
		$filter    = $request->get_param( 'private' );

		// Verify the parent prayer request exists.
		$pqQuery = new Prayer_Request_Query();
		if ( ! $pqQuery->get_item( $requestId ) ) {
			return new WP_REST_Response( array( 'message' => __( 'Prayer request not found.', 'intercessor' ) ), 404 );
		}

		$noteQuery = new Prayer_Note_Query();

		$notes = match ( $filter ) {
			'private' => $noteQuery->get_private_for_request( $requestId ),
			'shared'  => $noteQuery->get_items( array(
				'prayer_request_id' => $requestId,
				'is_private'        => 0,
				'orderby'           => 'date_created',
				'order'             => 'ASC',
				'number'            => 0,
			) ),
			default   => $noteQuery->get_for_request( $requestId ),
		};

		return new WP_REST_Response(
			array_map( array( $this, 'prepare_note_item' ), $notes ),
			200
		);
	}

	/**
	 * Create a new internal note on a prayer request.
	 *
	 * Only moderators can create notes. The author is always set to the
	 * currently authenticated WordPress user.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request REST request with id, content, is_private.
	 * @return WP_REST_Response         201 with new note data, 404, or 500.
	 */
	public function create_note( WP_REST_Request $request ): WP_REST_Response {
		$requestId = $request->get_param( 'id' );
		$content   = trim( (string) $request->get_param( 'content' ) );
		$private   = (bool) $request->get_param( 'is_private' );

		// Verify the parent prayer request exists.
		$pqQuery = new Prayer_Request_Query();
		if ( ! $pqQuery->get_item( $requestId ) ) {
			return new WP_REST_Response( array( 'message' => __( 'Prayer request not found.', 'intercessor' ) ), 404 );
		}

		$noteQuery = new Prayer_Note_Query();
		$newId     = $noteQuery->add_note( $requestId, $content, $private );

		if ( ! $newId ) {
			return new WP_REST_Response( array( 'message' => __( 'Could not save note.', 'intercessor' ) ), 500 );
		}

		$note = $noteQuery->get_item( $newId );

		return new WP_REST_Response(
			$note ? $this->prepare_note_item( $note ) : array( 'id' => $newId ),
			201
		);
	}

	/**
	 * Delete a single note by its ID.
	 *
	 * Validates that the note belongs to the stated prayer request (IDOR guard)
	 * before deleting.
	 *
	 * @since  1.0.0
	 * @param  WP_REST_Request $request REST request with id (request) and nid (note).
	 * @return WP_REST_Response         200, 403, or 404.
	 */
	public function delete_note( WP_REST_Request $request ): WP_REST_Response {
		$requestId = $request->get_param( 'id' );
		$noteId    = $request->get_param( 'nid' );

		$noteQuery = new Prayer_Note_Query();
		$note      = $noteQuery->get_item( $noteId );

		if ( ! $note ) {
			return new WP_REST_Response( array( 'message' => __( 'Note not found.', 'intercessor' ) ), 404 );
		}

		// IDOR guard: the note must belong to the stated prayer request.
		if ( $note->prayer_request_id !== $requestId ) {
			return new WP_REST_Response( array( 'message' => __( 'Note does not belong to this request.', 'intercessor' ) ), 403 );
		}

		$deleted = $noteQuery->delete_item( $noteId );

		if ( ! $deleted ) {
			return new WP_REST_Response( array( 'message' => __( 'Could not delete note.', 'intercessor' ) ), 500 );
		}

		return new WP_REST_Response( array( 'deleted' => true, 'id' => $noteId ), 200 );
	}

	// -------------------------------------------------------------------------
	// Permission callbacks
	// -------------------------------------------------------------------------

	/**
	 * Check whether the current caller may submit a prayer request.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	public function can_submit(): bool {
		if ( Settings::get( 'require_login', false ) ) {
			return is_user_logged_in();
		}

		return true;
	}

	/**
	 * Check whether the current caller may perform moderation actions.
	 *
	 * Used for status changes, note creation/deletion, and requester listing.
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	public function can_moderate(): bool {
		return current_user_can( 'edit_prayers' );
	}

	// -------------------------------------------------------------------------
	// Response shape helpers
	// -------------------------------------------------------------------------

	/**
	 * Shape a prayer request row into a REST-safe array.
	 *
	 * Sensitive fields (requester_id, moderator_note) are included only for
	 * callers with the edit_prayers capability.
	 *
	 * @since  1.0.0
	 * @param  object               $item Prayer request row.
	 * @return array<string, mixed>
	 */
	private function prepare_request_item( object $item ): array {
		$isAdmin = current_user_can( 'edit_prayers' );

		return array(
			'id'             => (int) $item->id,
			'subject'        => $item->subject,
			'content'        => $item->content,
			'status'         => $item->status,
			'is_anonymous'   => (bool) $item->is_anonymous,
			'date_created'   => $item->date_created,
			'requester_id'   => $isAdmin ? (int) $item->requester_id : null,
			'moderator_note' => $isAdmin ? $item->moderator_note     : null,
		);
	}

	/**
	 * Shape a PrayerNote row into a REST-safe array.
	 *
	 * Resolves the author's display name from the WordPress users table.
	 * The raw author_user_id is included so the client can link to user profiles.
	 *
	 * @since  1.0.0
	 * @param  Prayer_Note           $note Note row object.
	 * @return array<string, mixed>
	 */
	private function prepare_note_item( Prayer_Note $note ): array {
		$author     = get_user_by( 'id', $note->author_user_id );
		$authorName = $author ? $author->display_name : __( 'Unknown', 'intercessor' );

		return array(
			'id'                => $note->id,
			'prayer_request_id' => $note->prayer_request_id,
			'author_user_id'    => $note->author_user_id,
			'author_name'       => $authorName,
			'content'           => $note->content,
			'is_private'        => (bool) $note->is_private,
			'date_created'      => $note->date_created,
			'date_modified'     => $note->date_modified,
		);
	}

	/**
	 * Return the REST argument schema for the prayer request creation endpoint.
	 *
	 * @since  1.0.0
	 * @return array<string, array<string, mixed>>
	 */
	private function submission_args(): array {
		return array(
			'first_name'   => array( 'type' => 'string',  'required' => true,  'sanitize_callback' => 'sanitize_text_field' ),
			'last_name'    => array( 'type' => 'string',  'required' => false, 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ),
			'email'        => array( 'type' => 'string',  'required' => true,  'sanitize_callback' => 'sanitize_email', 'validate_callback' => 'is_email' ),
			'subject'      => array( 'type' => 'string',  'required' => true,  'sanitize_callback' => 'sanitize_text_field' ),
			'content'      => array( 'type' => 'string',  'required' => true,  'sanitize_callback' => 'sanitize_textarea_field' ),
			'is_anonymous' => array( 'type' => 'boolean', 'default'  => false ),
		);
	}
}
