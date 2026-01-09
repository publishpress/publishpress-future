import { useDispatch, useSelect } from '@wordpress/data';
import { store } from '../store';
import { Fieldset } from '../fieldset';
import { __ } from '@publishpress/i18n';

import { registerPlugin } from '@wordpress/plugins';
import { useEffect } from 'react';

import './css/style.css';

function BlockEditorWorkflowManualTrigger() {
    const { PluginDocumentSettingPanel } = wp.editPost;

    const {
        workflowsEnabledForPost
    } = useSelect((select) => {
        return {
            workflowsEnabledForPost: select(store).getWorkflowsEnabledForPost(),
        };
    });

    const { editPost } = useDispatch('core/editor');

    useEffect(() => {
        editPostAttribute(workflowsEnabledForPost);
    }, [workflowsEnabledForPost]);

    const editPostAttribute = (enabledWorkflows) => {
        const attribute = {
            publishpress_future_workflow_manual_trigger: {
                enabledWorkflows: [],
            }
        };

        attribute.publishpress_future_workflow_manual_trigger = {enabledWorkflows};

        editPost(attribute);
    }

    // Load the workflow settings for the post
    const apiUrl = window.futureWorkflowManualSelection.apiUrl;
    const nonce = window.futureWorkflowManualSelection.nonce;
    const workflowNonce = window.futureWorkflowManualSelection.workflowNonce;
    const postId = window.futureWorkflowManualSelection.postId;

    return (
        <PluginDocumentSettingPanel
            name={'publishpress-future-workflow-manual-trigger'}
            title={__('Action Workflows', 'post-expirator')}
            initialOpen={true}
            className={'future-workflow-manual-trigger'}
        >
            <Fieldset
                context='block-editor'
                postId={postId}
                apiUrl={apiUrl}
                nonce={nonce}
                workflowNonce={workflowNonce}
            />
        </PluginDocumentSettingPanel>
    );
}

registerPlugin('publishpress-future-workflow-manual-trigger', {
    render: BlockEditorWorkflowManualTrigger
});
