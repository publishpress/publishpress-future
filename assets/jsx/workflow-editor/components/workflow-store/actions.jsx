import { dispatch, select } from '@wordpress/data';
import { apiFetch } from '@wordpress/data-controls';
import { STORE_NAME } from './name';
import { __ } from '@publishpress/i18n';

const { apiUrl, nonce, workflowNonce } = window.futureWorkflowEditor;

const editableAttributes = ['title', 'description', 'flow', 'status', 'debugRayShowQueries', 'debugRayShowEmails', 'debugRayShowWordPressErrors', 'debugRayShowCurrentRunningStep'];

export function* setupEditor(workflowId) {
    yield {type: 'LOAD_WORKFLOW_START'};
    workflowId = parseInt(workflowId, 10);

    if (workflowId == 0) {
        yield {type: 'CREATE_WORKFLOW_START'};

        // No workflow ID, we have a new workflow. Call the API to create a new workflow and ge the ID.
        try {
            const workflow = yield apiFetch({
                path: `${apiUrl}/workflows`,
                method: 'POST',
                headers: {
                    'X-WP-Nonce': nonce,
                    'X-PP-Workflow-Nonce': workflowNonce,
                },
            });

            yield {type: 'CREATE_WORKFLOW_SUCCESS', payload: workflow};
        } catch (error) {
            dispatch('core/notices').createErrorNotice(
                __('Unable to create a new workflow. Please try again.', 'post-expirator')
            );
            // TODO: Show error message
            yield {type: 'CREATE_WORKFLOW_FAILURE'};
        }
    }

    if (workflowId > 0) {
        try {
            const workflow = yield apiFetch({
                path: `${apiUrl}/workflows/${workflowId}`,
                headers: {
                    'X-WP-Nonce': nonce,
                    'X-PP-Workflow-Nonce': workflowNonce,
                },
            });

            yield {type: 'LOAD_WORKFLOW_SUCCESS', payload: workflow};
        } catch (error) {
            dispatch('core/notices').createErrorNotice(
                __('Unable to load the workflow. Please try again.', 'post-expirator')
            );
            yield {type: 'LOAD_WORKFLOW_FAILURE'};
        }
    }
};

function addWorkflowIdToUrl(workflowId) {
    window.history.pushState({}, '', `?page=future_workflow_editor&workflow=${parseInt(workflowId)}`);
}

// FIXME: This is not working as expected. The state we get from the store is not updated if the
// inspector is open or was not closed after the changes.
export function* saveAsDraft() {
    yield {type: 'SAVE_AS_DRAFT_START'};

    try {
        const wasNewWorkflow = yield select(STORE_NAME).isNewWorkflow();
        const editedWorkflow = yield select(STORE_NAME).getWorkflow();

        const workflowToSave = {
            ...editedWorkflow,
            status: 'draft'
        };

        const newWorkflow = yield apiFetch({
            path: `${apiUrl}/workflows/${parseInt(editedWorkflow.id)}`,
            method: 'PUT',
            headers: {
                'X-WP-Nonce': nonce,
                'X-PP-Workflow-Nonce': workflowNonce,
            },
            body: JSON.stringify(workflowToSave),
        });

        // Add the workflow id to the url, keeping current state in the history
        if (wasNewWorkflow) {
            addWorkflowIdToUrl(newWorkflow.id);
        }

        yield {type: 'SAVE_AS_DRAFT_SUCCESS', payload: newWorkflow};

        dispatch('core/notices').createSuccessNotice(
            __('Workflow saved as draft.', 'post-expirator'),
            {
                type: 'snackbar',
                isDismissible: true,
            }
        );
    } catch (error) {
        dispatch('core/notices').createErrorNotice(
            __('Unable to save workflow. Please, try again.', 'post-expirator')
        );

        yield {type: 'SAVE_AS_DRAFT_FAILURE'};
    }
}

