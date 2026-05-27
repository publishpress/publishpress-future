<?php
defined('ABSPATH') or die('Direct access not allowed.');

// phpcs:disable Generic.Files.LineLength.TooLong

use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooksAbstract;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

$container = Container::getInstance();
$hooks = $container->get(ServicesAbstract::HOOKS);
$settingsFacade = $container->get(ServicesAbstract::SETTINGS);
$dateTimeFacade = $container->get(ServicesAbstract::DATETIME);

// phpcs:disable WordPress.NamingConventions.ValidVariableName.InterpolatedVariableNotSnakeCase
// phpcs:disable WordPress.PHP.StrictComparisons.LooseComparison

$expireddisplayfooter = $settingsFacade->getShowInPostFooter();
$expirationdateFooterContents = $settingsFacade->getFooterContents();
$expirationdateFooterStyle = $settingsFacade->getFooterStyle();

$expirationdateDefaultDateFormat = $settingsFacade->getDefaultDateFormat();
$expirationdateDefaultTimeFormat = $settingsFacade->getDefaultTimeFormat();

$shortcodeWrapper = $settingsFacade->getShortcodeWrapper();
$shortcodeWrapperClass = $settingsFacade->getShortcodeWrapperClass();
?>
<div class="pp-columns-wrapper<?php echo $showSideBar ? ' pp-enable-sidebar' : ''; ?>">
    <div class="pp-column-left">
        <form method="post" id="expirationdate_save_options">
            <?php
            wp_nonce_field('postexpirator_menu_display', '_postExpiratorMenuDisplay_nonce'); ?>

            <h3><?php esc_html_e('Default Formats', 'post-expirator'); ?></h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="expired-default-date-format">
                            <?php esc_html_e('Date Format', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            name="expired-default-date-format"
                            id="expired-default-date-format"
                            value="<?php echo esc_attr($expirationdateDefaultDateFormat); ?>"
                            placeholder="<?php echo esc_attr($dateTimeFacade->getDefaultDateFormat()); ?>"
                            size="25"
                        /> <span class="description">(
                            <?php
                            echo esc_html(
                                $dateTimeFacade->getWpDate(
                                    $expirationdateDefaultDateFormat,
                                    time(),
                                    $dateTimeFacade->getDefaultDateFormat()
                                )
                            ); ?>
                        )</span>
                        <p class="description">
                            <?php
                                                        echo sprintf(
                                                            // translators: %s is a link to the PHP date function documentation
                                                            esc_html__(
                                                                'The default format to use when displaying the action date within a post using the shortcode or within the footer.  For information on valid formatting options, see: %s.',
                                                                'post-expirator'
                                                            ),
                                                            '<a href="https://www.php.net/manual/en/function.date.php" target="_blank">' . esc_html__('PHP Date Function', 'post-expirator') . '</a>'
                                                        ); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="expired-default-time-format">
                            <?php
                                                        esc_html_e('Time Format', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            name="expired-default-time-format"
                            id="expired-default-time-format"
                            value="<?php echo esc_attr($expirationdateDefaultTimeFormat); ?>"
                            size="25"
                            placeholder="<?php echo esc_attr($dateTimeFacade->getDefaultTimeFormat()); ?>"
                        /> <span class="description">(
                            <?php
                                                        echo esc_html(
                                                            $dateTimeFacade->getWpDate(
                                                                $expirationdateDefaultTimeFormat,
                                                                time(),
                                                                $dateTimeFacade->getDefaultTimeFormat()
                                                            )
                                                        ); ?>
                        )</span>
                        <p class="description">
                            <?php
                                                        echo sprintf(
                                                            // translators: %s is a link to the PHP date function documentation
                                                            esc_html__(
                                                                'The default format to use when displaying the action time within a post using the shortcode or within the footer.  For information on valid formatting options, see: %s.',
                                                                'post-expirator'
                                                            ),
                                                            '<a href="https://www.php.net/manual/en/function.date.php" target="_blank">' . esc_html__('PHP Date Function', 'post-expirator') . '</a>'
                                                        ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <hr />

            <h3><?php
                esc_html_e('Post Footer Display', 'post-expirator'); ?></h3>
            <p class="description">
                <?php esc_html_e(
                    'Enabling this below will display the action date automatically at the end of any post which is set to run an action.',
                    'post-expirator'
                ); ?>
            </p>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="expired-display-footer">
                            <?php esc_html_e('Show in Post Footer?', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <div class="pp-settings-field-row">
                            <input
                                type="radio"
                                name="expired-display-footer"
                                id="expired-display-footer-true"
                                value="1"
                                <?php echo $expireddisplayfooter ? 'checked' : ''; ?> />
                            <label for="expired-display-footer-true">
                                <?php esc_html_e('Enabled', 'post-expirator'); ?>
                            </label>
                        </div>
                        <div class="pp-settings-field-row">
                            <input
                                type="radio"
                                name="expired-display-footer"
                                id="expired-display-footer-false"
                                value="0"
                                <?php echo !$expireddisplayfooter ? 'checked' : ''; ?> />
                            <label for="expired-display-footer-false">
                                <?php esc_html_e('Disabled', 'post-expirator'); ?>
                            </label>
                        </div>
                        <p class="description">
                            <?php esc_html_e(
                                'This will enable or disable displaying the future action date in the post footer.',
                                'post-expirator'
                            ); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="expired-footer-contents">
                            <?php esc_html_e('Footer Contents', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea
                            id="expired-footer-contents"
                            name="expired-footer-contents"
                            rows="3"
                            cols="50"
                        ><?php echo esc_textarea($expirationdateFooterContents); ?></textarea>
                        <p class="description">
                            <?php esc_html_e(
                                'Enter the text you would like to appear at the bottom of every post which has an action scheduled.  The following placeholders will be replaced with the future action date in the following format:',
                                'post-expirator'
                            ); ?>
                        </p>
                        <ul class="pe-list">
                            <li>
                                <p class="description">
                                    ACTIONFULL ->
                                    <?php
                                    if (empty($expirationdateDefaultDateFormat)) {
                                        $expirationdateDefaultDateFormat = $dateTimeFacade->getDefaultDateFormat();
                                    }

                                    if (empty($expirationdateDefaultTimeFormat)) {
                                        $expirationdateDefaultTimeFormat = $dateTimeFacade->getDefaultTimeFormat();
                                    }

                                    echo esc_html(date_i18n(
                                        "$expirationdateDefaultDateFormat $expirationdateDefaultTimeFormat"
                                    )); ?>
                                </p>
                            </li>
                            <li>
                                <p class="description">
                                    ACTIONDATE ->
                                    <?php echo esc_html(date_i18n("$expirationdateDefaultDateFormat")); ?>
                                </p>
                            </li>
                            <li>
                                <p class="description">
                                    ACTIONTIME ->
                                    <?php echo esc_html(date_i18n("$expirationdateDefaultTimeFormat")); ?>
                                </p>
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="expired-footer-style">
                            <?php esc_html_e('Footer Style', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea
                            name="expired-footer-style"
                            id="expired-footer-style"
                            rows="3"
                            cols="50"
                        ><?php echo esc_textarea($expirationdateFooterStyle); ?></textarea>
                        <p class="description">
                            <?php esc_html_e('The inline css which will be used to style the footer text.', 'post-expirator'); ?>
                        </p>
                        <br>
                        <div>
                            <label><?php echo esc_html__('Example: ', 'post-expirator'); ?></label>
                            <div style="background: white; padding: 10px; <?php echo esc_attr($expirationdateFooterStyle); ?>">
                                <?php echo esc_html($hooks->applyFilters(ExpiratorHooksAbstract::FILTER_CONTENT_FOOTER, '', true)); ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <hr />

            <h3><?php
                esc_html_e('Shortcode', 'post-expirator'); ?></h3>

            <p class="description">
                <?php
                echo sprintf(
                    // translators: %s is a code tag that wraps the shortcode
                    esc_html__('Use the %s[futureaction]%s shortcode to show when the future action will occur. You can add this shortcode anywhere in your post content.', 'post-expirator'),
                    '<code>',
                    '</code>'
                ); ?>
            </p>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="shortcode-wrapper">
                            <?php esc_html_e('Shortcode Wrapper', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <?php
                        $wrapperOptions = [
                            // translators: This is used to indicate that no wrapper should be used on the shortcode output
                            '' => __('— None —', 'post-expirator'),
                            'p' => '&lt;p&gt;',
                            'div' => '&lt;div&gt;',
                            'span' => '&lt;span&gt;',
                        ];
                        ?>
                        <div class="pp-settings-field-row">
                            <select name="shortcode-wrapper" id="shortcode-wrapper">
                                <?php foreach ($wrapperOptions as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php echo $value === $shortcodeWrapper ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p class="description">
                            <?php esc_html_e(
                                'The shortcode output will be wrapped in the selected HTML tag, allowing you to control its structure and styling.',
                                'post-expirator'
                            ); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top" id="shortcode-wrapper-class-row" style="display: none;">
                    <th scope="row">
                        <label for="shortcode-wrapper-class">
                            <?php esc_html_e('Wrapper Class', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="shortcode-wrapper-class" id="shortcode-wrapper-class" value="<?php echo esc_attr($shortcodeWrapperClass); ?>" size="25" />
                        <p class="description">
                            <?php esc_html_e('Add a CSS class to the wrapper element for custom styling.', 'post-expirator'); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Attributes', 'post-expirator'); ?>
                        </label>
                    </th>
                    <td>
                        <p><?php
                            // translators: %s is the shortcode code wrapped in code tags
                                echo sprintf(esc_html__('The following attributes are available for the %s shortcode:', 'post-expirator'), '<code>[futureaction]</code>'); ?>
                        </p>
                        <ul class="pe-list">
                            <li>
                                <p><?php
                                        echo sprintf(
                                            // translators: %s is a code tag that wraps the shortcode attribute name
                                            esc_html__('%s - Available options:', 'post-expirator'),
                                            '<code>type</code>'
                                        );
                                        echo '<ul>';
                                        echo '<li>' . sprintf(
    // translators: %s is a code tag that wraps the shortcode attribute value
                                            esc_html__('%s - Displays complete date/time.  Default value.', 'post-expirator'),
                                            '<code>full</code>'
                                        );
                                        echo '</li>';
                                        echo '<li>' . sprintf(
    // translators: %s is a code tag that wraps the shortcode attribute value
                                            esc_html__('%s - Displays date only', 'post-expirator'),
                                            '<code>date</code>'
                                        );
                                        echo '</li>';
                                        echo '<li>' . sprintf(
    // translators: %s is a code tag that wraps the shortcode attribute value
                                            esc_html__('%s - Displays time only', 'post-expirator'),
                                            '<code>time</code>'
                                        );
                                        echo '</li>';
                                        echo '</ul>';
                                        ?>
                                </p>
                            </li>
                            <li>
                                <p><?php
                                echo sprintf(
        // translators: %s is a code tag that wraps the shortcode attribute dateformat
                                    esc_html__('%s - Format set here will override the value set on the settings page', 'post-expirator'),
                                    '<code>dateformat</code>'
                                ); ?></p>
                            </li>
                            <li>
                                <p><?php
                                echo sprintf(
            // translators: %s is a code tag that wraps the shortcode attribute timeformat
                                    esc_html__('%s - Format set here will override the value set on the settings page', 'post-expirator'),
                                    '<code>timeformat</code>'
                                ); ?></p>
                            </li>
                        </ul>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input
                    type="submit"
                    name="expirationdateSaveDisplay"
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
<script>
    jQuery(document).ready(function($) {
        function handleStatusWrapperClassRow() {
            if ($('#shortcode-wrapper').val() === '') {
                $('#shortcode-wrapper-class-row').hide();
            } else {
                $('#shortcode-wrapper-class-row').show();
            }
        }

        $('#shortcode-wrapper').on('change', function() {
            handleStatusWrapperClassRow();
        });

        handleStatusWrapperClassRow();
    });
</script>
<?php
// phpcs:enable
