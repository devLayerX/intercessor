<?php
/**
 * Admin template: requesters page.
 *
 * Branches to the single-requester tabbed detail view when ?requester_id={id}
 * is present; otherwise renders the list table.
 *
 * @package Intercessor
 * @since   1.0.1
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

use Intercessor\Admin\Requester_View;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$intercessor_requester_id = isset( $_GET['requester_id'] ) ? absint( $_GET['requester_id'] ) : 0;

if ( $intercessor_requester_id > 0 ) {
	$intercessor_view = Requester_View::from_request();

	if ( $intercessor_view ) {
		require __DIR__ . '/requester-detail.php'; // $intercessor_view is in scope for requester-detail.php
		return;
	}

	// Requester not found — show a friendly error.
	echo '<div class="wrap"><div class="notice notice-error inline"><p>';
	echo esc_html__( 'Requester not found.', 'intercessor' );
	echo '</p></div>';
	echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=intercessor-requesters' ) ) . '">';
	echo '&larr; ' . esc_html__( 'Back to Requesters', 'intercessor' );
	echo '</a></p></div>';
	return;
}

/** @var \Intercessor\Admin\Requester_List_Table $table */
?>
<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Requesters', 'intercessor' ); ?>
	</h1>
	<hr class="wp-header-end">

	<?php if ( isset( $_GET['requester_deleted'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Requester permanently deleted.', 'intercessor' ); ?></p>
		</div>
	<?php endif; ?>
	<?php if ( isset( $_GET['requester_error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'An error occurred. Please try again.', 'intercessor' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="get">
		<input type="hidden" name="page" value="intercessor-requesters">
		<?php $table->display(); ?>
	</form>
</div>
