
/**
 * Export screen JS
 */
const IPR_Export = {

    init : function() {
        this.submit();
        this.dismiss_message();
    },

    submit : function() {
        const self = this;

        $( document.body ).on( 'submit', '.intercessor-export-form', function(e) {
            e.preventDefault();

            const form = $( this ),
                submitButton = form.find( 'input[type="submit"]' ).first();

            if ( submitButton.hasClass( 'button-disabled' ) || submitButton.is( ':disabled' ) ) {
                return;
            }

            const data = form.serialize();

            submitButton.addClass( 'button-disabled' );
            form.find( '.notice-wrap' ).remove();
            form.append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="intercessor-progress"><div></div></div></div>' );

            // start the process.
            self.process_step( 1, data, self );
        } );
    },

    process_step : function( step, data, self ) {

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                form: data,
                action: 'intercessor_do_ajax_export',
                step: step
            },
            dataType: 'json',
            success: function( response ) {
                if ( 'done' === response.step || response.error || response.success ) {

                    // We need to get the actual in progress form, not all forms on the page
                    const export_form = $( '.intercessor-export-form' ).find( '.intercessor-progress' ).parent().parent();
                    const notice_wrap = export_form.find( '.notice-wrap' );

                    export_form.find( '.button-disabled' ).removeClass( 'button-disabled' );

                    if ( response.error ) {
                        const error_message = response.message;
                        notice_wrap.html( '<div class="updated error"><p>' + error_message + '</p></div>' );
                    } else if ( response.success ) {
                        const success_message = response.message;
                        notice_wrap.html( '<div id="intercessor-batch-success" class="updated notice"><p>' + success_message + '</p></div>' );
                    } else {
                        notice_wrap.remove();
                        window.location = response.url;
                    }

                } else {
                    $('.intercessor-progress div').animate({
                        width: response.percentage + '%'
                    }, 50, function() {
                        // Animation complete.
                    } );
                    self.process_step( parseInt( response.step ), data, self );
                }

            }
        }).fail(function (response) {
            if ( window.console && window.console.log ) {
                console.log( response );
            }
        } );
    },

    dismiss_message : function() {
        $(document.body).on( 'click', '#intercessor-batch-success .notice-dismiss', function() {
            $('#intercessor-batch-success').parent().slideUp('fast');
        } );
    }
};

IPR_Export.init();

/**
 * Import screen JS
 */
var IPR_Import = {

    init : function() {
        this.submit();
    },

    submit : function() {
        var self = this;

        $('.iintercessor-import-form').ajaxForm({
            beforeSubmit: self.before_submit,
            success:      self.success,
            complete:     self.complete,
            dataType:     'json',
            error:        self.error
        });
    },

    before_submit : function( arr, form, options ) {
        form.find('.notice-wrap').remove();
        form.append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="iintercessor-progress"><div></div></div></div>' );

        //check whether client browser fully supports all File API.
        if ( window.File && window.FileReader && window.FileList && window.Blob ) {

            // HTML5 File API is supported by browser.

        } else {

            var import_form = $('.iintercessor-import-form').find('.iintercessor-progress').parent().parent();
            var notice_wrap = import_form.find('.notice-wrap');

            import_form.find('.button-disabled').removeClass('button-disabled');

            //Error for older unsupported browsers that doesn't support HTML5 File API.
            notice_wrap.html('<div class="update error"><p>' + iintercessor_vars.unsupported_browser + '</p></div>');
            return false;
        }
    },

    success: function( responseText, statusText, xhr, form ) {},

    complete: function( xhr ) {
        var self     = $( this ),
            response = jQuery.parseJSON( xhr.responseText );

        if ( response.success ) {
            var form = $('.iintercessor-import-form .notice-wrap').parent();

            form.find('.iintercessor-import-file-wrap,.notice-wrap').remove();
            form.find('.iintercessor-import-options').slideDown();

            // Show column mapping.
            var select  = form.find('select.iintercessor-import-csv-column'),
                row     = select.parents( 'tr' ).first(),
                options = '',
                columns = response.data.columns.sort(function(a,b) {
                    if ( a < b ) return -1;
                    if ( a > b ) return 1;
                    return 0;
                });

            $.each( columns, function( key, value ) {
                options += '<option value="' + value + '">' + value + '</option>';
            });

            select.append( options );

            select.on('change', function() {
                var key = $( this ).val();

                if ( ! key ) {
                    $( this ).parent().next().html( '' );
                } else {

                    if ( false !== response.data.first_row[key] ) {
                        $( this ).parent().next().html( response.data.first_row[key] );
                    } else {
                        $( this ).parent().next().html( '' );
                    }
                }
            });

            $.each( select, function() {
                $( this ).val( $( this ).attr( 'data-field' ) ).change();
            });

            $(document.body).on('click', '.iintercessor-import-proceed', function(e) {
                e.preventDefault();

                form.append( '<div class="notice-wrap"><span class="spinner is-active"></span><div class="iintercessor-progress"><div></div></div></div>' );

                response.data.mapping = form.serialize();

                IPR_Import.process_step( 1, response.data, self );
            });

        } else {
            IPR_Import.error( xhr );
        }
    },

    error : function( xhr ) {

        // Something went wrong. This will display error on form

        var response    = jQuery.parseJSON( xhr.responseText );
        var import_form = $('.iintercessor-import-form').find('.iintercessor-progress').parent().parent();
        var notice_wrap = import_form.find('.notice-wrap');

        import_form.find('.button-disabled').removeClass('button-disabled');

        if ( response.data.error ) {
            notice_wrap.html('<div class="update error"><p>' + response.data.error + '</p></div>');
        } else {
            notice_wrap.remove();
        }
    },

    process_step : function( step, import_data, self ) {
        $.ajax({
            type: 'POST',
            url:  ajaxurl,
            data: {
                form:    import_data.form,
                nonce:   import_data.nonce,
                class:   import_data.class,
                upload:  import_data.upload,
                mapping: import_data.mapping,
                action:  'iintercessor_do_ajax_import',
                step:    step
            },
            dataType: "json",
            success: function( response ) {

                if ( 'done' === response.data.step || response.data.error ) {

                    // We need to get the actual in progress form, not all forms on the page
                    var import_form  = $('.iintercessor-import-form').find('.iintercessor-progress').parent().parent();
                    var notice_wrap  = import_form.find('.notice-wrap');

                    import_form.find('.button-disabled').removeClass('button-disabled');

                    if ( response.data.error ) {
                        notice_wrap.html('<div class="update error"><p>' + response.data.error + '</p></div>');

                    } else {
                        import_form.find( '.iintercessor-import-options' ).hide();
                        $('html, body').animate({
                            scrollTop: import_form.parent().offset().top
                        }, 500 );

                        notice_wrap.html('<div class="updated"><p>' + response.data.message + '</p></div>');
                    }

                } else {
                    $('.iintercessor-progress div').animate({
                        width: response.data.percentage + '%'
                    }, 50, function() {
                        // Animation complete.
                    });

                    IPR_Import.process_step( parseInt( response.data.step ), import_data, self );
                }
            }
        }).fail(function (response) {
            if ( window.console && window.console.log ) {
                console.log( response );
            }
        });
    }
};

IPR_Import.init();