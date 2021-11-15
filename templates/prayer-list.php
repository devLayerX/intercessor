<?php
/**
 * Prayer Request List
 *
 * @author 		Victor Aigbeghian
 * @package 	Intercessor/Templates
 * @version     0.9.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $intercessor_options;

$anon 	   	  = $prayer->anon;
$message   	  = $prayer->message;
$title     	  = $prayer->title;
$name      	  = $prayer->first_name . ' ' . $prayer->last_name;
$date	   	  = $prayer->date_created;
$prayer_id 	  = $prayer->id;
$counts	   	  = intercessor_get_prayed_counts( $prayer_id );

$prayed_label = $intercessor_options['prayed_for_label'];
$prayed_for	  = sprintf( __( 'Prayed for %s times', 'intercessor' ), $counts );
$received	  = sprintf( __( 'Received %s ago', 'intercessor' ), $date );

if ( 1 === $anon ) {
	$display_name = esc_html__( 'Anonymous', 'intercessor' );
} else {
	$display_name = '<em>' . $name . '</em>';
}

$submitted 	  = sprintf( __( 'Submitted by %s', 'intercessor' ), $display_name );

if ( ! empty( $prayed_label ) ) {
	$prayed = $prayed_label;
} else {
	$prayed = esc_html__( 'I Prayed for this', 'intercessor' );
}
?>
<div class="intercessor-prayers-list">
<div class="prayers-<?php echo $prayer_id; ?>">
	<h4 class="prayer-title">
		<?php echo apply_filters( 'the_prayer_title', $title ); ?>
	</h4>
	
	<div id="intercessor_prayer_count" class="prayer-list-counter">
		<?php if ( '1' === $intercessor_options['enable_prayer_count']  ) : ?>
			<div class="prayed-for">
				<?php echo esc_attr( $prayed_for ); ?>
			</div>
		<?php endif; ?>	
		
		<form action="" id="form<?php echo $prayer_id; ?>" method="post">
			<?php wp_nonce_field( 'intercessor_update_prayed_counts' ); ?>
			<input name="prayed_count" value="1" type="hidden">
			<input name="id" value="<?php echo $prayer_id; ?>" class="id" type="hidden">
			<input type="submit" class="prayed-updater" value="<?php echo $prayed; ?>">
		</form>
	</div>
	
	<?php if ( get_the_content() !== '' ) : ?>			
		<div class="prayer-message">
			<?php echo apply_filters( 'the_prayer_message', $message ); ?>
		</div>
	<?php endif; ?>	
	
	<p class="prayer-requester">
		<span class="requester-name"><?php echo $submitted; ?></span>
	</p>
	<span class="prayer-date"><?php echo esc_attr( $received ); ?></span>
	<div class="clear"></div>
</div>