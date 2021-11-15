/**
 * Intercessor Admin Notes JS
 *
 * @package:     Intercessor
 * @copyright:   Copyright (c) 2020, Victor Aigbeghian
 * @license:     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

var intercessor_vars;

jQuery( document ).ready( function( $ ) {
    const Intercessor_Requester = {

        vars: {
            requester_card_wrap_editable:  $( '.intercessor-requester-card-wrapper .editable' ),
            requester_card_wrap_edit_item: $( '.intercessor-requester-card-wrapper .edit-item' ),
            user_id: $( 'input[name="requesterinfo[user_id]"]' ),
            note: $( '#requester-note' )
        },
    
        init : function() {
            this.edit_requester();
            this.add_email();
            this.user_search();
            this.remove_user();
            this.cancel_edit();
            this.delete_checked();
        },
    
        edit_requester: function() {
            $( document.body ).on( 'click', '#edit-requester', function( e ) {
                e.preventDefault();

                Intercessor_Requester.vars.requester_card_wrap_editable.hide();
                Intercessor_Requester.vars.requester_card_wrap_edit_item.fadeIn().css( 'display', 'block' );
            } );
        },
    
        add_email: function() {
            $( document.body ).on( 'click', '#add-requester-email', function( e ) {
                e.preventDefault();
                const button = $( this ),
                    wrapper = button.parent().parent().parent().parent(),
                    requester_id = wrapper.find( 'input[name="requester-id"]' ).val(),
                    email = wrapper.find( 'input[name="additional-email"]' ).val(),
                    primary = wrapper.find( 'input[name="make-additional-primary"]' ).is( ':checked' ),
                    nonce = wrapper.find( 'input[name="add_email_nonce"]' ).val(),
                    postData = {
                        intercessor_action: 'requester-add-email',
                        requester_id: requester_id,
                        email: email,
                        primary: primary,
                        _wpnonce: nonce,
                    };

                wrapper.parent().find( '.notice-container' ).remove();
                wrapper.find( '.spinner' ).css( 'visibility', 'visible' );
                button.attr( 'disabled', true );

                $.post( ajaxurl, postData, function( response ) {
                    setTimeout( function() {
                        if ( true === response.success ) {
                            window.location.href = response.redirect;
                        } else {
                            button.attr( 'disabled', false );
                            wrapper.before( '<div class="notice-container"><div class="notice notice-error inline"><p>' + response.message + '</p></div></div>' );
                            wrapper.find( '.spinner' ).css( 'visibility', 'hidden' );
                        }
                    }, 342 );
                }, 'json' );
            } );
        },
    
        user_search: function() {
            // Upon selecting a user from the dropdown, we need to update the User ID
            $( document.body ).on( 'click.iprSelectUser', '.intercessor_user_search_results a', function( e ) {
                e.preventDefault();
                const user_id = $( this ).data( 'userid' );
                Intercessor_Requester.vars.user_id.val( user_id );
            } );
        },
        remove_user: function() {
            $( document.body ).on( 'click', '#disconnect-requester', function( e ) {

                e.preventDefault();

                if ( confirm( intercessor_vars.disconnect_requester ) ) {

                    const requester_id = $( 'input[name="requesterinfo[id]"]' ).val();

                        postData = {
                            intercessor_action: 'disconnect-userid',
                            requester_id: requester_id,
                            _wpnonce: $( '#edit-requester-info #_wpnonce' ).val()
                        };

                    $.post(ajaxurl, postData, function( response ) {
                        window.location.href=window.location.href;
                    }, 'json' );
                }

            } );
        },
        cancel_edit: function() {
            $( document.body ).on( 'click', '#intercessor-edit-requester-cancel', function( e ) {
                e.preventDefault();
                Intercessor_Requester.vars.requester_card_wrap_edit_item.hide();
                Intercessor_Requester.vars.requester_card_wrap_editable.show();

                $( '.intercessor_user_search_results' ).html( '' );
            } );
        },
        delete_checked: function() {
            $( '#intercessor-requester-delete-confirm' ).change( function() {
                const records_input = $( '#intercessor-requester-delete-records' );
                const submit_button = $( '#intercessor-delete-requester' );

                if ( $(this).prop( 'checked' ) ) {
                    records_input.attr( 'disabled', false );
                    submit_button.attr( 'disabled', false );
                } else {
                    records_input.attr( 'disabled', true );
                    records_input.prop( 'checked', false );
                    submit_button.attr( 'disabled', true );
                }
            } );
        }

    };

    Intercessor_Requester.init();
} ); 