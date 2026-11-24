<?php

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

defined('ABSPATH') || exit;

if (! isset($opts['name'])) {
    return false;
}
if (! isset($opts['id'])) {
    $opts['id'] = $opts['name'];
}
if (! isset($opts['type'])) {
    $opts['type'] = '';
}

// Maybe settings have not been configured.
if (empty($opts['type']) && isset($opts['post_type'])) {
    $opts['type'] = $opts['post_type'];
}

$container = Container::getInstance();
$actionsModel = $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL);
$actions = $actionsModel->getActionsAsOptions($opts['type']);

?>
<select name="<?php echo esc_attr($opts['name']); ?>" id="<?php echo esc_attr($opts['id']); ?>" class="pe-howtoexpire">
    <?php
    foreach ($actions as $action) {
        ?>
        <option value="<?php echo esc_attr($action['value']); ?>"
            <?php selected($opts['selected'], $action['value'], true); ?>
        >
            <?php echo esc_html($action['label']); ?>
        </option>
        <?php
    }
    ?>
</select>
