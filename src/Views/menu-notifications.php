<?php

defined('ABSPATH') || exit;

// phpcs:disable Generic.Files.LineLength.TooLong

$container = \PublishPress\Future\Core\DI\Container::getInstance();
$settingsFacade = $container->get(\PublishPress\Future\Core\DI\ServicesAbstract::SETTINGS);
$optionsFacade = $container->get(\PublishPress\Future\Core\DI\ServicesAbstract::OPTIONS);

$expiredemailnotification = $settingsFacade->getSendEmailNotification();
$expiredemailnotificationadmins = $settingsFacade->getSendEmailNotificationToAdmins();
$expiredemailnotificationlist = $settingsFacade->getEmailNotificationAddressesList();
$pastDueActionsNotification = $settingsFacade->getPastDueActionsNotificationStatus();
$pastDueActionsNotificationList = $settingsFacade->getPastDueActionsNotificationAddressesList();
$expiredemailnotificationenabled = '';
$expiredemailnotificationdisabled = '';
if ($expiredemailnotification == 0) {
    $expiredemailnotificationdisabled = 'checked="checked"';
} elseif ($expiredemailnotification == 1) {
    $expiredemailnotificationenabled = 'checked="checked"';
}

$expiredemailnotificationadminsenabled = '';
$expiredemailnotificationadminsdisabled = '';
if ($expiredemailnotificationadmins == 0) {
    $expiredemailnotificationadminsdisabled = 'checked="checked"';
} elseif ($expiredemailnotificationadmins == 1) {
    $expiredemailnotificationadminsenabled = 'checked="checked"';
}

$pastDueActionsNotificationEnabled = '';
$pastDueActionsNotificationDisabled = '';
if ($pastDueActionsNotification == 0) {
    $pastDueActionsNotificationDisabled = 'checked="checked"';
} elseif ($pastDueActionsNotification == 1) {
    $pastDueActionsNotificationEnabled = 'checked="checked"';
}

$systemAdminEmail = $optionsFacade->getOption('admin_email');

