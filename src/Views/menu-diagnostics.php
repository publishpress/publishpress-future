<?php

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Debug\HooksAbstract;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Modules\Settings\HooksAbstract as SettingsHooksAbstract;

defined('ABSPATH') or die('Direct access not allowed.');

// phpcs:disable Generic.Files.LineLength.TooLong

$container = Container::getInstance();
$debug = $container->get(ServicesAbstract::DEBUG);
$hooks = $container->get(ServicesAbstract::HOOKS);
$dateTimeFacade = $container->get(ServicesAbstract::DATETIME);

/**
 * @var DBTableSchemaInterface $actionArgsSchema
 */
$actionArgsSchema = $container->get(ServicesAbstract::DB_TABLE_ACTION_ARGS_SCHEMA);

/**
 * @var DBTableSchemaInterface $debugLogSchema
 */
$debugLogSchema = $container->get(ServicesAbstract::DB_TABLE_DEBUG_LOG_SCHEMA);

/**
 * @var DBTableSchemaInterface $workflowScheduledStepsSchema
 */
$workflowScheduledStepsSchema = $container->get(ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA);

$isSchemaHealthOk = $actionArgsSchema->isTableHealthy()
    && $debugLogSchema->isTableHealthy();

$isSchemaHealthOk = $hooks->applyFilters(SettingsHooksAbstract::FILTER_SCHEMA_IS_HEALTHY, $isSchemaHealthOk);

$schemaHealthErrors = [
    $actionArgsSchema->getTableName() => $actionArgsSchema->getErrors(),
    $debugLogSchema->getTableName() => $debugLogSchema->getErrors(),
    $workflowScheduledStepsSchema->getTableName() => $workflowScheduledStepsSchema->getErrors(),
];

$rayDebugIsInstalled = function_exists('ray');
?>

