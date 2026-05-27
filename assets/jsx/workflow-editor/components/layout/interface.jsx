import classnames from 'classnames';
import { InterfaceSkeleton, ComplementaryArea } from "@wordpress/interface";
import { LayoutContent } from "./content";
import { LayoutFooter } from "./footer";
import { LayoutHeader } from "./header";
import { useSelect, useDispatch } from "@wordpress/data";
import { Button } from "@wordpress/components";
import { useViewportMatch } from "@wordpress/compose";
import { store as workflowStore } from "../workflow-store";
import { store as editorStore } from "../editor-store";
import { __ } from "@wordpress/i18n";
import { SIDEBAR_NODE_EDGE, SIDEBAR_WORKFLOW } from "../settings-sidebar/constants";
import { SLOT_SCOPE_WORKFLOW_EDITOR, FEATURE_SHOW_ICON_LABELS } from "../../constants";
import EditorNotices from "../editor-notices";

export function WorkflowEditorInterface({ className, secondarySidebar }) {
    const isMobileViewport = useViewportMatch('medium', '<');

    const {
        sidebarIsOpened,
        hasFixedToolbar,
        hasActiveMetaboxes,
        showIconLabels,
        hasSelectedNodes
    } = useSelect((select) => {
        const activeComplementaryArea = select('core/interface').getActiveComplementaryArea(SLOT_SCOPE_WORKFLOW_EDITOR);

        return {
            sidebarIsOpened: activeComplementaryArea !== null && activeComplementaryArea !== 'null/undefined',
            hasFixedToolbar: false,
            hasActiveMetaboxes: false,
            showIconLabels: select(editorStore).isFeatureActive(FEATURE_SHOW_ICON_LABELS),
            hasSelectedNodes: select(workflowStore).hasSelectedNodes(),
        }
    });

    const {
        openGeneralSidebar,
        closeInserter,
    } = useDispatch(editorStore);

    const interfaceClassNames = classnames(className, {
        'is-sidebar-opened': sidebarIsOpened,
        'has-fixed-toolbar': hasFixedToolbar,
        'has-metaboxes': hasActiveMetaboxes,
        'show-icon-labels': showIconLabels,
    });

    const openSidebarPanel = () => {
        openGeneralSidebar(
            hasSelectedNodes ? SIDEBAR_NODE_EDGE : SIDEBAR_WORKFLOW
        );
    }

    return (
        <InterfaceSkeleton
            className={interfaceClassNames}
            header={<LayoutHeader />}
            secondarySidebar={secondarySidebar()}
            content={<LayoutContent />}
            footer={<LayoutFooter />}
            actions={null}
            sidebar={
                (!isMobileViewport || sidebarIsOpened) && (
                    <ComplementaryArea.Slot scope={SLOT_SCOPE_WORKFLOW_EDITOR} />
                )
            }
        >
        </InterfaceSkeleton>
    );
}