export function* saveAsCurrentStatus() {
    const editedWorkflow = yield select(STORE_NAME).getWorkflow();

    if (editedWorkflow.status === 'auto-draft') {
        yield saveAsDraft();
        return;
    }

    yield {type: 'SAVE_AS_CURRENT_STATUS_START'};

    try {
        const wasNewWorkflow = yield select(STORE_NAME).isNewWorkflow();
        const editedWorkflow = yield select(STORE_NAME).getWorkflow();

        const workflowToSave = {
            ...editedWorkflow,
        };

        const newWorkflow = yield apiFetch({
            path: `${apiUrl}/workflows/${parseInt(editedWorkflow.id)}`,
            method: 'PUT',
            headers: {
                'X-WP-Nonce': nonce,
                'X-PP-Workflow-Nonce': workflowNonce,
            },
            body: JSON.stringify(workflowToSave),
        });

        // Add the workflow id to the url, keeping current state in the history
        if (wasNewWorkflow) {
            addWorkflowIdToUrl(newWorkflow.id);
        }

        yield {type: 'SAVE_AS_CURRENT_STATUS_SUCCESS', payload: newWorkflow};

        dispatch('core/notices').createSuccessNotice(
            __('Workflow saved.', 'post-expirator'),
            {
                type: 'snackbar',
                isDismissible: true,
            }
        );
    } catch (error) {
        dispatch('core/notices').createErrorNotice(
            __('Unable to save workflow. Please, try again.', 'post-expirator')
        );

        yield {type: 'SAVE_AS_CURRENT_STATUS_FAILURE'};
    }
}

export function* publishWorkflow() {
    yield {type: 'PUBLISH_WORKFLOW_START'};

    try {
        const wasNewWorkflow = yield select(STORE_NAME).isNewWorkflow();
        const editedWorkflow = yield select(STORE_NAME).getWorkflow();

        const workflowToSave = {
            ...editedWorkflow,
            status: 'publish',
        };

        const newWorkflow = yield apiFetch({
            path: `${apiUrl}/workflows/${parseInt(editedWorkflow.id)}`,
            method: 'PUT',
            headers: {
                'X-WP-Nonce': nonce,
                'X-PP-Workflow-Nonce': workflowNonce,
            },
            body: JSON.stringify(workflowToSave),
        });

        // Add the workflow id to the url, keeping current state in the history
        if (wasNewWorkflow) {
            addWorkflowIdToUrl(newWorkflow.id);
        }

        yield {type: 'PUBLISH_WORKFLOW_SUCCESS', payload: newWorkflow};

        dispatch('core/notices').createSuccessNotice(
            __('Workflow published.', 'post-expirator'),
            {
                type: 'snackbar',
                isDismissible: true,
            }
        );
    } catch (error) {
        dispatch('core/notices').createErrorNotice(
            __('Unable to publish the workflow. Please, try again.', 'post-expirator')
        );

        yield {type: 'PUBLISH_WORKFLOW_FAILURE'};
    }
}

export function* switchToDraft() {
    yield {type: 'SWITCH_TO_DRAFT_START'};

    try {
        const wasNewWorkflow = yield select(STORE_NAME).isNewWorkflow();
        const editedWorkflow = yield select(STORE_NAME).getWorkflow();

        const workflowToSave = {
            ...editedWorkflow,
            status: 'draft',
        };

        const newWorkflow = yield apiFetch({
            path: `${apiUrl}/workflows/${parseInt(editedWorkflow.id)}`,
            method: 'PUT',
            headers: {
                'X-WP-Nonce': nonce,
                'X-PP-Workflow-Nonce': workflowNonce,
            },
            body: JSON.stringify(workflowToSave),
        });

        // Add the workflow id to the url, keeping current state in the history
        if (wasNewWorkflow) {
            addWorkflowIdToUrl(newWorkflow.id);
        }

        yield {type: 'SWITCH_TO_DRAFT_SUCCESS', payload: newWorkflow};

        dispatch('core/notices').createSuccessNotice(
            __('Workflow switched to draft.', 'post-expirator'),
            {
                type: 'snackbar',
                isDismissible: true,
            }
        );
    } catch (error) {
        dispatch('core/notices').createErrorNotice(
            __('Unable to switch workflow to draft. Please, try again.', 'post-expirator')
        );

        yield {type: 'SWITCH_TO_DRAFT_FAILURE'};
    }
}

export const setFlow = (flow) => {
    return {
        type: 'SET_FLOW',
        payload: flow,
    };
};

export const setPostType = (postType) => {
    return {
        type: 'SET_POST_TYPE',
        payload: postType,
    };
};

export const setNodes = (nodes) => {
    return {
        type: 'SET_NODES',
        payload: nodes,
    };
};

export const setEdges = (edges) => {
    return {
        type: 'SET_EDGES',
        payload: edges,
    };
};

export const setInitialViewport = (viewport) => {
    return {
        type: 'SET_INITIAL_VIEWPORT',
        payload: viewport,
    };
}

export const setSelectedNodes = (nodes) => {
    return {
        type: 'SET_SELECTED_NODES',
        payload: nodes,
    };
};

