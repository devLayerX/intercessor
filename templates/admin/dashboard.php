<?php
/**
 * Admin template: Intercessor dashboard.
 *
 * @package Intercessor
 * @since   1.0.0
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

use Intercessor\Database\Query\Prayer_Request_Query;
use Intercessor\Database\Query\Requester_Query;

$intercessor_pr_query  = new Prayer_Request_Query();
$intercessor_rq_query  = new Requester_Query();
$intercessor_pending  = count( $intercessor_pr_query->get_pending() );
$intercessor_approved = $intercessor_pr_query->count_items( [ 'status' => 'approved' ] );
$intercessor_total_pr  = $intercessor_pr_query->count_items( [] );
$intercessor_total_rq  = $intercessor_rq_query->count_items( [] );
?>
<div class="wrap intercessor-dashboard">
    <h1><?php esc_html_e( 'Intercessor Dashboard', 'intercessor' ); ?></h1>

    <div class="intercessor-stats">

        <div class="intercessor-stat-card intercessor-stat-total">
            <span class="intercessor-stat-icon ipr-icon ipr-icon-praying" aria-hidden="true"></span>
            <span class="intercessor-stat-number"><?php echo esc_html( (string) $intercessor_total_pr ); ?></span>
            <span class="intercessor-stat-label"><?php esc_html_e( 'Total Prayer Requests', 'intercessor' ); ?></span>
        </div>

        <div class="intercessor-stat-card intercessor-stat-pending">
            <span class="intercessor-stat-icon ipr-icon ipr-icon-warning1" aria-hidden="true"></span>
            <span class="intercessor-stat-number"><?php echo esc_html( (string) $intercessor_pending ); ?></span>
            <span class="intercessor-stat-label"><?php esc_html_e( 'Awaiting Moderation', 'intercessor' ); ?></span>
        </div>

        <div class="intercessor-stat-card intercessor-stat-approved">
            <span class="intercessor-stat-icon ipr-icon ipr-icon-checkmark" aria-hidden="true"></span>
            <span class="intercessor-stat-number"><?php echo esc_html( (string) $intercessor_approved ); ?></span>
            <span class="intercessor-stat-label"><?php esc_html_e( 'Approved', 'intercessor' ); ?></span>
        </div>

        <div class="intercessor-stat-card intercessor-stat-requesters">
            <span class="intercessor-stat-icon ipr-icon ipr-icon-user" aria-hidden="true"></span>
            <span class="intercessor-stat-number"><?php echo esc_html( (string) $intercessor_total_rq ); ?></span>
            <span class="intercessor-stat-label"><?php esc_html_e( 'Requesters', 'intercessor' ); ?></span>
        </div>

    </div>

    <p>
        <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=intercessor-requests' ) ); ?>">
            <?php esc_html_e( 'Manage Prayer Requests', 'intercessor' ); ?>
        </a>
        &nbsp;
        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=intercessor-settings' ) ); ?>">
            <?php esc_html_e( 'Settings', 'intercessor' ); ?>
        </a>
    </p>
</div>
