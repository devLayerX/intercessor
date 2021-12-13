<?php

/**
 *  Admin Database Upgrade
 *
 *  Shows the databse upgrade process.
 *
 *  @date    24/8/18
 *  @since   5.7.4
 *  @param   void
 */

?>
<style>
	
	/* hide steps */
	.step-1,
	.step-2,
	.step-3 {
		display: none;
	}		
	
</style>
<div id="intercessor-upgrade-wrap" class="wrap">
	
	<h1><?php esc_html_e( 'Upgrade Database', 'intercessor' ); ?></h1>
	
<?php if ( intercessor_has_upgrade() ) : ?>

	<p><?php esc_html_e( 'Checking upgrade tasks...', 'intercessor' ); ?></p>
	<p class="step-1"><i class="intercessor-loading"></i> <?php printf( __( 'Upgrading database to version %s', 'intercessor' ), INTERCESSOR_VERSION ); ?></p>
	<p class="step-2"></p>
	<p class="step-3"><?php echo sprintf( __( 'Database upgrade complete. <a href="%s">See what\'s new</a>', 'intercessor' ), admin_url( 'index.php?page=intercessor-about' ) ); ?></p>
	
	<script type="text/javascript">
	(function($) {
		
		var upgrader = new intercessor.Model({
			initialize: function(){
				
				// allow user to read message for 1 second
				this.setTimeout( this.upgrade, 1000 );
			},
			upgrade: function(){
				
				// show step 1
				$('.step-1').show();
				
				// vars
				var response = '';
				var success = false;
				
				// send ajax request to upgrade DB
				$.ajax({
					url: intercessor.get('ajaxurl'),
					dataType: 'json',
					type: 'post',
					data: intercessor.prepareForAjax({
						action: 'intercessor/ajax/upgrade'
					}),
					success: function( json ){
						success = true;
					},
					error: function( jqXHR, textStatus, errorThrown ){
						response = '<?php _e( 'Upgrade failed.', 'intercessor' ); ?>';
						if( error = intercessor.getXhrError(jqXHR) ) {
							response += ' <code>' + error +  '</code>';
						}
					},
					complete: this.proxy(function(){
						
						// remove spinner
						$('.intercessor-loading').hide();
						
						// display response
						if( response ) {
							$('.step-2').show().html( response );
						}
						
						// display success
						if( success ) {
							$('.step-3').show();
						}
					})
				});
			}
		});
				
	})(jQuery);	
	</script>

<?php else : ?>

	<p><?php _e( 'No updates available.', 'intercessor' ); ?></p>
	
<?php endif; ?>
</div>
