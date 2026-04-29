import { PanelRow, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { store as workflowStore } from '../workflow-store';
import { useSelect, useDispatch } from '@wordpress/data';
import { useCallback, useRef } from '@wordpress/element';
import { WorkflowSwitchToDraftButton } from '../workflow-switch-to-draft-button';
import { WorkflowDeleteButton } from '../workflow-delete-button';
import PersistentPanelBody from '../persistent-panel-body';
import useScrollToTop from '../scrolled-to-top';

export const WorkflowSummary = () => {
    const {
        workflow,
        isLoadingWorkflow,
    } = useSelect((select) => {
        return {
            workflow: select(workflowStore).getWorkflow(),
            isLoadingWorkflow: select(workflowStore).isLoadingWorkflow(),
        }
    });

    const {
        setEditedWorkflowAttribute,
    } = useDispatch(workflowStore);

    const onChangeTitle = useCallback((title) => {
        setEditedWorkflowAttribute('title', title);
    })

    const onChangeDescription = useCallback((description) => {
        setEditedWorkflowAttribute('description', description);
    })

    const sidebarRef = useRef(null);

    useScrollToTop(sidebarRef, '.interface-interface-skeleton__sidebar');

    return (
        <div ref={sidebarRef}>
            <PersistentPanelBody
                className='edit-post-post-status'
                title={__('Summary', 'post-expirator')}
                initialOpen={true}
                disabled={isLoadingWorkflow}
            >
                <PanelRow className="editor-post-title__panel">
                    <TextControl
                        label={__('Title', 'post-expirator')}
                        value={workflow.title}
                        onChange={onChangeTitle}
                        disabled={isLoadingWorkflow}
                    />
                </PanelRow>

                <PanelRow className="editor-post-description__panel">
                    <TextareaControl
                        label={__('Description', 'post-expirator')}
                        value={workflow.description}
                        onChange={onChangeDescription}
                        disabled={isLoadingWorkflow}
                    />
                </PanelRow>

                <PanelRow className="editor-post-status__panel">
                    <WorkflowSwitchToDraftButton />
                    <WorkflowDeleteButton />
                </PanelRow>
            </PersistentPanelBody>
        </div>
    );
};

export default WorkflowSummary;
