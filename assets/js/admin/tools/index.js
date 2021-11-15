/**
 * Intercessor Admin Tools JS
 *
 * @package:     Intercessor
 * @copyright:   Copyright (c) 2020, Victor Aigbeghian
 * @license:     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

var intercessor_vars;

jQuery( document ).ready( function( $ ) {
    const Intercessor_Tools = {

        init: function() {
            this.recount_stats();
        },
    
        recount_stats: function() {
            $( document.body ).on( 'change', '#recount-stats-type', function() {
                const export_form = $( '#intercessor-tools-recount-form' ),
                    selected_type = $( 'option:selected', this ).data( 'type' ),
                    submit_button = $( '#recount-stats-submit' ),
                    prayers = $( '#tools-prayer-dropdown' );
    
                // Reset the form
                export_form.find( '.notice-wrap' ).remove();
                submit_button.removeClass( 'button-disabled' ).attr( 'disabled', false );
                prayers.hide();
                $( '.intercessor-recount-stats-descriptions span' ).hide();
    
                if ( 'recount-download' === selected_type ) {
                    prayers.show();
                    prayers.find( '.intercessor-select-chosen' ).css( 'width', 'auto' );
                } else if ( 'reset-stats' === selected_type ) {
                    export_form.append( '<div class="notice-wrap"></div>' );
                    const notice_wrap = export_form.find( '.notice-wrap' );
                    notice_wrap.html( '<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' + intercessor_vars.reset_stats_warn + '</label></p></div>' );
    
                    $( '#recount-stats-submit' ).addClass( 'button-disabled' ).attr( 'disabled', 'disabled' );
                } else {
                    prayers.hide();
                    prayers.val( 0 );
                }
    
                $( '#' + selected_type ).show();
            } );
    
            $( document.body ).on( 'change', '#confirm-reset', function() {
                const checked = $( this ).is( ':checked' );
                if ( checked ) {
                    $( '#recount-stats-submit' ).removeClass( 'button-disabled' ).removeAttr( 'disabled' );
                } else {
                    $( '#recount-stats-submit' ).addClass( 'button-disabled' ).attr( 'disabled', 'disabled' );
                }
            } );
    
            $( '#intercessor-tools-recount-form' ).submit( function( e ) {
                e.preventDefault();
    
                const selection = $( '#recount-stats-type' ).val(),
                    export_form = $( this ),
                    selected_type = $( 'option:selected', this ).data( 'type' );
    
                if ( 'reset-stats' === selected_type ) {
                    const is_confirmed = $( '#confirm-reset' ).is( ':checked' );
                    if ( is_confirmed ) {
                        return true;
                    }
                    has_errors = true;
                }
    
                export_form.find( '.notice-wrap' ).remove();
                export_form.append( '<div class="notice-wrap"></div>' );
    
                var notice_wrap = export_form.find( '.notice-wrap' ),
                    has_errors = false;
    
                if ( null === selection || 0 === selection ) {
                    // Needs to pick a method intercessor_vars.batch_export_no_class.
                    notice_wrap.html( '<div class="updated error"><p>' + intercessor_vars.batch_export_no_class + '</p></div>' );
                    has_errors = true;
                }
    
                if ( has_errors ) {
                    export_form.find( '.button-disabled' ).removeClass( 'button-disabled' );
                    return false;
                }
            } );
        },
    };

    Intercessor_Tools.init();
} );