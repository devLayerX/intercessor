const IPR_Edit_Prayer = {
    init: function () {
        this.change_requester();
        this.new_requester();
    },


    change_requester: function() {
        $( '#intercessor-requester-details' ).on( 'click', '.intercessor-prayer-change-requester, .intercessor-prayer-change-requester-cancel', function( e ) {
            e.preventDefault();

            const change_requester = $( this ).hasClass( 'intercessor-prayer-change-requester' ),
                cancel = $( this ).hasClass( 'intercessor-prayer-change-requester-cancel' );

            if ( change_requester ) {
                $( '.requester-info' ).hide();
                $( '.change-requester' ).show();
                setTimeout( function() {
                    $( '.intercessor-prayer-change-requester-input' ).css( 'width', '300' );
                }, 1 );
            } else if ( cancel ) {
                $( '.requester-info' ).show();
                $( '.change-requester' ).hide();
            }
        } );
    },

    new_requester: function() {
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

            if ( $( '.new-requester' ).is( ':visible' ) ) {
                new_requester.val( 1 );
            } else {
                new_requester.val( 0 );
            }
        } );
    }

};

jQuery( document ).ready( function ( $ ) {
    IPR_Edit_Prayer.init();
} );
