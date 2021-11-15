/**
 * Intercessor Admin ExportJS
 *
 * @package:     Intercessor
 * @copyright:   Copyright (c) 2020, Victor Aigbeghian
 * @license:     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

jQuery( document ).ready( function( $ ) {
	const Intercessor_Export = {

		init : function() {
			this.submit();
			this.dismiss_message();
		},

		submit : function() {
			const self = this;

			$( document.body ).on( 'submit', '.intercessor-export-form', function( e ) {
				e.preventDefault();

				const submitButton = $( this ).find( 'input[type="submit"]' ).first();

				if ( submitButton.hasClass( 'button-disabled' ) || submitButton.is( ':disabled' ) ) {
                    return;
                }
                const data = $( this ).serialize();

                submitButton.addClass( 'button-disabled' );
                $( this ).find( '.notice-wrap' ).remove();
                $( this ).append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="intercessor-progress"><div></div></div></div>' );

                // start the process
                self.process_step( 1, data, self );
			} );
		},

		process_step : function( step, data, self ) {
			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					form: data,
					action: 'intercessor_do_ajax_export',
					step: step,
				},
				dataType: "json",
				success: function( response ) {
					if ( 'done' == response.step || response.error || response.success ) {

						// We need to get the actual in progress form, not all forms on the page.
						const export_form = $( '.intercessor-export-form' ).find( '.intercessor-progress' ).parent().parent();
						const notice_wrap = export_form.find( '.notice-wrap' );

						export_form.find( '.button-disabled' ).removeClass( 'button-disabled' );

						if ( response.error ) {
							const error_message = response.message;
							notice_wrap.html( '<div class="updated error"><p>' + error_message + '</p></div>' );
						} else if ( response.success ) {
							const success_message = response.message;
							notice_wrap.html( '<div id="intercessor-batch-success" class="updated notice is-dismissible"><p>' + success_message + '<span class="notice-dismiss"></span></p></div>' );
						} else {
							notice_wrap.remove();
							window.location = response.url;
						}
					} else {
						$( '.intercessor-progress div' ).animate( {
							width: response.percentage + '%',
						}, 50, function() {
							// Animation complete.
						} );
						self.process_step( parseInt( response.step ), data, self );
					}

				}
			} ).fail( function ( response ) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			} );
		},

		dismiss_message : function() {
			$( document.body ).on( 'click', '#intercessor-batch-success .notice-dismiss', function() {
				$( '#intercessor-batch-success' ).parent().slideUp( 'fast' );
			} );
		}
	};

	Intercessor_Export.init();
} );