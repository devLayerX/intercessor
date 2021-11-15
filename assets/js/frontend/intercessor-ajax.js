var intercessor_params;
jQuery(document).ready(function($) {
	
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
var intercessor_params;
jQuery(document).ready(function(p) {
    p(".prayed-updater").on("click", function(e) {
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
        return p.post(s, a, function() {
            n.addClass("disabled"), d.slideDown(200).html(intercessor_params.praying).fadeOut(1e4);
            var e = Math.floor(i) + 1;
            n.html(intercessor_params.prayed), p("#prayed_counts_" + t).html(e)
        }), !1
    })
});