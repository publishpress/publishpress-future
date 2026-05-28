import { createRoot } from '@wordpress/element';
import { Fieldset } from '../fieldset';
import { __ } from '@wordpress/i18n';

import './css/style.css';

// We create a copy of the WP inline edit post function
const wpInlineEditPro = window.inlineEditPost.edit;
const wpInlineEditProRevert = window.inlineEditPost.revert;
const delayToUnmountAfterSaving = 1000;

const getPostIdFromButton = (id) => {
    // If id is a string or a number, return it directly
    if (typeof id === 'string' || typeof id === 'number') {
        return id;
    }

    // Otherwise, assume it's an HTML element and extract the post ID
    const trElement = id.closest('tr');
    const trId = trElement.id;
    const postId = trId.split('-')[1];

    return postId;
}

/**
 * We override the function with our own code so we can detect when
 * the inline edit row is displayed to recreate the React component.
 */
window.inlineEditPost.edit = function (button, id) {
    // Call the original WP edit function.
    wpInlineEditPro.apply(this, arguments);

    const postId = getPostIdFromButton(button);

    const container = document.getElementById("post-expirator-quick-edit");

    if (! container) {
        return;
    }

    const root = createRoot(container);

    const saveButton = document.querySelector('.inline-edit-save .save');
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
    const actionNonce = window.futureWorkflowManualSelection.actionNonce;

    const component = (
        <Fieldset
            context='quick-edit'
            postId={postId}
            apiUrl={apiUrl}
            nonce={nonce}
            workflowNonce={workflowNonce}
            actionNonce={actionNonce}
            wrapper={({workflowsWithManualTrigger, children}) => {
                if (workflowsWithManualTrigger.length === 0) {
                    return children;
                }

                return (
                    <fieldset class="inline-edit-col-left post-expirator-quick-edit-fieldset">
                        <div class="future-quick-edit-fieldset">
                            <div class="title">{__('Action Workflows', 'post-expirator')}</div>
                            <div class="inline-edit-col">
                                <div class="inline-edit-group">
                                    {children}
                                </div>
                            </div>
                        </div>
                    </fieldset>

                );
            }}
        />
    );

    root.render(component);

    window.inlineEditPost.revert = function () {
        root.unmount();

        // Call the original WP revert function.
        wpInlineEditProRevert.apply(this, arguments);
    };
};
