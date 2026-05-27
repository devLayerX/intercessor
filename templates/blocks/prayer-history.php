<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

// Variables provided by Prayer_History_Block::render().
/** @var \Intercessor\Database\Row\Prayer_Request  $request       */
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-scoped variables included via require, not true globals
/** @var \Intercessor\Database\Row\Prayer_History[] $history       */
/** @var bool $showNotes      */
/** @var bool $showModerator  */

$dateFormat = \Intercessor\Admin\Settings::get('date_format') ?: get_option('date_format');
$timeFormat = get_option('time_format');
?>
<div class="intercessor-prayer-history wp-block-intercessor-prayer-history">

    <div class="intercessor-history-header">
        <h3 class="intercessor-history-subject"><?php echo esc_html($request->subject); ?></h3>
        <span class="intercessor-status-badge intercessor-status-<?php echo esc_attr($request->status); ?>">
            <?php echo esc_html(ucfirst($request->status)); ?>
        </span>
    </div>

    <?php if (empty($history)) : ?>
        <p class="intercessor-empty"><?php esc_html_e('No history available for this request.', 'intercessor'); ?></p>
    <?php else : ?>

        <ol class="intercessor-timeline">
            <?php foreach ($history as $entry) : ?>
                <?php
                $actor = '';
                if ($showModerator && $entry->actor_user_id > 0) {
                    $user  = get_user_by('id', $entry->actor_user_id);
                    $actor = $user ? $user->display_name : __('Unknown', 'intercessor');
                }

                $formattedDate = $entry->date_created
                    ? mysql2date($dateFormat . ' ' . $timeFormat, $entry->date_created)
                    : '';
                ?>
                <li class="intercessor-timeline-entry">

                    <div class="intercessor-timeline-dot ipr-icon" aria-hidden="true"></div>

                    <div class="intercessor-timeline-content">
                        <div class="intercessor-timeline-status-change">
                            <?php if ($entry->old_status) : ?>
                                <span class="intercessor-status-tag intercessor-status-<?php echo esc_attr($entry->old_status); ?>">
                                    <?php echo esc_html(ucfirst($entry->old_status)); ?>
                                </span>
                                <span class="intercessor-arrow" aria-label="<?php esc_attr_e('changed to', 'intercessor'); ?>">&#8594;</span>
                            <?php endif; ?>

                            <span class="intercessor-status-tag intercessor-status-<?php echo esc_attr($entry->new_status); ?>">
                                <?php echo esc_html(ucfirst($entry->new_status)); ?>
                            </span>
                        </div>

                        <div class="intercessor-timeline-meta">
                            <?php if ($formattedDate) : ?>
                                <time class="intercessor-timeline-date" datetime="<?php echo esc_attr($entry->date_created); ?>">
                                    <?php echo esc_html($formattedDate); ?>
                                </time>
                            <?php endif; ?>

                            <?php if ($showModerator && $actor) : ?>
                                <span class="intercessor-timeline-actor">
                                    <?php
                                    printf(
                                        /* translators: %s: moderator display name */
                                        esc_html__('by %s', 'intercessor'),
                                        esc_html($actor)
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($showNotes && ! empty($entry->note)) : ?>
                            <div class="intercessor-timeline-note">
                                <p><?php echo wp_kses_post($entry->note); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                </li>
            <?php endforeach; ?>
        </ol>

    <?php endif; ?>

</div>
