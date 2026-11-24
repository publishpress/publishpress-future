<?php
defined('ABSPATH') || exit;
?>
<div id="publishpress-future-classic-editor"></div>

<input type="hidden" name="future_action_enabled" value="<?php echo $enabled ? 1 : 0; ?>" />
<input type="hidden" name="future_action_date" value="<?php echo esc_attr($date); ?>" />
<input type="hidden" name="future_action_action" value="<?php echo esc_attr($action); ?>" />
<input type="hidden" name="future_action_new_status" value="<?php echo esc_attr($newStatus); ?>" />
<input type="hidden" name="future_action_terms" value="<?php echo esc_attr(implode(',', $terms)); ?>" />
<input type="hidden" name="future_action_taxonomy" value="<?php echo esc_attr($taxonomy); ?>" />

<input name="future_action_view" value="classic-editor" type="hidden" />
<?php
wp_nonce_field('__future_action', '_future_action_nonce');
