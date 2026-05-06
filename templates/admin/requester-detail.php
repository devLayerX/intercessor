<?php
/**
 * Admin template: single-requester tabbed detail page.
 *
 * Included by templates/admin/requesters.php when ?requester_id={id} is set
 * and a Requester_View has been resolved.
 *
 * Variables in scope (set by requesters.php before including this file):
 *   @var \Intercessor\Admin\Requester_View $view  Resolved view controller.
 *
 * @package Intercessor
 * @since   1.0.1
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'view_prayer_reports' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'intercessor' ) );
}

$intercessor_requester  = $intercessor_view->get_requester();
$intercessor_active_tab = $intercessor_view->get_active_tab();
$intercessor_tabs       = $intercessor_view->get_tabs();
$intercessor_back_url   = admin_url( 'admin.php?page=intercessor-requesters' );
?>
<div class="wrap intercessor-detail">

	<h1 class="wp-heading-inline">
		<?php echo esc_html( $intercessor_requester->get_display_name() ); ?>
		<span class="intercessor-status <?php echo esc_attr( $intercessor_requester->status ); ?>">
			<?php echo esc_html( ucfirst( $intercessor_requester->status ) ); ?>
		</span>
	</h1>

	<a href="<?php echo esc_url( $intercessor_back_url ); ?>" class="page-title-action">
		<?php esc_html_e( '&larr; All Requesters', 'intercessor' ); ?>
	</a>

	<hr class="wp-header-end">

	<?php // ── Flash notices ───────────────────────────────────────────── ?>
	<?php if ( isset( $_GET['requester_updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Requester updated.', 'intercessor' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['requester_error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'An error occurred. Please try again.', 'intercessor' ); ?></p>
		</div>
	<?php endif; ?>

	<?php // ── WordPress-style nav tabs ────────────────────────────────── ?>
	<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Requester details tabs', 'intercessor' ); ?>">
		<?php foreach ( $intercessor_tabs as $intercessor_slug => $intercessor_tab ) :
			$intercessor_is_active  = $intercessor_slug === $intercessor_active_tab;
			$intercessor_tab_url    = $intercessor_view->tab_url( $intercessor_slug );
			$intercessor_tab_class  = 'nav-tab' . ( $intercessor_is_active ? ' nav-tab-active' : '' );
			$intercessor_is_delete  = $intercessor_slug === 'delete';
		?>
			<a href="<?php echo esc_url( $intercessor_tab_url ); ?>"
			   class="<?php echo esc_attr( $intercessor_tab_class ); ?>"
			   <?php if ( $intercessor_is_active ) : ?>aria-current="page"<?php endif; ?>
			   style="<?php echo $intercessor_is_delete ? 'color:#d63638;' : ''; ?>">
				<span class="dashicons <?php echo esc_attr( $intercessor_tab['dashicon'] ); ?>"
				      style="vertical-align:text-bottom;margin-right:4px;font-size:1.1em;"
				      aria-hidden="true"></span>
				<?php echo esc_html( $intercessor_tab['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php // ── Tab content ──────────────────────────────────────────────── ?>
	<div class="intercessor-tab-content" style="margin-top: 1.25rem;">
		<?php $intercessor_view->render_tab_content(); ?>
	</div>

</div>
