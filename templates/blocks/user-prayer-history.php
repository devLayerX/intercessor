<?php
/**
 * Front-end template: User Prayer History block.
 *
 * Displays all prayer requests belonging to the currently logged-in user,
 * with inline edit and delete actions. Rendered by Prayer_History_Block::render()
 * when the user is authenticated and a requester record exists for them.
 *
 * Variables provided by Prayer_History_Block::render():
 *
 * @var \Intercessor\Database\Row\Prayer_Request[] $items          User's prayer requests (all statuses).
 * @var \Intercessor\Database\Query\Prayed_Count_Query $countQuery Prayed-count query instance.
 *
 * @package Intercessor
 * @since   1.1.0
 */
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

$dateFormat = \Intercessor\Admin\Settings::get( 'date_format' ) ?: get_option( 'date_format' );

/**
 * Map a status slug to a human-readable label.
 *
 * @param string $status Raw status slug from the database.
 * @return string Translated label.
 */
$status_label = static function ( string $status ): string {
	$labels = array(
		'pending'  => esc_html__( 'Pending review', 'intercessor' ),
		'approved' => esc_html__( 'Approved', 'intercessor' ),
		'rejected' => esc_html__( 'Rejected', 'intercessor' ),
		'archived' => esc_html__( 'Archived', 'intercessor' ),
		'private'  => esc_html__( 'Private', 'intercessor' ),
	);
	return $labels[ $status ] ?? ucfirst( $status );
};
?>
<div class="intercessor-user-history wp-block-intercessor-prayer-history">

	<?php if ( empty( $items ) ) : ?>
		<p class="intercessor-empty">
			<?php esc_html_e( "You haven't submitted any prayer requests yet.", 'intercessor' ); ?>
		</p>

	<?php else : ?>

		<h2 class="intercessor-user-history__title">
			<?php esc_html_e( 'Your Prayer Requests', 'intercessor' ); ?>
		</h2>

		<div class="intercessor-user-history__notice" aria-live="polite"></div>

		<table class="intercessor-user-history__table">
			<thead>
				<tr>
					<th class="ipr-col-id"><?php esc_html_e( '#', 'intercessor' ); ?></th>
					<th class="ipr-col-date"><?php esc_html_e( 'Date', 'intercessor' ); ?></th>
					<th class="ipr-col-details"><?php esc_html_e( 'Prayer Details', 'intercessor' ); ?></th>
					<th class="ipr-col-prayed"><?php esc_html_e( 'Prayed', 'intercessor' ); ?></th>
					<th class="ipr-col-status"><?php esc_html_e( 'Status', 'intercessor' ); ?></th>
					<th class="ipr-col-actions"><?php esc_html_e( 'Actions', 'intercessor' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ( $items as $item ) :
					$prayed_total = $countQuery->get_total_for_request( $item->id );
					$item_date    = $item->date_created
						? mysql2date( $dateFormat, $item->date_created )
						: '';
				?>
					<tr class="ipr-user-row intercessor-status-<?php echo esc_attr( $item->status ); ?>">

						<td class="ipr-col-id" data-label="<?php esc_attr_e( '#', 'intercessor' ); ?>">
							#<?php echo absint( $item->id ); ?>
						</td>

						<td class="ipr-col-date" data-label="<?php esc_attr_e( 'Date', 'intercessor' ); ?>">
							<?php if ( $item_date ) : ?>
								<time datetime="<?php echo esc_attr( $item->date_created ); ?>">
									<?php echo esc_html( $item_date ); ?>
								</time>
							<?php endif; ?>
						</td>

						<td class="ipr-col-details" data-label="<?php esc_attr_e( 'Prayer Details', 'intercessor' ); ?>">
							<p class="ipr-row-subject">
								<strong><?php echo esc_html( $item->subject ); ?></strong>
							</p>
							<p class="ipr-row-content">
								<?php echo esc_html( wp_trim_words( $item->content, 20, '…' ) ); ?>
							</p>

							<?php // ── Inline edit form (hidden until Edit is clicked) ── ?>
							<div class="ipr-edit-form" hidden>
								<form class="ipr-update-form">
									<input type="hidden" name="request_id" value="<?php echo absint( $item->id ); ?>" />

									<p>
										<label for="ipr-subject-<?php echo absint( $item->id ); ?>">
											<?php esc_html_e( 'Subject', 'intercessor' ); ?>
										</label>
										<input
											type="text"
											id="ipr-subject-<?php echo absint( $item->id ); ?>"
											name="subject"
											class="intercessor-input"
											value="<?php echo esc_attr( $item->subject ); ?>"
											required
										/>
									</p>

									<p>
										<label for="ipr-content-<?php echo absint( $item->id ); ?>">
											<?php esc_html_e( 'Prayer Request', 'intercessor' ); ?>
										</label>
										<textarea
											id="ipr-content-<?php echo absint( $item->id ); ?>"
											name="content"
											class="intercessor-input"
											rows="4"
											required
										><?php echo esc_textarea( $item->content ); ?></textarea>
									</p>

									<p class="ipr-form-msg" role="status" aria-live="polite"></p>

									<p class="ipr-form-notice">
										<?php esc_html_e( 'Your request will be sent back for review after saving.', 'intercessor' ); ?>
									</p>

									<button type="submit" class="wp-element-button intercessor-submit">
										<?php esc_html_e( 'Save Changes', 'intercessor' ); ?>
									</button>
								</form>
							</div>
						</td>

						<td class="ipr-col-prayed" data-label="<?php esc_attr_e( 'Prayed', 'intercessor' ); ?>">
							<?php echo absint( $prayed_total ); ?>
						</td>

						<td class="ipr-col-status" data-label="<?php esc_attr_e( 'Status', 'intercessor' ); ?>">
							<span class="ipr-status-badge intercessor-status-<?php echo esc_attr( $item->status ); ?>">
								<?php echo esc_html( $status_label( $item->status ) ); ?>
							</span>
						</td>

						<td class="ipr-col-actions" data-label="<?php esc_attr_e( 'Actions', 'intercessor' ); ?>">
							<button
								type="button"
								class="wp-element-button intercessor-btn--secondary ipr-btn-edit"
								data-ipr-action="edit"
							>
								<?php esc_html_e( 'Edit', 'intercessor' ); ?>
							</button>

							<button
								type="button"
								class="wp-element-button intercessor-btn--danger ipr-btn-delete"
								data-ipr-action="delete"
								data-request-id="<?php echo absint( $item->id ); ?>"
							>
								<?php esc_html_e( 'Delete', 'intercessor' ); ?>
							</button>
						</td>

					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	<?php endif; ?>

</div>
