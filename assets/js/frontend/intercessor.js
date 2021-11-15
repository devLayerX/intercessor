"use strict";

var intercessor_params;
jQuery(document).ready(function ($) {
  /*
  $( '.prayed-updater' ).on( 'click', function(e) {
  	e.preventDefault();
  		var button = $(this),
  		wrapper    = button.parent().parent(),
  		prayerform = wrapper.find( '#intercessor_update_counts' ),
  		data       = {
  			action: 'intercessor_process_praying',
  			id: wrapper.find( 'input[name="prayer_id"]' ).val(),
  			nonce: wrapper.find( 'input[name="intercessor_update_prayed_nonce"]' ).val(),
  		},
  		prayerid   = wrapper.find( 'input[name="prayer_id"]' ).val(),
  		oldcounts = wrapper.find( 'input[name="intercessor_prayed_count"]' ).val(),
  		presentcount = wrapper.find( '#prayed_counts_' + prayerid ).val(),
  		nonce        = wrapper.find( 'input[name="intercessor_update_prayed_nonce"]' ).val(),
  		ajaxurl      = intercessor_params.ajax_url,
  		praying      = wrapper.find( '#intercessor_praying' ),
  		delay        = 2000;
  
  		function updatecounts() {
  		button.addClass( 'disabled' );
  		praying.slideDown(200).html( intercessor_params.praying ).fadeOut(10000);
  
  		var newcounts = Math.floor(oldcounts) + 1,
  			delay       = 2000;
  			button.html( intercessor_params.prayed );
  		$( '#prayed_counts_'+prayerid ).html( newcounts );
  		}
  		$.post( ajaxurl, data, updatecounts );
  	return false;
  } );
  */

  /*
  $( document.body ).on( 'click', '.prayed-updater', function( e ) {
  	e.preventDefault();
  		const button = $( this ),
  		wrapper    = button.parent().parent(),
  		prayerform = wrapper.find( '#intercessor_update_counts' ),
  		prayerid   = wrapper.find( 'input[name="prayer_id"]' ).val(),
  		presentcount = wrapper.find( '#prayed_counts_' + prayerid ).val(),
  		prayedcounts = wrapper.find( '#intercessor_prayed_count' ).val(),
  		nonce        = wrapper.find( 'input[name="intercessor_update_prayed_nonce"]' ).val(),
  		ajaxurl      = intercessor_params.ajax_url,
  		praying      = wrapper.find( '#intercessor_praying' ),
  		delay        = 2000,
  		postData     = {
  			action: 'intercessor_process_praying',
  			prayer_id: prayerid,
  			_wpnonce: nonce,
  		};
  		$.post( ajaxurl, postData, function( response ) {
  		setTimeout( function() {
  			if ( true === response.success ) {
  				const newcounts = parseInt( presentcount ) + 1,
  				praying         = intercessor_params.praying;
  					$( '#prayed_counts_' + prayerid ).html( newcounts );
  				button.html( intercessor_params.prayed );
  				button.attr( 'disabled', true );
  			} else {
  			//	praying.html( intercessor_params.nopraying );
  				button.attr( 'disabled', false );
  			}
  		}, delay );
  	} );
  } );
  
  $( '.prayed-updater' ).click(function() {
  	const prayerform = $(this).parents( 'form' ).serialize(),
  		ajaxurl      = intercessor_params.ajax_url,
  		button       = $( this ),
  		x            = $(this).siblings( 'input.id' ).attr( 'value' ),
  		oldcounts    = $( 'span#prayed_counts_'+x).html() ,
  		prayedcount  = $(this).siblings( '#intercessor_prayed_count' ).attr( 'value' ),
  		y            = Math.floor(Math.random()*1001);
  		function processcounting() {
  		const newcounts = parseInt(oldcounts) + 1,
  			delay       = 2000,
  			praying     = intercessor_params.praying;
  				setTimeout( function() {
  				button.attr( 'disabled', true );
  				$( '#prayed_counts_'+x ).html( newcounts );
  				button.html( intercessor_params.prayed );
  			}, delay );
  		//	$( '#prayed_counts_'+x).load(ajaxurl+'?id='+x+'&time='+y);
  	};
  	$.post( ajaxurl, prayerform, processcounting );
  	return false;
  } );
  */
});
/*
function() {
	var prayercountform = jQuery(this).parents("form").serialize();
	var formurl = jQuery(this).parents("form").attr("action");
	var xxpe = jQuery(".xxpe").attr("value");
	var x = jQuery(this).siblings("input.id").attr("value");
	var y = Math.floor(Math.random() * 1001);

	function loadrefreshednumber() {
		jQuery('#count' + x).load(formurl + '?id=' + x + '&time=' + y + "&xxpe=" + xxpe);
	};
	jQuery.post(formurl + "?xxpe=" + xxpe, prayercountform, loadrefreshednumber);
	return false;
}
*/
"use strict";

