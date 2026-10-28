import { Button } from '@wordpress/components';
import { useEffect, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { speak } from '@wordpress/a11y';
import { chevronUp, chevronDown } from '@wordpress/icons';

/**
 * Keyboard alternative to drag and drop for rules and groups.
 * Used as the react-querybuilder `shiftActions` control element.
 */
export const ShiftActions = ({
    path = [],
    shiftUp,
    shiftDown,
    shiftUpDisabled,
    shiftDownDisabled,
    ruleOrGroup,
    disabled,
}) => {
    const pendingMoveRef = useRef(null);
    const pathKey = path.join('-');

    const isGroup = !! ruleOrGroup?.rules;

    // Announce the new position once the moved element has re-rendered.
    useEffect(() => {
        if (! pendingMoveRef.current) {
            return;
        }

        pendingMoveRef.current = null;

        const position = (path[path.length - 1] ?? 0) + 1;

        speak(
            sprintf(
                isGroup
                    /* translators: %d: the new position of the group. */
                    ? __('Group moved to position %d.', 'post-expirator')
                    /* translators: %d: the new position of the rule. */
                    : __('Rule moved to position %d.', 'post-expirator'),
                position
            ),
            'assertive'
        );
    }, [pathKey]);

    const onShift = (direction) => {
        if (disabled || (direction === 'up' ? shiftUpDisabled : shiftDownDisabled)) {
            return;
        }

        pendingMoveRef.current = direction;

        if (direction === 'up') {
            shiftUp?.();
        } else {
            shiftDown?.();
        }
    };

    if (path.length === 0) {
        // The root group can not be moved.
        return null;
    }

    return (
        <span className="conditional-editor-modal-shift-actions">
            <Button
                icon={chevronUp}
                size="small"
                label={isGroup ? __('Move group up', 'post-expirator') : __('Move rule up', 'post-expirator')}
                showTooltip
                onClick={() => onShift('up')}
                disabled={disabled || shiftUpDisabled}
                accessibleWhenDisabled
            />
            <Button
                icon={chevronDown}
                size="small"
                label={isGroup ? __('Move group down', 'post-expirator') : __('Move rule down', 'post-expirator')}
                showTooltip
                onClick={() => onShift('down')}
                disabled={disabled || shiftDownDisabled}
                accessibleWhenDisabled
            />
        </span>
    );
};

export default ShiftActions;
