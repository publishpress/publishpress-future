/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { close } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import {
    useViewportMatch,
    __experimentalUseDialog as useDialog,
} from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { store as editorStore } from '../editor-store';
import { FEATURE_MOST_USED_NODES, FEATURE_INSERTER } from '../../constants';
import { InserterLibrary } from './library';

export function InserterSidebar() {
    const { showMostUsedNodes } = useSelect((select) => {
        return {
            showMostUsedNodes: select(editorStore).isFeatureActive(FEATURE_MOST_USED_NODES),
        };
    }, []);

    const { disableFeature } = useDispatch(editorStore);

    const closeInserter = () => {
        disableFeature(FEATURE_INSERTER);
    };

    const isMobileViewport = useViewportMatch('medium', '<');
    const [inserterDialogRef, inserterDialogProps] = useDialog({
        onClose: () => closeInserter(),
    });

    return (
        <div
            ref={inserterDialogRef}
            {...inserterDialogProps}
            className="editor-inserter-sidebar"
        >
            <div className="editor-inserter-sidebar__header">
                <Button
                    icon={close}
                    label={__('Close block inserter', 'post-expirator')}
                    onClick={() => closeInserter()}
                    className="block-editor-tabbed-sidebar__close-button is-small"
                />
            </div>
            <div className="editor-inserter-sidebar__content">
                <InserterLibrary
                    showMostUsedNodes={showMostUsedNodes}
                    showInserterHelpPanel={true}
                    shouldFocusNode={isMobileViewport}
                />
            </div>
        </div>
    );
}
