<?php
/**
 * Intercessor Prayer Submit Form reCaptcha Fields.
 *
 * This template is used to display the Google reCaptcha fields.
 *
 * @package   	Intercessor
 * @copyright   Copyright (c) 2019, Victor Aigbeghian
 * @license     http://opensource.org/licenses/gpl-3.0 GNU Public License
 * @since       0.9.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<div id="intercessor-recaptcha" class="intercessor-row">
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<div class="col form-column">
		<label for="intercessor-captcha">
			<?php echo stripslashes( $label ); ?>
		</label>
		<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>

			<p>
				<input type="hidden" name="g-recaptcha-remoteip" value=<?php echo esc_attr( intercessor_get_ip() ); ?> />
			</p>
	</div>
</div>
