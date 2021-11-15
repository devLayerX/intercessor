/**
 * Intercessor Admin Prayer Edit JS
 *
 * @package:     Intercessor
 * @copyright:   Copyright (c) 2020, Victor Aigbeghian
 * @license:     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

jQuery( document ).ready( function( $ ) {
    const Intercessor_Edit_Prayer = {

		init : function() {
			this.change_requester();
			this.new_requester();
			this.resend_notification();
			this.enter_key();
			this.add_note();
			this.remove_note();
		},

		change_requester : function() {

			$( '#intercessor-requester-details' ).on( 'click', '.intercessor-prayer-change-requester, .intercessor-prayer-change-requester-cancel', function( e ) {
				e.preventDefault();

				var requester_change = $( this ).hasClass( 'intercessor-prayer-change-requester' ),
					cancel = $( this ).hasClass( 'intercessor-prayer-change-requester-cancel' );

				if ( requester_change ) {
					$( '.requester-info' ).hide();
					$( '.change-requester' ).show();
					$( '.intercessor-prayer-change-requester-input' ).css( 'width', 'auto' );
				} else if ( cancel) {
					$( '.requester-info' ).show();
					$( '.change-requester' ).hide();
				}
			} );
		},

		new_requester : function() {

			$( '#intercessor-requester-details' ).on( 'click', '.intercessor-prayer-new-requester, .intercessor-prayer-new-requester-cancel', function( e ) {
				e.preventDefault();

				var new_requester = $( this ).hasClass( 'intercessor-prayer-new-requester' ),
					cancel = $( this ).hasClass( 'intercessor-prayer-new-requester-cancel' );

				if ( new_requester ) {
					$( '.requester-info' ).hide();
					$( '.new-requester' ).show();
				} else if ( cancel ) {
					$( '.requester-info' ).show();
					$( '.new-requester' ).hide();
				}

				var new_requester = $( '#intercessor-new-requester' );

				if ($( '.new-requester' ).is( ':visible' )) {
					new_requester.val(1);
				} else {
					new_requester.val(0);
				}
			} );
		},

		resend_notification : function() {
			var emails_wrap = $( '.intercessor-prayer-resend-notification-addresses' );

			$( document.body ).on( 'click', '#intercessor-select-notification-email', function( e ) {
				e.preventDefault();
				emails_wrap.slideDown();
			} );

			$( document.body ).on( 'change', '.intercessor-prayer-resend-notification-email', function() {
				var href = $( '#intercessor-select-notification-email' ).prop( 'href' ) + '&email=' + $( this ).val();

				if ( confirm( intercessor_admin.resend_notification ) ) {
					window.location = href;
				}
			} );

			$( document.body ).on( 'click', '#intercessor-resend-notification', function( e ) {
				return confirm( intercessor_admin.resend_notification );
			} );
		},
		
		enter_key: function() {
			$( document.body ).on( 'keydown', '#intercessor-note', function( e ) {
				if ( e.keyCode === 13 && ( e.metaKey || e.ctrlKey ) ) {
					e.preventDefault();
					$( '#intercessor-add-note' ).click();
				}
			} );
		},

		/**
		 * Ajax handler for adding new notes
		 *
		 * @since 1.0.0
		 */
		add_note: function() {
			$( '#intercessor-add-note' ).on( 'click', function( e ) {
				e.preventDefault();

				const intercessor_button = $( this ),
					intercessor_note = $( '#intercessor-note' ),
					intercessor_notes = $( '.intercessor-notes' ),
					intercessor_no_notes = $( '.intercessor-no-notes' ),
					intercessor_spinner = $( '.intercessor-add-note .spinner' ),
					intercessor_note_nonce = $( '#intercessor_note_nonce' );

				const postData = {
					action: 'intercessor_add_note',
					nonce: intercessor_note_nonce.val(),
					object_id: intercessor_button.data( 'object-id' ),
					object_type: intercessor_button.data( 'object-type' ),
					note: intercessor_note.val(),
				};

				if ( postData.note ) {
					intercessor_button.prop( 'disabled', true );
					intercessor_spinner.css( 'visibility', 'visible' );

					$.ajax( {
						type: 'POST',
						data: postData,
						url: ajaxurl,
						success: function( response ) {
							let res = wpAjax.parseAjaxResponse( response );
							res = res.responses[ 0 ];

							intercessor_notes.append( res.data );
							intercessor_no_notes.hide();
							intercessor_button.prop( 'disabled', false );
							intercessor_spinner.css( 'visibility', 'hidden' );
							intercessor_note.val( '' );
						},
					} ).fail( function( data ) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
						intercessor_button.prop( 'disabled', false );
						intercessor_spinner.css( 'visibility', 'hidden' );
					} );
				} else {
					const border_color = intercessor_note.css( 'border-color' );

					intercessor_note.css( 'border-color', 'red' );

					setTimeout( function() {
						intercessor_note.css( 'border-color', border_color );
					}, userInteractionInterval );
				}
			} );
		},

		/**
		 * Ajax handler for deleting existing notes
		 *
		 * @since 1.0.0
		 */
		remove_note: function() {
			$( document.body ).on( 'click', '.intercessor-delete-note', function( e ) {
				e.preventDefault();

				const intercessor_link = $( this ),
					intercessor_notes = $( '.intercessor-note' ),
					intercessor_note = intercessor_link.parents( '.intercessor-note' ),
					intercessor_no_notes = $( '.intercessor-no-notes' ),
					intercessor_note_nonce = $( '#intercessor_note_nonce' );

				if ( confirm( intercessor_vars.delete_note ) ) {
					const postData = {
						action: 'intercessor_delete_note',
						nonce: intercessor_note_nonce.val(),
						note_id: intercessor_link.data( 'note-id' ),
					};

					intercessor_note.addClass( 'deleting' );

					$.ajax( {
						type: 'POST',
						data: postData,
						url: ajaxurl,
						success: function( response ) {
							if ( '1' === response ) {
								intercessor_note.remove();
							}

							if ( intercessor_notes.length === 1 ) {
								intercessor_no_notes.show();
							}

							return false;
						},
					} ).fail( function( data ) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
						intercessor_note.removeClass( 'deleting' );
					} );
					return true;
				}
			} );
		},
	};
	Intercessor_Edit_Prayer.init();
} );