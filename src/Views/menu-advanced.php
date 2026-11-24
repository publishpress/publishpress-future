<?php

use PublishPress\Future\Core\DI\Container as DIContainer;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Settings\HooksAbstract;

defined('ABSPATH') || exit;

// phpcs:disable Generic.Files.LineLength.TooLong

// Get Option

$container = DIContainer::getInstance();
$hooks = $container->get(ServicesAbstract::HOOKS);
$settingsFacade = $container->get(ServicesAbstract::SETTINGS);

$preserveData = $settingsFacade->getSettingPreserveData();

?>
<div class="pp-columns-wrapper<?php echo $showSideBar ? ' pp-enable-sidebar' : ''; ?>">
    <div class="pp-column-left">
        <form method="post" id="expirationdate_save_options">
            <?php
            wp_nonce_field('postexpirator_menu_advanced', '_postExpiratorMenuAdvanced_nonce'); ?>

            <h3><?php esc_html_e('Advanced Options', 'post-expirator'); ?></h3>
            <table class="form-table">

                <?php $hooks->doAction(HooksAbstract::ACTION_SETTINGS_TAB_ADVANCED_BEFORE); ?>

                <!-- Configure the Scheduled Workflow's Cron Cleanup Rules -->
                <tr id="scheduled-steps-cleanup-settings">
                    <!-- React component -->
                </tr>

                <!-- Enable experimental features -->
                <?php if (PUBLISHPRESS_FUTURE_WORKFLOW_EXPERIMENTAL) : ?>
                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e('Experimental Features', 'post-expirator'); ?>
                        </th>
                        <td>
                            <div class="pp-settings-field-row">
                                <input type="checkbox" name="future-experimental-features"
                                        id="future-experimental-features"
                                        value="1"
                                        <?php echo $settingsFacade->getExperimentalFeaturesStatus() ? 'checked' : ''; ?> />
                                <label for="future-experimental-features"><?php
                                    esc_html_e('Enable experimental features', 'post-expirator'); ?></label>
                                <p class="description offset">
                                    <?php esc_html_e(
                                        'Enable experimental features that are still in development and may not be fully functional.',
                                        'post-expirator'
                                    ); ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr valign="top">
                    <th scope="row">
                        <?php
                        esc_html_e('Preserve Data After Deactivating the Plugin', 'post-expirator'); ?>
                    </th>
                    <td>
                        <fieldset class="pp-a11y-fieldset">
                        <legend class="screen-reader-text"><?php esc_html_e('Preserve Data After Deactivating the Plugin', 'post-expirator'); ?></legend>
                        <div class="pp-settings-field-row">
                            <input type="radio" name="expired-preserve-data-deactivating"
                                id="expired-preserve-data-deactivating-true"
                                value="1" <?php
                                    echo $preserveData ? ' checked="checked"' : ''; ?>/>
                            <label for="expired-preserve-data-deactivating-true">
                                <?php
                                    esc_html_e('Preserve data', 'post-expirator'); ?>
                            </label>
                        </div>
                        <div class="pp-settings-field-row">
                            <input type="radio" name="expired-preserve-data-deactivating"
                                id="expired-preserve-data-deactivating-false"
                                value="0" <?php
                                    echo ! $preserveData ? ' checked="checked"' : ''; ?>/>
                            <label for="expired-preserve-data-deactivating-false">
                                <?php
                                    esc_html_e('Delete data', 'post-expirator'); ?>
                            </label>
                        </div>
                        <p class="description">
                            <?php
                            esc_html_e(
                                'Toggle between preserving or deleting data after the plugin is deactivated.',
                                'post-expirator'
                            ); ?>
                        </p>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="expirationdateSave" class="button-primary"
                       value="<?php esc_attr_e('Save Changes', 'post-expirator'); ?>"/>
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
