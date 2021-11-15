var $intercessor_vars;

jQuery( document ).ready( function( $ ) {
	$( document.body ).on('click', '.intercessor_prayers_links', function() {
		//e.preventDefault();
		$(this).parent().prev('.ipr-prayers-history-edit').slideToggle();
		$(this).parent().find('.intercessor_prayers_links').toggle();

		return false;
	});

	$( 'input[name="ipr_history_delete"]' ).on( 'click', function() {
		if ( confirm( intercessor_vars.delete_prayer ) ) {
			return true;
		}
		
		return false;
	} );
});
