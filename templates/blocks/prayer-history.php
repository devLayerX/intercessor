<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

// Variables provided by Prayer_History_Block::render().
/** @var \Intercessor\Database\Row\Prayer_Request  $request       */
/** @var \Intercessor\Database\Row\Prayer_History[] $history       */
/** @var bool $showNotes      */
/** @var bool $showModerator  */

$intercessor_date_format = \Intercessor\Admin\Settings::get('date_format') ?: get_option('date_format');
$intercessor_time_format = get_option('time_format');
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
            <?php foreach ($history as $intercessor_entry) : ?>
                <?php
                $intercessor_actor = '';
                if ($showModerator && $intercessor_entry->actor_user_id > 0) {
                    $intercessor_user  = get_user_by('id', $intercessor_entry->actor_user_id);
                    $intercessor_actor = $intercessor_user ? $intercessor_user->display_name : __('Unknown', 'intercessor');
                }

                $intercessor_formatted_date = $intercessor_entry->date_created
                    ? mysql2date($intercessor_date_format . ' ' . $intercessor_time_format, $entry->date_created)
                    : '';
                ?>
                <li class="intercessor-timeline-entry">

                    <div class="intercessor-timeline-dot ipr-icon" aria-hidden="true"></div>

                    <div class="intercessor-timeline-content">
                        <div class="intercessor-timeline-status-change">
                            <?php if ($intercessor_entry->old_status) : ?>
                                <span class="intercessor-status-tag intercessor-status-<?php echo esc_attr($intercessor_entry->old_status); ?>">
                                    <?php echo esc_html(ucfirst($intercessor_entry->old_status)); ?>
                                </span>
                                <span class="intercessor-arrow" aria-label="<?php esc_attr_e('changed to', 'intercessor'); ?>">&#8594;</span>
                            <?php endif; ?>

                            <span class="intercessor-status-tag intercessor-status-<?php echo esc_attr($intercessor_entry->new_status); ?>">
                                <?php echo esc_html(ucfirst($intercessor_entry->new_status)); ?>
                            </span>
                        </div>

                        <div class="intercessor-timeline-meta">
                            <?php if ($intercessor_formatted_date) : ?>
                                <time class="intercessor-timeline-date" datetime="<?php echo esc_attr($intercessor_entry->date_created); ?>">
                                    <?php echo esc_html($intercessor_formatted_date); ?>
                                </time>
                            <?php endif; ?>

                            <?php if ($showModerator && $intercessor_actor) : ?>
                                <span class="intercessor-timeline-actor">
                                    <?php
                                    // translators: %s: moderator display name
                                    printf(
                                        /* translators: %s: moderator display name */
                                        esc_html__('by %s', 'intercessor'),
                                        esc_html($intercessor_actor)
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($showNotes && ! empty($intercessor_entry->note)) : ?>
                            <div class="intercessor-timeline-note">
                                <p><?php echo wp_kses_post($intercessor_entry->note); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                </li>
            <?php endforeach; ?>
        </ol>

    <?php endif; ?>

</div>