<div class="pp-columns-wrapper<?php echo $showSideBar ? ' pp-enable-sidebar' : ''; ?>">
    <div class="pp-column-left">
        <form method="post" id="postExpiratorMenuUpgrade">
            <?php
            wp_nonce_field('postexpirator_menu_diagnostics', '_postExpiratorMenuDiagnostics_nonce'); ?>
            <h3><?php
                esc_html_e('Advanced Diagnostics and Tools', 'post-expirator'); ?></h3>
            <table class="form-table">
                <tr id="diagnostics-cron-check">
                    <th scope="row"><?php
                        esc_html_e('WP-Cron Status Check', 'post-expirator'); ?></th>
                    <td>
                        <?php if (PostExpirator_CronFacade::is_cron_enabled()) : ?>
                            <i class="dashicons dashicons-yes pe-status pe-status-enabled"></i> <span><?php
                                esc_html_e('Passed', 'post-expirator'); ?></span>
                        <?php else : ?>
                            <i class="dashicons dashicons-no pe-status pe-status-disabled"></i> <span><?php
                                esc_html_e('WP Cron Disabled', 'post-expirator'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr id="diagnostics-database-schema-check">
                    <th scope="row"><?php
                        esc_html_e('Database Schema Check', 'post-expirator'); ?></th>
                    <td>
                        <p class="description" style="margin-top:0;">
                            <?php esc_html_e(
                                'After activating the plugin, tables are updated automatically on the next page load. Use the button below if you still see schema errors.',
                                'post-expirator'
                            ); ?>
                        </p>
                        <?php if ($isSchemaHealthOk) : ?>
                            <i class="dashicons dashicons-yes pe-status pe-status-enabled"></i> <span><?php
                                esc_html_e('Passed', 'post-expirator'); ?></span>
                        <?php else : ?>
                            <i class="dashicons dashicons-no pe-status pe-status-disabled"></i>
                            <span><?php echo esc_html(
                                _n(
                                    'Error found on the database schema:',
                                    'Errors found on the database schema:',
                                    count($schemaHealthErrors),
                                    'post-expirator'
                                )
                                  ); // phpcs:ignore PSR2.Methods.FunctionCallSignature.Indent?>
                            </span>

                            <?php foreach ($schemaHealthErrors as $tableName => $errors) : ?>
                                <?php if (empty($errors)) {
                                    continue;
                                } ?>

                                <table class="widefat striped" style="margin-top: 10px; margin-bottom: 10px;">
                                    <thead>
                                        <tr>
                                            <th><strong><?php echo esc_html($tableName); ?></strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($errors as $error) : ?>
                                            <tr>
                                            <td><?php echo esc_html($error); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>

                            <input type="submit" class="button" name="fix-db-schema" id="fix-db-schema" value="<?php
                            esc_attr_e('Try to Fix Database', 'post-expirator'); ?>"/>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr><td colspan="2"><hr></td></tr>

                <tr id="debug-logging">
                    <th scope="row"><label for="postexpirator-log"><?php
                            esc_html_e('Debug Logging', 'post-expirator'); ?></label></th>
                    <td>
                        <?php if ($debug->isEnabled()) : ?>
                            <i class="dashicons dashicons-yes pe-status pe-status-enabled"></i> <span><?php
                                esc_html_e('Enabled', 'post-expirator'); ?></span>
                            <?php
                            echo '<input type="submit" class="button" name="debugging-disable" id="debugging-disable" value=" '
                                . esc_html__(
                                    'Disable Debugging',
                                    'post-expirator'
                                ) . '" />'; ?>

                            <input type="submit" class="button" name="purge-debug" id="purge-debug" value="<?php
                            esc_attr_e('Purge Debug Log', 'post-expirator'); ?>"/>
                            <br /><br />

                            <?php
                            echo '<a href="' . esc_url(
                                admin_url(
                                    'admin.php?page=publishpress-future-settings&tab=viewdebug'
                                )
                            ) . '">' . esc_html__('View Debug Logs', 'post-expirator') . '</a>'; ?>
                        <?php else : ?>
                            <i class="dashicons dashicons-no-alt pe-status pe-status-disabled"></i> <span><?php
                            esc_html_e('Disabled', 'post-expirator'); ?></span>
                            <?php
                            echo '<input type="submit" class="button" name="debugging-enable" id="debugging-enable" value=" '
                            . esc_html__(
                                'Enable Debugging',
                                'post-expirator'
                            ) . '" />'; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr id="diagnostics-ray-check">
                    <th scope="row"><?php
                        esc_html_e('Spatie Ray Debug', 'post-expirator'); ?></th>
                    <td>
                        <?php if ($rayDebugIsInstalled) : ?>
                            <i class="dashicons dashicons-yes pe-status pe-status-enabled"></i> <span><?php
                                esc_html_e('Spatie Ray Detected', 'post-expirator'); ?></span>
                            <p class="description">
                                <?php esc_html_e('Spatie Ray Debug is detected. The "Send to Ray" workflow step will be available in the workflow editor.', 'post-expirator'); ?>
                            </p>
                        <?php else : ?>
                            <i class="dashicons dashicons-no-alt pe-status pe-status-disabled"></i> <span><?php
                                esc_html_e('Spatie Ray Not Detected', 'post-expirator'); ?></span>
                            <p class="description">
                                <?php esc_html_e('Spatie Ray Debug is not detected. This is not an error, but the "Send to Ray" workflow step will not be available in the workflow editor.', 'post-expirator'); ?>
                            </p>
                        <?php endif; ?>
                        <a href="https://spatie.be/products/ray" target="_blank"><?php esc_html_e('Learn more about Spatie Ray', 'post-expirator'); ?></a>
                    </td>
                </tr>
                <?php $hooks->doAction(HooksAbstract::ACTION_AFTER_DEBUG_LOG_SETTING); ?>

                <tr><td colspan="2"><hr></td></tr>

                <tr id="tools-migrate-legacy-future-actions">
                    <th scope="row"><?php
                        esc_html_e('Migrate Legacy Future Actions', 'post-expirator'); ?>
                    </th>
                    <td>
                        <input type="submit" class="button" name="migrate-legacy-actions" id="migrate-legacy-actions" value="<?php
                        esc_attr_e('Run Migration', 'post-expirator'); ?>"/>

                        <p class="description">
                            <?php esc_html_e(
                                'Migrate legacy future actions from WP Cron to the new Action Scheduler. This will run in the background and may take a while.',
                                'post-expirator'
                            ); ?>
                        </p>
                    </td>
                </tr>

                <tr id="tools-restore-legacy-action-arguments">
                    <th scope="row"><?php
                        esc_html_e('Restore Legacy Action Arguments', 'post-expirator'); ?>
                    </th>
                    <td>
                        <input type="submit" class="button" name="restore-post-meta" id="restore-post-meta" value="<?php
                        esc_attr_e('Run Data Restoration', 'post-expirator'); ?>"/>

                        <p class="description">
                            <?php esc_html_e(
                                'Restore legacy action arguments as Post Meta. This is useful if you have issues with 3rd party plugins that read that data. This will run in the background and may take a while.',
                                'post-expirator'
                            ); ?>
                        </p>
                    </td>
                </tr>

                <?php if (! empty($cron)) : ?>
                    <tr><td colspan="2"><hr></td></tr>

                    <tr id="tools-legacy-cron-schedule">
                        <th scope="row"><label for="cron-schedule"><?php
                                esc_html_e('Legacy Cron Schedule', 'post-expirator'); ?></label></th>
                        <td>
                            <?php
                            $cron = PostExpirator_CronFacade::get_plugin_cron_events();

                            ?>
                            <p><?php
                         // phpcs:disable Generic.Files.LineLength.TooLong, PSR2.Methods.FunctionCallSignature.Indent
                            esc_html_e(
                            'The below table will show all currently scheduled cron events for the plugin with the next run time.',
                            'post-expirator'
                               );
                    // phpcs:enable
                                ?></p>

                            <div>
                                <table class="striped wp-list-table widefat fixed table-view-list">
                                    <thead>
                                        <tr>
                                            <th class="pe-date-column">
                                                <?php esc_html_e('Date', 'post-expirator'); ?>
                                            </th>
                                            <th class="pe-event-column">
                                                <?php esc_html_e('Event', 'post-expirator'); ?>
                                            </th>
                                            <th>
                                                <?php esc_html_e('Posts and expiration settings', 'post-expirator'); ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $printPostEvent = function ($post) use ($container) {
                                            echo esc_html("$post->ID: $post->post_title (status: $post->post_status)");

                                            $factory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
                                            $postModel = $factory($post->ID);
                                            $attributes = $postModel->getExpirationDataAsArray();

                                            echo ': <span class="post-expiration-attributes">';
                                            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
                                            print_r($attributes);
                                            echo '</span>';
                                        };

                    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
                    foreach ($cron as $time => $value) {
        foreach ($value as $eventKey => $eventValue) {
            echo '<tr class="pe-event">';
            echo '<td>' . esc_html($dateTimeFacade->getWpDate('r', $time))
                . '</td>';
            echo '<td>' . esc_html($eventKey) . '</td>';
            $eventValueKeys = array_keys($eventValue);
            echo '<td>';
            foreach ($eventValueKeys as $eventGUID) {
                if (false === empty($eventValue[$eventGUID]['args'])) {
                    echo '<div class="pe-event-post" title="' . esc_attr((string)$eventGUID) . '">';
                    foreach ($eventValue[$eventGUID]['args'] as $value) {
                        $eventPost = get_post((int)$value);

                        if (
                            false === empty($eventPost)
                            && false === is_wp_error($eventPost)
                            && is_object($eventPost)
                        ) {
                            $printPostEvent($eventPost);
                        }
                    }
                    echo '</div>';
                }
            }
            echo '</td>';
            echo '</tr>';
        }
                    }
                    // phpcs:enable?>
                                    </tbody>
                                </table>
                            </div>
                            <p><?php
                                // phpcs:disable Generic.Files.LineLength.TooLong
                                esc_html_e(
                                    'This is a legacy feature and will be removed in a future version.',
                                    'post-expirator'
                                );
                    // phpcs:enable
                                ?></p>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </form>
    </div>

    <?php
    if ($showSideBar) {
        include __DIR__ . '/ad-banner-right-sidebar.php';
    }
    ?>
</div>
<?php

// phpcs:enable
