import { PanelRow } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { store as workflowStore } from '../workflow-store';
import { useSelect, useDispatch } from '@wordpress/data';
import { useCallback } from '@wordpress/element';
import PersistentPanelBody from '../persistent-panel-body';
import { ToggleControl } from '@wordpress/components';

export const WorkflowDebugRayPanel = () => {
    const {
        showQueries,
        showEmails,
        showWordPressErrors,
        showCurrentRunningStep,
        isLoadingWorkflow,
    } = useSelect((select) => {
        return {
            showQueries: select(workflowStore).getEditedWorkflowAttribute('debugRayShowQueries'),
            showEmails: select(workflowStore).getEditedWorkflowAttribute('debugRayShowEmails'),
            showWordPressErrors: select(workflowStore).getEditedWorkflowAttribute('debugRayShowWordPressErrors'),
            showCurrentRunningStep: select(workflowStore).getEditedWorkflowAttribute('debugRayShowCurrentRunningStep'),
            isLoadingWorkflow: select(workflowStore).isLoadingWorkflow(),
        }
    });

    const {
        setEditedWorkflowAttribute,
    } = useDispatch(workflowStore);

    const onChangeShowQueries = useCallback((selected) => {
        setEditedWorkflowAttribute('debugRayShowQueries', selected);
    })

    const onChangeShowEmails = useCallback((selected) => {
        setEditedWorkflowAttribute('debugRayShowEmails', selected);
    })

    const onChangeShowWordPressErrors = useCallback((selected) => {
        setEditedWorkflowAttribute('debugRayShowWordPressErrors', selected);
    })

    const onChangeShowCurrentRunningStep = useCallback((selected) => {
        setEditedWorkflowAttribute('debugRayShowCurrentRunningStep', selected);
    })

    return (
        <div>
            <PersistentPanelBody
                className='edit-post-post-status workflow-editor-dev-panel'
                title={__('Ray Debug', 'post-expirator')}
                initialOpen={true}
                disabled={isLoadingWorkflow}
            >
                <PanelRow>
                    <p>{__('Enable or disable the different types of debug information that is shown in Ray when this workflow is activated.', 'post-expirator')}</p>
                </PanelRow>
                <PanelRow>
                    <ToggleControl
                        label={__('Show Queries', 'post-expirator')}
                        checked={showQueries}
                        onChange={onChangeShowQueries}
                    />
                </PanelRow>
                <PanelRow>
                    <ToggleControl
                        label={__('Show Emails', 'post-expirator')}
                        checked={showEmails}
                        onChange={onChangeShowEmails}
                    />
                </PanelRow>
                <PanelRow>
                    <ToggleControl
                        label={__('Show WordPress Errors', 'post-expirator')}
                        checked={showWordPressErrors}
                        onChange={onChangeShowWordPressErrors}
                    />
                </PanelRow>
                <PanelRow>
                    <ToggleControl
                        label={__('Show current running step', 'post-expirator')}
                        checked={showCurrentRunningStep}
                        onChange={onChangeShowCurrentRunningStep}
                    />
                </PanelRow>
            </PersistentPanelBody>
        </div>
    );
};

export default WorkflowDebugRayPanel;
