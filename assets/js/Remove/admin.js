/* global intercessor_vars */
jQuery(document).ready(function($) {
    'use strict';

    // Initilize color picker.
    var intercessor_color_picker = $('.intercessor-color-picker');

    if ( intercessor_color_picker.length ) {
        intercessor_color_picker.wpColorPicker();
    }

    // Tooltips
    var tooltips = $('.intercessor-help-tip');
    intercessor_attach_tooltips( tooltips );

    /**
     * Date picker
     *
     * This juggles a few CSS classes to avoid styling collisions with other
     * third-party plugins.
     */
    var intercessor_datepicker = $( 'input.intercessor_datepicker' );
    if ( intercessor_datepicker.length > 0 ) {
        var dateFormat = 'mm/dd/yy';
        intercessor_datepicker.datepicker( {
            dateFormat: dateFormat
        } );
    }

    // WP 3.5+ uploader
    var file_frame;
    window.formfield = '';

    $( document.body ).on('click', '.intercessor_settings_upload_button', function(e) {

        e.preventDefault();

        var button = $(this);

        window.formfield = $(this).parent().prev();

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            frame: 'post',
            state: 'insert',
            title: button.data( 'uploader_title' ),
            button: {
                text: button.data( 'uploader_button_text' )
            },
            multiple: false
        } );

        file_frame.on( 'menu:render:default', function( view ) {
            // Store our views in an object.
            var views = {};

            // Unset default menu items
            view.unset( 'library-separator' );
            view.unset( 'gallery' );
            view.unset( 'featured-image' );
            view.unset( 'embed' );

            // Initialize the views in our view object.
            view.set( views );
        } );

        // When an image is selected, run a callback.
        file_frame.on( 'insert', function() {

            var selection = file_frame.state().get('selection');
            selection.each( function( attachment, index ) {
                attachment = attachment.toJSON();
                window.formfield.val(attachment.url);
            } );
        } );

        // Finally, open the modal
        file_frame.open();
    } );


    // Show the email template previews
    var email_preview_wrap = $('#email-preview-wrap');
    if( email_preview_wrap.length ) {
        var emailPreview = $('#email-preview');
        email_preview_wrap.colorbox({
            inline: true,
            href: emailPreview,
            width: '80%',
            height: 'auto'
        } );
    }

    /**
     * Notes
     */
    var IPR_Notes = {
        init : function() {
            this.enter_key();
            this.add_note();
            this.remove_note();
        },

        enter_key : function() {
            $(document.body).on('keydown', '#intercessor-note', function(e) {
                if (e.keyCode === 13 && ( e.metaKey || e.ctrlKey ) ) {
                    e.preventDefault();
                    $('#intercessor-add-note').click();
                }
            } );
        },

        /**
         * Ajax handler for adding new notes
         *
         * @since 1.0.0
         */
        add_note : function() {
            $('#intercessor-add-note').on('click', function(e) {
                e.preventDefault();

                var intercessor_button     = $( this ),
                    intercessor_note       = $('#intercessor-note'),
                    intercessor_notes      = $('.intercessor-notes'),
                    intercessor_no_notes   = $('.intercessor-no-notes'),
                    intercessor_spinner    = $('.intercessor-add-note .spinner'),
                    intercessor_note_nonce = $('#intercessor_note_nonce');

                var postData = {
                    action:      'intercessor_add_note',
                    nonce:       intercessor_note_nonce.val(),
                    object_id:   intercessor_button.data('object-id'),
                    object_type: intercessor_button.data('object-type'),
                    note:        intercessor_note.val()
                };

                if ( postData.note ) {
                    intercessor_button.prop('disabled', true);
                    intercessor_spinner.css('visibility', 'visible');

                    $.ajax({
                        type: 'POST',
                        data: postData,
                        url:  ajaxurl,
                        success: function (response) {
                            var res = wpAjax.parseAjaxResponse( response );
                            res = res.responses[0];

                            intercessor_notes.append( res.data );
                            intercessor_no_notes.hide();
                            intercessor_button.prop('disabled', false);
                            intercessor_spinner.css('visibility', 'hidden');
                            intercessor_note.val('');
                        }
                    }).fail(function (data) {
                        if ( window.console && window.console.log ) {
                            console.log( data );
                        }
                        intercessor_button.prop('disabled', false);
                        intercessor_spinner.css('visibility', 'hidden');
                    } );

                } else {
                    var border_color = intercessor_note.css('border-color');

                    intercessor_note.css('border-color', 'red');

                    setTimeout( function() {
                        intercessor_note.css('border-color', border_color );
                    }, userInteractionInterval );
                }
            } );
        },

        /**
         * Ajax handler for deleting existing notes
         *
         * @since 1.0.0
         */
        remove_note : function() {
            $( document.body ).on('click', '.intercessor-delete-note', function(e) {
                e.preventDefault();

                var intercessor_link       = $( this ),
                    intercessor_notes      = $('.intercessor-note'),
                    intercessor_note       = intercessor_link.parents( '.intercessor-note' ),
                    intercessor_no_notes   = $('.intercessor-no-notes'),
                    intercessor_note_nonce = $('#intercessor_note_nonce');

                if ( confirm( intercessor_admin.delete_note ) ) {
                    var postData = {
                        action:  'intercessor_delete_note',
                        nonce:   intercessor_note_nonce.val(),
                        note_id: intercessor_link.data('note-id')
                    };

                    intercessor_note.addClass('deleting');

                    $.ajax({
                        type: 'POST',
                        data: postData,
                        url:  ajaxurl,
                        success: function (response) {
                            if ( '1' === response ) {
                                intercessor_note.remove();
                            }

                            if ( intercessor_notes.length === 1 ) {
                                intercessor_no_notes.show();
                            }

                            return false;
                        }
                    }).fail(function (data) {
                        if ( window.console && window.console.log ) {
                            console.log( data );
                        }
                        intercessor_note.removeClass('deleting');
                    } );
                    return true;
                }
            } );
        }
    };

    IPR_Notes.init();

    /**
     * Requester management screen JS
     */
    var IPR_Requester = {

        vars: {
            requester_card_wrap_editable:  $( '.intercessor-requester-card-wrapper .editable' ),
            requester_card_wrap_edit_item: $( '.intercessor-requester-card-wrapper .edit-item' ),
            user_id: $('input[name="requesterinfo[user_id]"]'),
            state_input: $(':input[name="requesterinfo[state]"]'),
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

                IPR_Requester.vars.requester_card_wrap_editable.hide();
                IPR_Requester.vars.requester_card_wrap_edit_item.fadeIn().css( 'display', 'block' );
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
                    }, userInteractionInterval );
                }, 'json' );
            } );
        },
        user_search: function() {
            // Upon selecting a user from the dropdown, we need to update the User ID
            $( document.body ).on('click.iprSelectUser', '.intercessor_user_search_results a', function( e ) {
                e.preventDefault();
                const user_id = $(this).data('userid');
                IPR_Requester.vars.user_id.val(user_id);
            } );
        },
        remove_user: function() {
            $( document.body ).on( 'click', '#disconnect-requester', function( e ) {

                e.preventDefault();

                if ( confirm( intercessor_admin.disconnect_requester ) ) {

                    const requester_id = $('input[name="requesterinfo[id]"]').val();

                    postData = {
                        intercessor_action:   'disconnect-userid',
                        requester_id: requester_id,
                        _wpnonce:     $( '#edit-requester-info #_wpnonce' ).val()
                    };

                    $.post(ajaxurl, postData, function( response ) {

                        window.location.href=window.location.href;

                    }, 'json');
                }

            } );
        },
        cancel_edit: function() {
            $( document.body ).on( 'click', '#intercessor-edit-requester-cancel', function( e ) {
                e.preventDefault();
                IPR_Requester.vars.requester_card_wrap_edit_item.hide();
                IPR_Requester.vars.requester_card_wrap_editable.show();

                $( '.intercessor_user_search_results' ).html('');
            } );
        },
        delete_checked: function() {
            $( '#intercessor-requester-delete-confirm' ).change( function() {
                const records_input = $('#intercessor-requester-delete-records');
                const submit_button = $('#intercessor-delete-requester');

                if ( $(this).prop('checked') ) {
                    records_input.attr('disabled', false);
                    submit_button.attr('disabled', false);
                } else {
                    records_input.attr('disabled', true);
                    records_input.prop('checked', false);
                    submit_button.attr('disabled', true);
                }
            } );
        }

    };
    IPR_Requester.init();

    /**
     * Edit prayer screen JS
     */
    var IPR_Edit_Prayer = {

        init : function() {
            this.change_requester();
            this.new_requester();
            this.resend_notification();
        },

        change_requester : function() {

            $('#intercessor-requester-details').on('click', '.intercessor-request-change-requester, .intercessor-request-change-requester-cancel', function(e) {
                e.preventDefault();

                var requester_change = $( this ).hasClass('intercessor-request-change-requester'),
                    cancel          = $( this ).hasClass('intercessor-request-change-requester-cancel');

                if ( requester_change ) {
                    $('.requester-info').hide();
                    $('.change-requester').show();
                    $('.intercessor-request-change-requester-input').css('width', 'auto');
                } else if ( cancel) {
                    $('.requester-info').show();
                    $('.change-requester').hide();
                }
            } );
        },

        new_requester : function() {

            $('#intercessor-requester-details').on('click', '.intercessor-request-new-requester, .intercessor-request-new-requester-cancel', function(e) {
                e.preventDefault();

                var new_requester = $( this ).hasClass('intercessor-request-new-requester'),
                    cancel       = $( this ).hasClass('intercessor-request-new-requester-cancel');

                if ( new_requester ) {
                    $('.requester-info').hide();
                    $('.new-requester').show();
                } else if ( cancel ) {
                    $('.requester-info').show();
                    $('.new-requester').hide();
                }

                var new_requester = $( '#intercessor-new-requester' );

                if ($('.new-requester').is(':visible')) {
                    new_requester.val(1);
                } else {
                    new_requester.val(0);
                }
            } );
        },

        resend_notification : function() {
            var emails_wrap = $('.intercessor-request-resend-notification-addresses');

            $( document.body ).on( 'click', '#intercessor-select-notification-email', function( e ) {
                e.preventDefault();
                emails_wrap.slideDown();
            } );

            $( document.body ).on( 'change', '.intercessor-request-resend-notification-email', function() {
                var href = $('#intercessor-select-notification-email').prop( 'href' ) + '&email=' + $( this ).val();

                if ( confirm( intercessor_admin.resend_notification ) ) {
                    window.location = href;
                }
            } );

            $( document.body ).on( 'click', '#intercessor-resend-notification', function( e ) {
                return confirm( intercessor_admin.resend_notification );
            } );
        }
    };
    IPR_Edit_Prayer.init();

} );