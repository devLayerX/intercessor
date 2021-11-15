<?php
/**
 * Intercessor Prayer Submit Form Request Fields.
 *
 * This template is used to display the prayer content fields.
 *
 * @package   	Intercessor
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<form name="intercessor_request" method="post" class="intercessor_request" action="<?php echo esc_url( intercessor_get_prayer_request_uri() ); ?>" enctype="multipart/form-data">
	<?php if ( $prayer->get_request_fields() ) : ?>
	<div id="intercessor_requester_details">
		<?php 
		$requester_fields = $prayer->get_request_fields( 'requester' );
		
		foreach ( $requester_fields as $key => $field ) {
			intercessor_requeste_form_html( $key, $field, $prayer->obtain_value( $key ) );
		}
		?>
	</div>	

</form>