export const unselectAll = () => {
    return {
        type: 'UNSELECT_ALL',
    };
};

export const setSelectedEdges = (edges) => {
    return {
        type: 'SET_SELECTED_EDGES',
        payload: edges,
    };
};

export const setEditedWorkflowAttribute = (key, value) => {
    if (!editableAttributes.includes(key)) {
        throw new Error(`The workflow attribute "${key}" is not editable.`);
    }

    return {
        type: 'SET_EDITED_WORKFLOW_ATTRIBUTE',
        payload: { key, value },
    };
};

export function* deleteWorkflow () {
    yield {type: 'DELETE_WORKFLOW_START'};

    const editedWorkflow = yield select(STORE_NAME).getWorkflow();

    try {
        const newWorkflow = yield apiFetch({
            path: `${apiUrl}/workflows/${parseInt(editedWorkflow.id)}`,
            method: 'DELETE',
            headers: {
                'X-WP-Nonce': nonce,
                'X-PP-Workflow-Nonce': workflowNonce,
            },
        });

        yield {type: 'DELETE_WORKFLOW_SUCCESS', payload: newWorkflow};

        dispatch('core/notices').createSuccessNotice(
            __('Workflow deleted. Redirecting...', 'post-expirator'),
            {
                type: 'snackbar',
                isDismissible: true,
            }
        );

        // Redirect to the workflow list
        window.location.href = `edit.php?post_type=ppfuture_workflow`;
    } catch (error) {
        dispatch('core/notices').createErrorNotice(
            __('Unable to delete the workflow. Please, try again.', 'post-expirator')
        );

        yield {type: 'DELETE_WORKFLOW_FAILURE'};
    }
}

export function updateNode(node) {
    return {
        type: 'UPDATE_NODE',
        payload: node,
    };
}

export function setDataTypes(dataTypes) {
    return {
        type: 'SET_DATA_TYPES',
        payload: dataTypes,
    };
}

export function addDataType(dataType) {
    return {
        type: 'ADD_DATA_TYPE',
        payload: dataType,
    };
}

export function setGlobalVariable(globalVariable) {
    return {
        type: 'SET_GLOBAL_VARIABLE',
        payload: globalVariable,
    };
}

export function* fetchTaxonomyTerms(taxonomy) {
    yield {type: 'FETCH_TAXONOMY_TERMS_START'};

    try {
        const result = yield apiFetch({
            path: `${apiUrl}/terms/${taxonomy}`,
            headers: {
                'X-WP-Nonce': nonce,
                'X-PP-Workflow-Nonce': workflowNonce,
            },
        });

        yield {type: 'FETCH_TAXONOMY_TERMS_SUCCESS', payload: {taxonomy, result}};
    } catch (error) {
        // TODO: Show error message
        yield {type: 'FETCH_TAXONOMY_TERMS_FAILURE'};
    }
}

export function incrementBaseSlugCounts(baseSlug) {
    return {
        type: 'INCREMENT_BASE_SLUG_COUNTS',
        payload: baseSlug,
    };
}

export function updateBaseSlugCounts(nodeSlug) {
    return {
        type: 'UPDATE_BASE_SLUG_COUNTS',
        payload: nodeSlug,
    };
}

export function addNodeError(nodeId, error, message, details = '') {
    return {
        type: 'ADD_NODE_ERROR',
        payload: {nodeId, error, message, details},
    };
}

export function removeNodeError(nodeId, error) {
    return {
        type: 'REMOVE_NODE_ERROR',
        payload: {nodeId, error}
    };
}

export function resetNodeErrors(nodeId) {
    return {
        type: 'RESET_NODE_ERRORS',
        payload: nodeId,
    };
}

export function removeNode(nodeId) {
    return {
        type: 'REMOVE_NODE',
        payload: nodeId,
    };
}

export function removeEdge(edgeId) {
    return {
        type: 'REMOVE_EDGE',
        payload: edgeId,
    };
}

export function addNode(node) {
    return {
        type: 'ADD_NODE',
        payload: node,
    };
}

export function removePlaceholderNodes() {
    return {
        type: 'REMOVE_PLACEHOLDER_NODES',
    };
}

export function setDraggingFromHandle({sourceId, handleId, handleType}) {
    return {
        type: 'SET_DRAGGING_FROM_HANDLE',
        payload: {sourceId, handleId, handleType},
    };
}

export function setIsConnectingNodes(isConnecting) {
    return {
        type: 'SET_IS_CONNECTING_NODES',
        payload: isConnecting,
    };
}
