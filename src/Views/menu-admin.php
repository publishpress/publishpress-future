<?php

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

defined('ABSPATH') || exit;

// phpcs:disable Generic.Files.LineLength.TooLong

$container = Container::getInstance();
$settingsFacade = $container->get(ServicesAbstract::SETTINGS);

// phpcs:disable WordPress.NamingConventions.ValidVariableName.InterpolatedVariableNotSnakeCase
// phpcs:disable WordPress.PHP.StrictComparisons.LooseComparison

$metaboxTitle = $settingsFacade->getMetaboxTitle();
$metaboxCheckboxLabel = $settingsFacade->getMetaboxCheckboxLabel();

$timeFormat = $settingsFacade->getTimeFormatForDatePicker();
$columnStyle = $settingsFacade->getColumnStyle();
?>
<div class="pp-columns-wrapper<?php echo $showSideBar ? ' pp-enable-sidebar' : ''; ?>">
    <div class="pp-column-left">
        <form method="post" id="expirationdate_save_options">
            <?php
            wp_nonce_field('postexpirator_menu_admin', '_postExpiratorMenuAdmin_nonce'); ?>
            <h3><?php
                esc_html_e('Metabox', 'post-expirator'); ?></h3>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="expirationdate-metabox-title">
                            <?php
                                                        esc_html_e('Metabox Title', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            name="expirationdate-metabox-title"
                            id="expirationdate-metabox-title"
                            value="<?php echo esc_attr($metaboxTitle); ?>"
                            size="25"
                            placeholder="<?php esc_attr_e('Future Actions', 'post-expirator'); ?>"
                        />
                        <p class="description">
                            <?php
                                                        esc_html_e('The title of the metabox that will be displayed in the post edit screen.', 'post-expirator'); ?>
                        </p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="expirationdate-metabox-checkbox-label">
                            <?php
                                                        esc_html_e('Checkbox Field Label', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            name="expirationdate-metabox-checkbox-label"
                            id="expirationdate-metabox-checkbox-label"
                            value="<?php echo esc_attr($metaboxCheckboxLabel); ?>"
                            size="25"
                            placeholder="<?php esc_attr_e('Enable Future Action', 'post-expirator'); ?>"
                        />
                        <p class="description">
                            <?php esc_html_e('The label of the checkbox field that will be displayed in the metabox.', 'post-expirator'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <hr />

            <h3><?php esc_html_e('Future Actions Column', 'post-expirator'); ?></h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Future Action Column Style', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <fieldset class="pp-a11y-fieldset">
                        <legend class="screen-reader-text"><?php esc_html_e('Future Action Column Style', 'post-expirator'); ?></legend>
                        <div class="pp-settings-field-row">
                            <input type="radio" name="future-action-column-style"
                                id="future-action-column-style-verbose"
                                value="verbose" <?php
                                                                            echo $columnStyle === 'verbose' ? 'checked' : ''; ?> />
                            <label for="future-action-column-style-verbose">
                                <?php esc_html_e('Detailed', 'post-expirator'); ?>
                            </label>
                            <p class="description offset">
                                <?php esc_html_e('Displays all information in the Future Action column on the "Posts" screen.', 'post-expirator'); ?>
                            </p>
                        </div>

                        <div class="pp-settings-field-row">
                            <input type="radio" name="future-action-column-style"
                                id="future-action-column-style-simple"
                                value="simple" <?php
                                                                            echo $columnStyle === 'simple' ? 'checked' : ''; ?> />
                            <label for="future-action-column-style-simple"><?php
                                                                                                        esc_html_e('Simplified', 'post-expirator'); ?></label>
                            <p class="description offset">
                                <?php esc_html_e('Displays only the icon and date/time in the Future Action column on the "Posts" screen.', 'post-expirator'); ?>
                            </p>
                        </div>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <hr />

            <h3><?php
                esc_html_e('Future Actions Editor', 'post-expirator'); ?></h3>

            <p class="description">
                <?php esc_html_e('This controls the time format used to select dates for Future Actions.', 'post-expirator'); ?>
            </p>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Time format in the date picker', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <fieldset class="pp-a11y-fieldset">
                        <legend class="screen-reader-text"><?php esc_html_e('Time format in the date picker', 'post-expirator'); ?></legend>
                        <div class="pp-settings-field-row">
                            <input
                                type="radio"
                                name="future-action-time-format"
                                id="future-action-time-format-inherited"
                                value="inherited"
                                <?php echo $timeFormat === 'inherited' ? 'checked' : ''; ?> />
                            <label for="future-action-time-format-inherited">
                                <?php esc_html_e('Inherit from Site Settings', 'post-expirator'); ?>
                            </label>
                        </div>
                        <div class="pp-settings-field-row">
                            <input
                                type="radio"
                                name="future-action-time-format"
                                id="future-action-time-format-24h"
                                value="24h"
                                <?php echo $timeFormat === '24h' ? 'checked' : ''; ?> />
                            <label for="future-action-time-format-24h">
                                <?php esc_html_e('24 hours', 'post-expirator'); ?>
                            </label>
                        </div>
                        <div class="pp-settings-field-row">
                            <input
                                type="radio"
                                name="future-action-time-format"
                                id="future-action-time-format-12h"
                                value="12h"
                                <?php echo $timeFormat === '12h' ? 'checked' : ''; ?> />
                            <label for="future-action-time-format-12h">
                                <?php esc_html_e('AM/PM', 'post-expirator'); ?>
                            </label>
                        </div>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input
                    type="submit"
                    name="expirationdateSaveAdmin"
                    class="button-primary"
                    value="<?php esc_attr_e('Save Changes', 'post-expirator'); ?>"
                />
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
