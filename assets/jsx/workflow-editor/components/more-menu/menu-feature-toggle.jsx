/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { MenuItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { check } from '@wordpress/icons';
import { speak } from '@wordpress/a11y';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../editor-store';

export const MoreMenuFeatureToggle = ({
    scope,
    label,
    info,
    messageActivated,
    messageDeactivated,
    shortcut,
    feature,
}) => {
    const isActive = useSelect(
        (select) =>
            select(editorStore).isFeatureActive(feature),
        [feature]
    );
    const { toggleFeature } = useDispatch(editorStore);
    const speakMessage = () => {
        if (isActive) {
            speak(messageDeactivated || __('Feature deactivated', 'post-expirator'));
        } else {
            speak(messageActivated || __('Feature activated', 'post-expirator'));
        }
    };

    return (
        <MenuItem
            icon={isActive && check}
            isSelected={isActive}
            onClick={() => {
                toggleFeature(feature);
                speakMessage();
            }}
            role="menuitemcheckbox"
            info={info}
            shortcut={shortcut}
        >
            {label}
        </MenuItem>
    );
}
