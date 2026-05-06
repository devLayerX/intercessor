<?php
/**
 * Front-end template: Prayer List block.
 *
 * Variables provided by Prayer_List_Block::render():
 *
 * @var \Intercessor\Database\Row\Prayer_Request[] $items          Prayer request rows.
 * @var \Intercessor\Database\Query\Requester_Query $requesterQuery Query instance for name lookups.
 * @var bool   $showDate     Whether to display the submission date.
 * @var bool   $showAuthor   Whether to display the requester name.
 * @var int    $paged        Current page number.
 * @var int    $maxPages     Total number of pages.
 * @var int    $limit        Items per page.
 *
 * @package Intercessor
 * @since   1.0.0
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

use Intercessor\Admin\Settings;
use Intercessor\Database\Query\Prayed_Count_Query;

$intercessor_date_format  = Settings::get( 'date_format' ) ?: get_option( 'date_format' );
$intercessor_count_query  = new Prayed_Count_Query();

// Output the prayer interaction config as an inline <script> so it is
// always available regardless of whether any JS handle is registered.
// Guard with a flag so multiple Prayer_List blocks on one page only
// emit the config once.
if ( ! defined( 'INTERCESSOR_PRAY_CONFIG_PRINTED' ) ) {
	define( 'INTERCESSOR_PRAY_CONFIG_PRINTED', true );
	$intercessor_pray_config = wp_json_encode( array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'intercessor_record_prayer' ),
		'action'  => 'intercessor_record_prayer',
		'i18n'    => array(
			'praying' => __( 'Praying…',           'intercessor' ),
			'prayed'  => __( 'I prayed for this!', 'intercessor' ),
			'pray'    => __( 'I prayed for this',  'intercessor' ),
			'error'   => __( 'Could not record your prayer. Please try again.', 'intercessor' ),
		),
	) );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<script>window.intercessorPray = ' . $intercessor_pray_config . ';</script>';
}
?>
<div class="intercessor-prayer-list wp-block-intercessor-prayer-list">

	<?php if ( empty( $items ) ) : ?>
		<p class="intercessor-empty">
			<?php esc_html_e( 'No prayer requests found.', 'intercessor' ); ?>
		</p>
	<?php else : ?>

		<ul class="intercessor-request-list">
			<?php foreach ( $items as $intercessor_item ) :
				$intercessor_display_name = '';
				if ( $showAuthor && ! $intercessor_item->is_anonymous() && $intercessor_item->requester_id > 0 ) {
					$intercessor_requester   = $requesterQuery->get_item( $intercessor_item->requester_id );
					$intercessor_display_name = $intercessor_requester ? esc_html( $intercessor_requester->get_display_name() ) : '';
				}
				$intercessor_prayed_total = $intercessor_count_query->get_total_for_request( $intercessor_item->id );
			?>
				<li class="intercessor-request-item intercessor-status-<?php echo esc_attr( $intercessor_item->status ); ?>">

					<h3 class="intercessor-request-subject">
						<?php echo esc_html( $intercessor_item->subject ); ?>
					</h3>

					<div class="intercessor-request-meta">
						<?php if ( $showAuthor ) : ?>
							<span class="intercessor-request-author intercessor-meta-author">
								<?php if ( $intercessor_display_name ) : ?>
									// translators: %d: total number of prayers prayed for this request
									<?php printf(
										/* translators: %s: requester name */
										esc_html__( 'By %s', 'intercessor' ),
										esc_html( $intercessor_display_name )
									); ?>
								<?php else : ?>
									<?php esc_html_e( 'Anonymous', 'intercessor' ); ?>
								<?php endif; ?>
							</span>
						<?php endif; ?>

						<?php if ( $showDate && $intercessor_item->date_created ) : ?>
							<time class="intercessor-request-date"
								  datetime="<?php echo esc_attr( $intercessor_item->date_created ); ?>">
								<?php echo esc_html( mysql2date( $intercessor_date_format, $intercessor_item->date_created ) ); ?>
							</time>
						<?php endif; ?>
					</div>

					<div class="intercessor-request-content">
						<?php echo wp_kses_post( wpautop( $intercessor_item->content ) ); ?>
					</div>

					<?php // ── "I prayed for this" button ───────────────── ?>
					<div class="intercessor-pray-action">
						<button
							class="intercessor-pray-btn"
							data-request-id="<?php echo absint( $intercessor_item->id ); ?>"
							aria-label="<?php esc_attr_e( 'I prayed for this request', 'intercessor' ); ?>"
						>
							<span class="intercessor-pray-icon ipr-icon ipr-icon-praying" aria-hidden="true"></span>
							<span class="intercessor-pray-label">
								<?php esc_html_e( 'I prayed for this', 'intercessor' ); ?>
							</span>
							<span class="intercessor-pray-count" aria-label="<?php esc_attr_e( 'prayers', 'intercessor' ); ?>">
								<?php echo absint( $intercessor_prayed_total ); ?>
							</span>
						</button>
					</div>

				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( $maxPages > 1 ) : ?>
			<nav class="intercessor-pagination"
				 aria-label="<?php esc_attr_e( 'Prayer requests navigation', 'intercessor' ); ?>">

				<?php if ( $paged > 1 ) : ?>
					<a class="intercessor-page-link"
					   href="<?php echo esc_url( add_query_arg( 'ipage', $paged - 1 ) ); ?>">
						&laquo; <?php esc_html_e( 'Previous', 'intercessor' ); ?>
					</a>
				<?php endif; ?>

				<span class="intercessor-page-info">
					// translators: %s: requester display name
					<?php printf(
						/* translators: 1: current page 2: total pages */
						esc_html__( 'Page %1$d of %2$d', 'intercessor' ),
						(int) $paged,
						(int) $maxPages
					); ?>
				</span>

				<?php if ( $paged < $maxPages ) : ?>
					<a class="intercessor-page-link"
					   href="<?php echo esc_url( add_query_arg( 'ipage', $paged + 1 ) ); ?>">
						<?php esc_html_e( 'Next', 'intercessor' ); ?> &raquo;
					</a>
				<?php endif; ?>

			</nav>
		<?php endif; ?>

	<?php endif; ?>