?>
<div class="pp-columns-wrapper<?php echo $showSideBar ? ' pp-enable-sidebar' : ''; ?>">
    <div class="pp-column-left">
        <form method="post" id="expirationdate_save_options">
            <?php
            wp_nonce_field('postexpirator_menu_notifications', '_postExpiratorMenuNotifications_nonce'); ?>

            <h3><?php
                esc_html_e('Email Notification', 'post-expirator'); ?></h3>
            <p class="description"><?php
                esc_html_e(
                    'Whenever an action runs, an email can be sent to alert users.',
                    'post-expirator'
                ); ?></p>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php
                                        esc_html_e('Enable Email Notification?', 'post-expirator'); ?></th>
                    <td>
                        <fieldset class="pp-a11y-fieldset">
                        <legend class="screen-reader-text"><?php esc_html_e('Enable Email Notification?', 'post-expirator'); ?></legend>
                        <div class="pp-settings-field-row">
                            <input type="radio" name="expired-email-notification" id="expired-email-notification-true"
                                value="1" <?php
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                echo $expiredemailnotificationenabled; ?>/> <label
                                    for="expired-email-notification-true"><?php
                                                    esc_html_e('Enabled', 'post-expirator'); ?></label>
                        </div>
                        <div class="pp-settings-field-row">
                            <input type="radio" name="expired-email-notification" id="expired-email-notification-false"
                                value="0" <?php
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                echo $expiredemailnotificationdisabled; ?>/> <label
                                    for="expired-email-notification-false"><?php
                                                    esc_html_e('Disabled', 'post-expirator'); ?></label>
                        </div>
                        <p class="description"><?php
                                            esc_html_e(
                                                'This will enable or disable the send of email notification on future action.',
                                                'post-expirator'
                                            ); ?></p>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php
                        esc_html_e('Include Blog Administrators?', 'post-expirator'); ?></th>
                    <td>
                        <fieldset class="pp-a11y-fieldset">
                        <legend class="screen-reader-text"><?php esc_html_e('Include Blog Administrators?', 'post-expirator'); ?></legend>
                        <div class="pp-settings-field-row">
                            <input type="radio" name="expired-email-notification-admins"
                                id="expired-email-notification-admins-true"
                                value="1" <?php
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            echo $expiredemailnotificationadminsenabled; ?>/> <label
                                    for="expired-email-notification-admins-true"><?php
                                                esc_html_e('Enabled', 'post-expirator'); ?></label>
                        </div>
                        <div class="pp-settings-field-row">
                            <input type="radio" name="expired-email-notification-admins"
                                id="expired-email-notification-admins-false"
                                value="0" <?php
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            echo $expiredemailnotificationadminsdisabled; ?>/> <label
                                    for="expired-email-notification-admins-false"><?php
                                                esc_html_e('Disabled', 'post-expirator'); ?></label>
                        </div>
                        <p class="description"><?php
                            esc_html_e(
                                'This will include all users with the role of "Administrator" in the email.',
                                'post-expirator'
                            ); ?></p>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label
                                for="expired-email-notification-list"><?php
                                esc_html_e('Who to Notify', 'post-expirator'); ?></label>
                    </th>
                    <td>
                        <input class="large-text" type="text" name="expired-email-notification-list"
                               id="expired-email-notification-list" value="<?php
                                echo esc_attr(implode(', ', $expiredemailnotificationlist)); ?>"/>
                        <p class="description"><?php
                            esc_html_e(
                                'Enter a comma separate list of emails that you would like to be notified when the action runs.  This will be applied to ALL post types.  You can set post type specific emails on the Defaults tab.',
                                'post-expirator'
                            ); ?></p>
                    </td>
                </tr>
            </table>

            <h3><?php
                esc_html_e('Past-due Actions Notification', 'post-expirator'); ?></h3>
            <p class="description"><?php
                esc_html_e(
                    'This will enable or disable the send of email notification on past-due actions.',
                    'post-expirator'
                ); ?></p>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php
                        esc_html_e('Enable Past-due Actions Notification?', 'post-expirator'); ?></th>
                    <td>
                        <fieldset class="pp-a11y-fieldset">
                        <legend class="screen-reader-text"><?php esc_html_e('Enable Past-due Actions Notification?', 'post-expirator'); ?></legend>
                        <div class="pp-settings-field-row">
                            <input type="radio" name="past-due-actions-notification" id="past-due-actions-notification-true"
                                value="1" <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                echo $pastDueActionsNotificationEnabled; ?>/> <label
                                    for="past-due-actions-notification-true"><?php
                                    esc_html_e('Enabled', 'post-expirator'); ?></label>
                        </div>
                        <div class="pp-settings-field-row">
                            <input type="radio" name="past-due-actions-notification" id="past-due-actions-notification-false"
                                value="0" <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                echo $pastDueActionsNotificationDisabled; ?>/> <label
                                    for="past-due-actions-notification-false"><?php
                                    esc_html_e('Disabled', 'post-expirator'); ?></label>
                        </div>
                        <p class="description"><?php
                        esc_html_e(
                            'This will enable or disable the send of email notification on past-due actions.',
                            'post-expirator'
                        ); ?></p>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="past-due-actions-notification-list"><?php
                        esc_html_e('Who to Notify', 'post-expirator'); ?></label></th>
                    <td>
                        <input class="large-text" type="text" name="past-due-actions-notification-list"
                               id="past-due-actions-notification-list" placeholder="<?php echo esc_attr($systemAdminEmail); ?>" value="<?php
                                echo esc_attr(implode(', ', $pastDueActionsNotificationList)); ?>"/>
                        <p class="description"><?php
                            esc_html_e(
                                'Enter a comma separate list of emails that you would like to be notified when past-due actions are detected.',
                                'post-expirator'
                            ); ?></p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="expirationNotificationSave" class="button-primary"
                       value="<?php
                        esc_attr_e('Save Changes', 'post-expirator'); ?>"/>
            </p>
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
