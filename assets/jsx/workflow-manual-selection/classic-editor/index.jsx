import { select, dispatch } from '@wordpress/data';
import { createRoot } from 'react-dom/client';
import { store } from '../store';
import { Fieldset } from '../fieldset';
import apiFetch from '@wordpress/api-fetch';

import './css/style.css';

const container = document.getElementById("post-expirator-classic-editor");

if (container) {
    const root = createRoot(container);

    const saveButton = document.querySelector('.inline-edit-save .save');
    const delayToUnmountAfterSaving = 1000;

    if (saveButton) {
        saveButton.onclick = function() {
            setTimeout(() => {
                root.unmount();
            }, delayToUnmountAfterSaving);
        };
    }

    // Load the workflow settings for the post
    const apiUrl = window.futureWorkflowManualSelection.apiUrl;
    const nonce = window.futureWorkflowManualSelection.nonce;
    const workflowNonce = window.futureWorkflowManualSelection.workflowNonce;
    const postId = window.futureWorkflowManualSelection.postId;

    dispatch(store).setWorkflowsWithManualTrigger([]);
    dispatch(store).setWorkflowsEnabledForPost([]);

    apiFetch({
        url: `${apiUrl}/posts/workflow-settings/${postId}`,
        headers: {
            'X-WP-Nonce': nonce,
            'X-PP-Workflow-Nonce': workflowNonce,
        },
    }).then((response) => {
        dispatch(store).setWorkflowsWithManualTrigger(response.workflowsWithManualTrigger);
        dispatch(store).setWorkflowsEnabledForPost(response.manuallyEnabledWorkflows);
    });

    const component = (
        <Fieldset
            context='classic-editor'
            postId={postId}
            apiUrl={apiUrl}
            nonce={nonce}
            workflowNonce={workflowNonce}
        />
    );

    root.render(component);
}