</div>

<script>
( function () {
	'use strict';

	const config = window.intercessorPray || {};
	const i18n   = config.i18n || {};

	document.querySelectorAll( '.intercessor-pray-btn' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', async function () {
			if ( btn.disabled ) return;

			btn.disabled = true;
			btn.classList.add( 'intercessor-pray-btn--loading' );
			const label = btn.querySelector( '.intercessor-pray-label' );
			const count = btn.querySelector( '.intercessor-pray-count' );
			const orig  = label ? label.textContent : '';

			if ( label ) label.textContent = i18n.praying || 'Praying…';

			try {
				const body = new FormData();
				body.append( 'action',     config.action || 'intercessor_record_prayer' );
				body.append( 'nonce',      config.nonce  || '' );
				body.append( 'request_id', btn.dataset.requestId || '' );

				const res  = await fetch( config.ajaxUrl || '/wp-admin/admin-ajax.php', {
					method: 'POST',
					body,
				} );
				const json = await res.json();

				if ( json.success ) {
					if ( label ) label.textContent = i18n.prayed || 'I prayed for this!';
					if ( count ) count.textContent = json.data.total;
					btn.classList.add( 'intercessor-pray-btn--prayed' );
					// Keep disabled — one prayer per page load per button.
				} else {
					if ( label ) label.textContent = orig;
					btn.disabled = false;
					alert( json.data.message || i18n.error || 'Could not record your prayer.' );
				}
			} catch ( err ) {
				if ( label ) label.textContent = orig;
				btn.disabled = false;
			} finally {
				// Always remove the loading state so the spinner stops,
				// regardless of whether the request succeeded, returned an
				// error response, or threw a network exception.
				btn.classList.remove( 'intercessor-pray-btn--loading' );
			}
		} );
	} );
} () );
</script>
