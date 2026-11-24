<?php

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

defined('ABSPATH') || exit;

$wpDateFormat = get_option('date_format') . ' ' . get_option('time_format');

$hasListedWorkflows = false;

$container = Container::getInstance();
$cachePostsWithFutureActions = $container->get(ServicesAbstract::CACHE_POSTS_WITH_FUTURE_ACTION);

foreach ($enabledWorkflows as $workflowModel) :
    $schedules = $postModel->getManuallyEnabledWorkflowsSchedule($workflowModel->getId());
    $workflowLabel = $workflowModel->getManualSelectionLabel();

    if (empty($schedules)) :
        continue;
    endif;

    foreach ($schedules as $schedule) :
        if (empty($schedule)) :
            continue;
        endif;

        $timestamp = $schedule['timestamp'];
        if (! is_numeric($timestamp)) :
            $timestamp = strtotime($timestamp);
        endif;

        ?>
        <div class="post-expire-col">
            <span class="dashicons dashicons-clock icon-scheduled" aria-hidden="true"></span>
            <span class="future-action-action-name"><?php echo esc_html((string) $workflowLabel); ?></span>
            <span class="future-action-action-date"><?php echo esc_html((string) wp_date($wpDateFormat, $timestamp)); ?></span>
        </div>
        <?php
        $hasListedWorkflows = true;
        $cachePostsWithFutureActions->addValue((string) $postModel->getId());
    endforeach;
endforeach;
