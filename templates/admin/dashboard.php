<?php
/**
 * Admin template: Intercessor dashboard.
 *
 * @package Intercessor
 * @since   1.0.0
 */
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables included via require, not true globals

declare(strict_types=1);

defined('ABSPATH') || exit;

use Intercessor\Database\Query\Prayer_Request_Query;
use Intercessor\Database\Query\Requester_Query;

$prQuery  = new Prayer_Request_Query();
$rqQuery  = new Requester_Query();
$pending  = count( $prQuery->get_pending() );
$approved = $prQuery->count_items( [ 'status' => 'approved' ] );
$totalPR  = $prQuery->count_items( [] );
$totalRQ  = $rqQuery->count_items( [] );
?>
<div class="wrap intercessor-dashboard">
    <h1><?php esc_html_e( 'Intercessor Dashboard', 'intercessor' ); ?></h1>

    <div class="intercessor-stats">

        <div class="intercessor-stat-card intercessor-stat-total">
            <span class="intercessor-stat-icon ipr-icon ipr-icon-praying" aria-hidden="true"></span>
            <span class="intercessor-stat-number"><?php echo esc_html( (string) $totalPR ); ?></span>
            <span class="intercessor-stat-label"><?php esc_html_e( 'Total Prayer Requests', 'intercessor' ); ?></span>
        </div>

        <div class="intercessor-stat-card intercessor-stat-pending">
            <span class="intercessor-stat-icon ipr-icon ipr-icon-warning1" aria-hidden="true"></span>
            <span class="intercessor-stat-number"><?php echo esc_html( (string) $pending ); ?></span>
            <span class="intercessor-stat-label"><?php esc_html_e( 'Awaiting Moderation', 'intercessor' ); ?></span>
        </div>

        <div class="intercessor-stat-card intercessor-stat-approved">
            <span class="intercessor-stat-icon ipr-icon ipr-icon-checkmark" aria-hidden="true"></span>
            <span class="intercessor-stat-number"><?php echo esc_html( (string) $approved ); ?></span>
            <span class="intercessor-stat-label"><?php esc_html_e( 'Approved', 'intercessor' ); ?></span>
        </div>

        <div class="intercessor-stat-card intercessor-stat-requesters">
            <span class="intercessor-stat-icon ipr-icon ipr-icon-user" aria-hidden="true"></span>
            <span class="intercessor-stat-number"><?php echo esc_html( (string) $totalRQ ); ?></span>
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