var intercessor_params;
jQuery(document).ready(function (p) {
  p(".prayed-updater").on("click", function (e) {
    e.preventDefault();
    var n = p(this),
        r = n.parent().parent(),
        a = (r.find("#intercessor_update_counts"), {
      action: "intercessor_process_praying",
      praying_id: r.find('input[name="prayer_id"]').val(),
      praying_nonce: r.find('input[name="intercessor_update_prayed_nonce"]').val()
    }),
        t = r.find('input[name="prayer_id"]').val(),
        i = r.find('input[name="intercessor_prayed_count"]').val(),
        s = (r.find("#prayed_counts_" + t).val(), r.find('input[name="intercessor_update_prayed_nonce"]').val(), intercessor_params.ajax_url),
        d = r.find("#intercessor_praying");
    return p.post(s, a, function () {
      n.addClass("disabled"), d.slideDown(200).html(intercessor_params.praying).fadeOut(1e4);
      var e = Math.floor(i) + 1;
      n.html(intercessor_params.prayed), p("#prayed_counts_" + t).html(e);
    }), !1;
  });
});
"use strict";

function onSubmit(e) {
  document.getElementById("submit-prayer-form").submit();
}

jQuery(function (r) {
  if ("undefined" == typeof ipr_frontend_vars) return !1;
  r(document.body).on("click", ".intercessor_terms_links", function () {
    return r(this).parent().prev(".intercessor-terms").slideToggle(), r(this).parent().find(".intercessor_terms_links").toggle(), !1;
  }), {
    init: function init() {
      this.toggle_register_fields();
    },
    toggle_register_fields: function toggle_register_fields() {
      r(document.body).on("change", "input#intercessor_create_account", function () {
        var e = r(this).is(":checked");
        r("#intercessor_username", "#intercessor_password", "#intercessor_password2");
        register_fields = r("#intercessor_register_fields"), e && (register_fields.removeClass(".intercessor-hidden"), register_fields.show());
      });
    }
  }.init(), r("#intercessor_prayed_updater").click(function () {
    r.post(intercessor.ajax_url, {
      action: "update_praying",
      prayer_id: this.attr()
    });
  });
});
"use strict";

var $intercessor_vars;
jQuery(document).ready(function ($) {
  $(document.body).on('click', '.intercessor_prayers_links', function () {
    //e.preventDefault();
    $(this).parent().prev('.ipr-prayers-history-edit').slideToggle();
    $(this).parent().find('.intercessor_prayers_links').toggle();
    return false;
  });
  $('input[name="ipr_history_delete"]').on('click', function () {
    if (confirm(intercessor_vars.delete_prayer)) {
      return true;
    }

    return false;
  });
});
"use strict";

var $intercessor_vars;
jQuery(document).ready(function (r) {
  r(document.body).on("click", ".intercessor_prayers_links", function () {
    return r(this).parent().prev(".ipr-prayers-history-edit").slideToggle(), r(this).parent().find(".intercessor_prayers_links").toggle(), !1;
  }), r('input[name="ipr_history_delete"]').on("click", function () {
    return !!confirm(intercessor_vars.delete_prayer);
  });
});