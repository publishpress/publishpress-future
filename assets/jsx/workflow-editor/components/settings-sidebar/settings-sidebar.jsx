import { Platform } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { cog } from '@wordpress/icons';
import { store as keyboardShortcutStore } from '@wordpress/keyboard-shortcuts';
import { SHORTCUT_TOGGLE_SIDEBAR } from '../keyboard-shortcuts/constants';
import { PluginSidebarEditPost } from './plugin-sidebar';
import { SettingsHeader } from './settings-header';
import { __ } from '@wordpress/i18n';
import {
    SLOT_SCOPE_WORKFLOW_EDITOR,
    FEATURE_DEVELOPER_MODE
} from '../../constants';
import { SIDEBAR_WORKFLOW, SIDEBAR_NODE_EDGE } from './constants';
import { WorkflowSummary } from '../workflow-summary';
import { NodeInspector } from '../node-inspector';
import { store as editorStore } from '../editor-store';
import WorkflowDebugRayPanel from '../workflow-debug-ray-panel';


const SIDEBAR_ACTIVE_BY_DEFAULT = Platform.select({
    web: true,
    native: false,
});

export const SettingsSidebar = () => {
    const { sidebarName, keyboardShortcut } = useSelect((select) => {
        const shortcut = select(keyboardShortcutStore).getShortcutRepresentation(SHORTCUT_TOGGLE_SIDEBAR);
        const sidebarName = select('core/interface').getActiveComplementaryArea(SLOT_SCOPE_WORKFLOW_EDITOR);

        return {
            sidebarName: sidebarName,
            keyboardShortcut: shortcut,
        };
    });

    const {
        isDeveloperModeEnabled,
        isRayDebugInstalled,
    } = useSelect((select) => {
        return {
            isDeveloperModeEnabled: select(editorStore).isFeatureActive(FEATURE_DEVELOPER_MODE),
            isRayDebugInstalled: select(editorStore).isRayDebugInstalled(),
        };
    });

    return (
        <PluginSidebarEditPost
            identifier={sidebarName}
            header={<SettingsHeader sidebarName={sidebarName} />}
            closeLabel={__('Close settings', 'post-expirator')}
            headerClassName="edit-post-sidebar__panel-tabs"
            className="edit-post-sidebar__panel"
            /* translators: button label text should, if possible, be under 16 characters. */
            title={__('Settings', 'post-expirator')}
            toggleShortcut={keyboardShortcut}
            icon={cog}
            isActiveByDefault={SIDEBAR_ACTIVE_BY_DEFAULT}
        >
            {sidebarName === SIDEBAR_WORKFLOW && (
                <>
                    <WorkflowSummary />
                    {isDeveloperModeEnabled && isRayDebugInstalled && (
                        <>
                            <WorkflowDebugRayPanel />
                        </>
                    )}
                </>
            )}
            {sidebarName === SIDEBAR_NODE_EDGE && (
                <>
                    <NodeInspector />
                </>
            )}
        </PluginSidebarEditPost>
    );
}

export default SettingsSidebar;
