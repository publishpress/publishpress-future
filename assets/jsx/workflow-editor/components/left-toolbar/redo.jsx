/**
 * WordPress dependencies
 */
import { __, isRTL } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { displayShortcut } from '@wordpress/keycodes';
import { redo as redoIcon, undo as undoIcon } from '@wordpress/icons';
import { forwardRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { store as workflowStore } from '../workflow-store';

function Component(props, ref) {
    const hasRedo = useSelect(
        // (select) => select(workflowStore).hasRedo(),
        (select) => false,
        []
    );

    const { redo } = useDispatch(workflowStore);

    return (
        <Button
            {...props}
            ref={ref}
            icon={!isRTL() ? redoIcon : undoIcon}
            /* translators: button label text should, if possible, be under 16 characters. */
            label={__('Redo', 'post-expirator')}
            shortcut={displayShortcut.primaryShift('z')}
            // If there are no redo levels we don't want to actually disable this
            // button, because it will remove focus for keyboard users.
            // See: https://github.com/WordPress/gutenberg/issues/3486
            aria-disabled={!hasRedo}
            onClick={hasRedo ? redo : undefined}
            className="editor-history__redo"
        />
    );
}

export const EditorHistoryRedo = forwardRef(Component);
