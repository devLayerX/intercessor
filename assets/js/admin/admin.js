/**
 * Intercessor admin JavaScript.
 *
 * Handles progressive-enhancement behaviours in the Intercessor admin pages.
 * All critical functionality (form submission, moderation) works without JS;
 * this file adds convenience UX only.
 *
 * @package Intercessor
 * @since   1.0.0
 */

/* global jQuery */
( function ( $ ) {
	'use strict';

	/**
	 * Auto-dismiss WordPress admin notices after 5 seconds.
	 * Standard dismissible notices already have a close button; this just
	 * removes them automatically for a cleaner workflow.
	 */
	function autoDismissNotices() {
		setTimeout( function () {
			$( '.intercessor-dashboard .notice.is-dismissible, ' +
			   '.wrap .notice.notice-success.is-dismissible' ).fadeOut( 400 );
		}, 5000 );
	}

	/**
	 * Confirm destructive bulk-delete action before the form is submitted.
	 * Intercepts the bulk action form on the Prayer Requests list table.
	 */
	function confirmBulkDelete() {
		$( 'body' ).on( 'submit', 'form[action*="admin-post.php"]', function ( e ) {
			var $form   = $( this );
			var action  = $form.find( '[name="bulk_action"]' ).val();
			var checked = $form.find( 'input[name="bulk_ids[]"]:checked' ).length;

			if ( action !== 'bulk_delete' || checked === 0 ) {
				return;
			}

			var message = window.intercessorAdmin && window.intercessorAdmin.i18n
				? window.intercessorAdmin.i18n.confirmDelete
				: 'Permanently delete the selected prayer requests? This cannot be undone.';

			if ( ! window.confirm( message ) ) {
				e.preventDefault();
			}
		} );
	}

	/**
	 * Highlight the currently active status filter tab link.
	 */
	function highlightActiveTab() {
		var params = new URLSearchParams( window.location.search );
		var filter = params.get( 'status_filter' ) || '';

		$( '.subsubsub a' ).each( function () {
			var href   = $( this ).attr( 'href' ) || '';
			var hParam = new URLSearchParams( href.split( '?' )[ 1 ] || '' );
			if ( ( hParam.get( 'status_filter' ) || '' ) === filter ) {
				$( this ).addClass( 'current' );
			}
		} );
	}

	$( function () {
		autoDismissNotices();
		confirmBulkDelete();
		highlightActiveTab();
	} );

} )( jQuery );
