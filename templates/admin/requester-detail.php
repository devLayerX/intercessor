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
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables included via require, not true globals

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'view_prayer_reports' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'intercessor' ) );
}

$requester  = $view->get_requester();
$active_tab = $view->get_active_tab();
$tabs       = $view->get_tabs();
$back_url   = admin_url( 'admin.php?page=intercessor-requesters' );
?>
<div class="wrap intercessor-detail">

	<h1 class="wp-heading-inline">
		<?php echo esc_html( $requester->get_display_name() ); ?>
		<span class="intercessor-status <?php echo esc_attr( $requester->status ); ?>">
			<?php echo esc_html( ucfirst( $requester->status ) ); ?>
		</span>
	</h1>

	<a href="<?php echo esc_url( $back_url ); ?>" class="page-title-action">
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
		<?php foreach ( $tabs as $slug => $tab ) :
			$is_active  = $slug === $active_tab;
			$tab_url    = $view->tab_url( $slug );
			$tab_class  = 'nav-tab' . ( $is_active ? ' nav-tab-active' : '' );
			$is_delete  = $slug === 'delete';
		?>
			<a href="<?php echo esc_url( $tab_url ); ?>"
			   class="<?php echo esc_attr( $tab_class ); ?>"
			   <?php if ( $is_active ) : ?>aria-current="page"<?php endif; ?>
			   style="<?php echo $is_delete ? 'color:#d63638;' : ''; ?>">
				<span class="dashicons <?php echo esc_attr( $tab['dashicon'] ); ?>"
				      style="vertical-align:text-bottom;margin-right:4px;font-size:1.1em;"
				      aria-hidden="true"></span>
				<?php echo esc_html( $tab['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php // ── Tab content ──────────────────────────────────────────────── ?>
	<div class="intercessor-tab-content" style="margin-top: 1.25rem;">
		<?php $view->render_tab_content(); ?>
	</div>

</div>
