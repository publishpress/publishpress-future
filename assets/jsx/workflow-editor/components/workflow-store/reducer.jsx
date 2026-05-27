import { MarkerType } from 'reactflow';
import {
    POST_TYPE,
    NODE_TYPE_PLACEHOLDER
} from '../../constants';
import { __ } from '@wordpress/i18n';
import { newTriggerPlaceholderNode, getNodeById } from '../../utils';

export const DEFAULT_STATE = {
    postType: POST_TYPE,
    isLoadingWorkflow: false,
    isCreatingWorkflow: false,
    isSavingWorkflow: false,
    isNewWorkflow: true,
    isDeletingWorkflow: false,
    isAutosavingWorkflow: false,
    isEditedWorkflowEmpty: true,
    isCurrentWorkflowPublished: false,
    workflow: {
        id: 0,
        title: '',
        description: '',
        flow: {
            nodes: [],
            edges: [],
            viewport: {
                x: 0,
                y: 0,
                zoom: 2,
            },
        },
        status: 'auto-draft',
        debugRayShowQueries: false,
        debugRayShowEmails: false,
        debugRayShowWordPressErrors: false,
        debugRayShowCurrentRunningStep: false,
    },
    editedWorkflowAttributes: {},
    initialViewport: {
        x: 0,
        y: 0,
        zoom: 2,
    },
    selectedNodes: [],
    selectedEdges: [],
    dataTypes: [],
    globalVariables: [],
    isFetchingTaxonomyTerms: false,
    taxonomyTerms: {},
    baseSlugCounts: {},
    nodeErrors: {},
    draggingFromHandle: {
        sourceId: null,
        handleId: null,
        handleType: null,
    },
    isConnectingNodes: false,
}

const loadWorkflowStart = (state, action) => {
    return {
        ...state,
        isLoadingWorkflow: true
    };
}

// Update the markerEnd for each edge
const normalizeMarkerEnd = (payload) => {
    return payload.map(edge => {
        if (edge.type !== 'genericEdge') {
            return edge;
        }

        return {
            ...edge,
            markerEnd: {
                type: MarkerType.ArrowClosed,
            },
        };
    });
}

const removeBrokenConnections = (nodes, edges) => {
    return edges.filter(edge => {
        const sourceNode = getNodeById(edge.source, nodes);
        const targetNode = getNodeById(edge.target, nodes);

        if (! sourceNode || ! targetNode) {
            return false;
        }

        return true;
    });
}

function _setInitialStateForGlobalVariables(state, workflow = {}) {
    state = setGlobalVariable(state, {
        payload: {
            name: 'site',
            label: __('Site', 'post-expirator'),
            type: 'site',
            runtimeOnly: true,
            description: __('The current site.', 'post-expirator'),
        }
    });

    state = setGlobalVariable(state, {
        payload: {
            name: 'workflow',
            label: __('Workflow', 'post-expirator'),
            type: 'workflow',
            runtimeOnly: false,
            description: __('The current workflow.', 'post-expirator'),
        }
    });

    state = setGlobalVariable(state, {
        payload: {
            name: 'user',
            label: __('Activating user', 'post-expirator'),
            type: 'user',
            runtimeOnly: true,
            description: __('The current user.', 'post-expirator'),
        }
    });

    state = setGlobalVariable(state, {
        payload: {
            name: 'trigger',
            label: __('Activating trigger', 'post-expirator'),
            type: 'node',
            runtimeOnly: true,
            description: __('The node that activated the workflow.', 'post-expirator'),
        }
    });

    state = setGlobalVariable(state, {
        payload: {
            name: 'engine_execution_id',
            label: __('Engine execution ID', 'post-expirator'),
            type: 'string',
            runtimeOnly: true,
            description: __('A unique identifier assigned each time the workflow engine runs. Useful for tracking specific workflow executions and debugging.', 'post-expirator'),
            priority: 35,
        }
    });

    return state;
}

const loadWorkflowSuccess = (state, action) => {
    const { payload } = action;

    let nodes = payload.flow?.nodes || [];
    let edges = payload.flow?.edges || [];
    const initialViewport = payload.flow?.viewport || DEFAULT_STATE.viewport;

    nodes.map(node => {
        const slug = node?.data?.slug;

        if (! slug) {
            return;
        }

        state = updateBaseSlugCounts(state, {payload: slug});
    });

    if (! nodes.length) {
        nodes = [newTriggerPlaceholderNode()];
    }

    edges = normalizeMarkerEnd(edges);
    edges = removeBrokenConnections(nodes, edges);

    state = _setInitialStateForGlobalVariables(state, payload);

    unselectAll(state);

    return {
        ...state,
        isLoadingWorkflow: false,
        workflow: {
            ...payload,
            flow: {
                ...payload.flow,
                nodes,
                edges,
            },
        },
        editedWorkflowAttributes: {},
        isNewWorkflow: payload.status === 'auto-draft',
        initialViewport,
        isEditedWorkflowEmpty: edges.length === 0 && nodes.length === 0,
        isCurrentWorkflowPublished: payload.status === 'publish',
    };
}

const loadWorkflowFailure = (state, action) => {
    return {
        ...state,
        isLoadingWorkflow: false,
    };
}

const createWorkflowStart = (state, action) => {
    return {
        ...state,
        isCreatingWorkflow: true,
        isLoadingWorkflow: true,
    };
}

const createWorkflowSuccess = (state, action) => {
    const { payload } = action;

    const nodes = [newTriggerPlaceholderNode()];

    state = _setInitialStateForGlobalVariables(state, {});

    return {
        ...state,
        isCreatingWorkflow: false,
        isLoadingWorkflow: false,
        workflow: {
            ...payload,
            flow: {
                ...payload.flow,
                nodes,
            },
        },
        editedWorkflowAttributes: {},
        isNewWorkflow: payload.status === 'auto-draft',
        isEditedWorkflowEmpty: state.workflow.flow.edges.length === 0 && state.workflow.flow.nodes.length === 0,
        isCurrentWorkflowPublished: payload.status === 'publish',
    };
}

const createWorkflowFailure = (state, action) => {
    return {
        ...state,
        isCreatingWorkflow: false,
        isLoadingWorkflow: false,
    };
}

const saveAsDraftStart = (state, action) => {
    return {
        ...state,
        isSavingWorkflow: true,
    };
}

const saveAsDraftSuccess = (state, action) => {
    const { payload } = action;

    return {
        ...state,
        isSavingWorkflow: false,
        workflow: payload,
        editedWorkflowAttributes: {},
        isNewWorkflow: payload.status === 'auto-draft',
        isEditedWorkflowEmpty: state.workflow.flow.edges.length === 0 && state.workflow.flow.nodes.length === 0,
        isCurrentWorkflowPublished: payload.status === 'publish',
    };
}

const saveAsDraftFailure = (state, action) => {
    return {
        ...state,
        isSavingWorkflow: false,
    };
}

const switchToDraftStart = (state, action) => {
    return {
        ...state,
        isSavingWorkflow: true,
    };
}

const switchToDraftSuccess = (state, action) => {
    const { payload } = action;

    const newWorkflow = {
        ...state.workflow,
        status: payload.status,
    };

    return {
        ...state,
        isSavingWorkflow: false,
        workflow: newWorkflow,
        isNewWorkflow: false,
        isEditedWorkflowEmpty: state.workflow.flow.edges.length === 0 && state.workflow.flow.nodes.length === 0,
        isCurrentWorkflowPublished: newWorkflow.status === 'publish',
        editedWorkflowAttributes: {},
    };
}

const switchToDraftFailure = (state, action) => {
    return {
        ...state,
        isSavingWorkflow: false,
    };
}

const saveAsCurrentStatusStart = (state, action) => {
    return {
        ...state,
        isSavingWorkflow: true,
    };
}

const saveAsCurrentStatusSuccess = (state, action) => {
    const { payload } = action;

    const newWorkflow = {
        ...state.workflow,
        ...payload,
    };

    return {
        ...state,
        isSavingWorkflow: false,
        workflow: newWorkflow,
        isNewWorkflow: false,
        isEditedWorkflowEmpty: state.workflow.flow.edges.length === 0 && state.workflow.flow.nodes.length === 0,
        isCurrentWorkflowPublished: state.workflow.status === 'publish',
        editedWorkflowAttributes: {},
    };
}

const saveAsCurrentStatusFailure = (state, action) => {
    return {
        ...state,
        isSavingWorkflow: false,
    };
}

const publishWorkflowStart = (state, action) => {
    return {
        ...state,
        isSavingWorkflow: true,
    };
}

const publishWorkflowSuccess = (state, action) => {
    const { payload } = action;

    const newWorkflow = {
        ...state.workflow,
        status: payload.status,
    };

    return {
        ...state,
        isSavingWorkflow: false,
        workflow: newWorkflow,
        isNewWorkflow: false,
        isEditedWorkflowEmpty: state.workflow.flow.edges.length === 0 && state.workflow.flow.nodes.length === 0,
        isCurrentWorkflowPublished: newWorkflow.status === 'publish',
        editedWorkflowAttributes: {},
    };
}

const publishWorkflowFailure = (state, action) => {
    return {
        ...state,
        isSavingWorkflow: false,
    };
}

const setEditedWorkflowAttribute = (state, action) => {
    const { key, value } = action.payload;

    return {
        ...state,
        workflow: {
            ...state.workflow,
            [key]: value,
        },
        editedWorkflowAttributes: {
            ...state.editedWorkflowAttributes,
            [key]: value,
        },
        isEditedWorkflowEmpty: Object.keys(state.editedWorkflowAttributes).length === 0,
    };
}

const setPostType = (state, action) => {
    const { payload } = action;

    return {
        ...state,
        postType: payload,
    };
}

const setNodes = (state, action) => {
    const { payload } = action;

    return {
        ...state,
        workflow: {
            ...state.workflow,
            flow: {
                ...state.workflow.flow,
                nodes: payload,
            },
        },
    };
}

const addNode = (state, action) => {
    const { payload } = action;

    const newNodes = [
        ...state.workflow.flow.nodes,
        payload,
    ];

    return {
        ...state,
        workflow: {
            ...state.workflow,
            flow: {
                ...state.workflow.flow,
                nodes: newNodes,
            },
        },
    };
}

const setEdges = (state, action) => {
    const { payload } = action;

    const updatedEdges = normalizeMarkerEnd(payload);

    return {
        ...state,
        workflow: {
            ...state.workflow,
            flow: {
                ...state.workflow.flow,
                edges: updatedEdges,
            },
        },
    };
}

const setInitialViewport = (state, action) => {
    const { payload } = action;

    return {
        ...state,
        initialViewport: payload,
    };
}

const setSelectedNodes = (state, action) => {
    const { payload } = action;

    return {
        ...state,
        selectedNodes: payload,
    };
}

const setSelectedEdges = (state, action) => {
    const { payload } = action;

    return {
        ...state,
        selectedEdges: payload,
    };
}

const unselectAll = (state, action) => {
    setTimeout(() => jQuery('.react-flow__pane').trigger('click'), 200);

    return {
        ...state,
        selectedNodes: [],
        selectedEdges: [],
    };
}

const deleteWorkflowStart = (state, action) => {
    return {
        ...state,
        isDeletingWorkflow: true,
    };
}

const deleteWorkflowSuccess = (state, action) => {
    return {
        ...state,
        isDeletingWorkflow: false,
    };
}

const deleteWorkflowFailure = (state, action) => {
    return {
        ...state,
        isDeletingWorkflow: false,
    };
}

const updateNode = (state, action) => {
    const { payload } = action;

    // Update the settings of the node with the given ID
    const updatedNodes = state.workflow.flow.nodes.map(node => {
        if (node.id === payload.id) {
            return {
                ...node,
                ...payload,
                data: {
                    ...node.data,
                    ...payload.data,
                    settings: {
                        ...node.data.settings,
                        ...payload.data.settings,
                    },
                },
            };
        }

        return node;
    });

    const newWorkflow = {
        ...state.workflow,
        flow: {
            ...state.workflow.flow,
            nodes: updatedNodes,
        },
    };

    return {
        ...state,
        workflow: newWorkflow,
        editedWorkflowAttributes: {
            ...state.editedWorkflowAttributes,
            flow: {
                ...state.editedWorkflowAttributes.flow,
                nodes: updatedNodes,
            },
        },
    };
}

const setDataTypes = (state, action) => {
    const { payload } = action;

    return {
        ...state,
        dataTypes: payload,
    };
}

const addDataType = (state, action) => {
    const { payload } = action.payload;

    return {
        ...state,
        dataTypes: [
            ...state.dataTypes,
            payload,
        ],
    };
}

const setGlobalVariable = (state, action) => {
    const { name, label, type, value, runtimeOnly, description, priority } = action.payload;

    const globalVariables = {
        ...state.globalVariables
    };

    globalVariables[name] = {
        name,
        type,
        value,
        label,
        runtimeOnly,
        description,
        priority,
    };

    return {
        ...state,
        globalVariables,
    };
}

const fetchTaxonomyTermsStart = (state, action) => {
    return {
        ...state,
        isFetchingTaxonomyTerms: true,
    };
}

const fetchTaxonomyTermsSuccess = (state, action) => {
    const { taxonomy, result } = action.payload;

    const terms = result?.terms?.map(term => {
        return {
            value: term.id,
            label: term.name,
        };
    }) || [];

    const newTaxonomyTerms = {
        ...state.taxonomyTerms,
        [taxonomy]: terms,
    };

    return {
        ...state,
        isFetchingTaxonomyTerms: false,
        taxonomyTerms: newTaxonomyTerms,
    };
}

const fetchTaxonomyTermsFailure = (state, action) => {
    return {
        ...state,
        isFetchingTaxonomyTerms: false,
    };
}

const incrementBaseSlugCounts = (state, action) => {
    const { payload } = action;

    const newBaseSlugCounts = {
        ...state.baseSlugCounts,
        [payload]: (state.baseSlugCounts[payload] || 0) + 1,
    };

    return {
        ...state,
        baseSlugCounts: newBaseSlugCounts,
    };
}


const extractSlugParts = (slug) => {
    if (! slug) {
        return {};
    }

    // The payload is a string with the format "baseSlug<count>"
    const matches = slug.match(/([a-zA-Z0-9]+)(\d+)$/);

    if (! matches) {
        return {};
    }

    return {
        baseSlug: matches[1],
        count: parseInt(matches[2]),
    };
}

const calculateBaseSlugCount = (state, slug) => {
    const slugParts = extractSlugParts(slug);
    const currentBaseSlugCount = state.baseSlugCounts[slugParts?.baseSlug] || 0;

    const baseSlugCount = slugParts?.count || 0;

    if (isNaN(baseSlugCount)) {
        return currentBaseSlugCount;
    }

    if (baseSlugCount > currentBaseSlugCount) {
        return baseSlugCount;
    }

    return currentBaseSlugCount;
}

const updateBaseSlugCounts = (state, action) => {
    const { payload } = action;

    const slugParts = extractSlugParts(payload);
    const baseSlugCount = calculateBaseSlugCount(state, payload);

    const baseSlug = slugParts?.baseSlug;

    if (! baseSlug) {
        return state;
    }

    if (! baseSlugCount) {
        return state;
    }

    return {
        ...state,
        baseSlugCounts: {
            ...state.baseSlugCounts,
            [baseSlug]: baseSlugCount,
        },
    };
}

const addNodeError = (state, action) => {
    const { payload } = action;

    const theNodeErrors = state.nodeErrors[payload.nodeId] || {};

    theNodeErrors[payload.error] = {
        error: payload.error,
        message: payload.message,
        details: payload?.details,
    };

    const nodeErrors = {
        ...state.nodeErrors,
        [payload.nodeId]: theNodeErrors,
    };

    return {
        ...state,
        nodeErrors: nodeErrors,
    };
}

const removeNodeError = (state, action) => {
    const { payload } = action;

    const newErrors = {
        ...state.nodeErrors,
    };

    if (! newErrors[payload.nodeId]) {
        return state;
    }

    delete newErrors[payload.nodeId][payload.error];

    return {
        ...state,
        nodeErrors: newErrors,
    };
}

const resetNodeErrors = (state, action) => {
    const { payload } = action;

    const newErrors = {
        ...state.nodeErrors,
    };

    delete newErrors[payload];

    return {
        ...state,
        nodeErrors: newErrors,
    };
}

const removeNode = (state, action) => {
    const { payload } = action;

    // Remove the edges that are connected to the node
    const newEdges = state.workflow.flow.edges.filter(edge => edge.source !== payload && edge.target !== payload);
    const newNodes = state.workflow.flow.nodes.filter(node => node.id !== payload);

    return {
        ...state,
        workflow: {
            ...state.workflow,
            flow: {
                ...state.workflow.flow,
                nodes: newNodes,
                edges: newEdges,
            },
        },
        selectedNodes: [],
        selectedEdges: [],
    };
}

const removeEdge = (state, action) => {
    const { payload } = action;

    const newEdges = state.workflow.flow.edges.filter(edge => edge.id !== payload);

    return {
        ...state,
        workflow: {
            ...state.workflow,
            flow: {
                ...state.workflow.flow,
                edges: newEdges,
            },
        },
        selectedNodes: [],
        selectedEdges: [],
    };
}

const removePlaceholderNodes = (state, action) => {
    const newNodes = state.workflow.flow.nodes.filter(node => node.data.elementaryType !== NODE_TYPE_PLACEHOLDER);

    return {
        ...state,
        workflow: {
            ...state.workflow,
            flow: {
                ...state.workflow.flow,
                nodes: newNodes,
            },
        },
    };
}

const setDraggingFromHandle = (state, action) => {
    const { sourceId, handleId, handleType } = action.payload;

    return {
        ...state,
        draggingFromHandle: {
            sourceId,
            handleId,
            handleType,
        },
    };
}

const setIsConnectingNodes = (state, action) => {
    return {
        ...state,
        isConnectingNodes: action.payload,
    };
}

export const reducer = (state = DEFAULT_STATE, action) => {
    switch (action.type) {
        case 'CREATE_WORKFLOW_START':
            return createWorkflowStart(state, action);
        case 'CREATE_WORKFLOW_SUCCESS':
            return createWorkflowSuccess(state, action);
        case 'CREATE_WORKFLOW_FAILURE':
            return createWorkflowFailure(state, action);
        case 'LOAD_WORKFLOW_START':
            return loadWorkflowStart(state, action);
        case 'LOAD_WORKFLOW_SUCCESS':
            return loadWorkflowSuccess(state, action);
        case 'LOAD_WORKFLOW_FAILURE':
            return loadWorkflowFailure(state, action);
        case 'SAVE_AS_DRAFT_START':
            return saveAsDraftStart(state, action);
        case 'SAVE_AS_DRAFT_SUCCESS':
            return saveAsDraftSuccess(state, action);
        case 'SAVE_AS_DRAFT_FAILURE':
            return saveAsDraftFailure(state, action);
        case 'SWITCH_TO_DRAFT_START':
            return switchToDraftStart(state, action);
        case 'SWITCH_TO_DRAFT_SUCCESS':
            return switchToDraftSuccess(state, action);
        case 'SWITCH_TO_DRAFT_FAILURE':
            return switchToDraftFailure(state, action);
        case 'SAVE_AS_CURRENT_STATUS_START':
            return saveAsCurrentStatusStart(state, action);
        case 'SAVE_AS_CURRENT_STATUS_SUCCESS':
            return saveAsCurrentStatusSuccess(state, action);
        case 'SAVE_AS_CURRENT_STATUS_FAILURE':
            return saveAsCurrentStatusFailure(state, action);
        case 'PUBLISH_WORKFLOW_START':
            return publishWorkflowStart(state, action);
        case 'PUBLISH_WORKFLOW_SUCCESS':
            return publishWorkflowSuccess(state, action);
        case 'PUBLISH_WORKFLOW_FAILURE':
            return publishWorkflowFailure(state, action);
        case 'SET_EDITED_WORKFLOW_ATTRIBUTE':
            return setEditedWorkflowAttribute(state, action);
        case 'SET_POST_TYPE':
            return setPostType(state, action);
        case 'SET_NODES':
            return setNodes(state, action);
        case 'ADD_NODE':
            return addNode(state, action);
        case 'SET_EDGES':
            return setEdges(state, action);
        case 'ADD_EDGE':
            return addEdge(state, action);
        case 'SET_INITIAL_VIEWPORT':
            return setInitialViewport(state, action);
        case 'SET_SELECTED_NODES':
            return setSelectedNodes(state, action);
        case 'SET_SELECTED_EDGES':
            return setSelectedEdges(state, action);
        case 'UNSELECT_ALL':
            return unselectAll(state, action);
        case 'DELETE_WORKFLOW_START':
            return deleteWorkflowStart(state, action);
        case 'DELETE_WORKFLOW_SUCCESS':
            return deleteWorkflowSuccess(state, action);
        case 'DELETE_WORKFLOW_FAILURE':
            return deleteWorkflowFailure(state, action);
        case 'UPDATE_NODE':
            return updateNode(state, action);
        case 'SET_DATA_TYPES':
            return setDataTypes(state, action);
        case 'ADD_DATA_TYPE':
            return addDataType(state, action);
        case 'SET_GLOBAL_VARIABLE':
            return setGlobalVariable(state, action);
        case 'FETCH_TAXONOMY_TERMS_START':
            return fetchTaxonomyTermsStart(state, action);
        case 'FETCH_TAXONOMY_TERMS_SUCCESS':
            return fetchTaxonomyTermsSuccess(state, action);
        case 'FETCH_TAXONOMY_TERMS_FAILURE':
            return fetchTaxonomyTermsFailure(state, action);
        case 'INCREMENT_BASE_SLUG_COUNTS':
            return incrementBaseSlugCounts(state, action);
        case 'UPDATE_BASE_SLUG_COUNTS':
            return updateBaseSlugCounts(state, action);
        case 'ADD_NODE_ERROR':
            return addNodeError(state, action);
        case 'REMOVE_NODE_ERROR':
            return removeNodeError(state, action);
        case 'RESET_NODE_ERRORS':
            return resetNodeErrors(state, action);
        case 'REMOVE_NODE':
            return removeNode(state, action);
        case 'REMOVE_EDGE':
            return removeEdge(state, action);
        case 'REMOVE_PLACEHOLDER_NODES':
            return removePlaceholderNodes(state, action);
        case 'SET_DRAGGING_FROM_HANDLE':
            return setDraggingFromHandle(state, action);
        case 'SET_IS_CONNECTING_NODES':
            return setIsConnectingNodes(state, action);
    }

    return state;
}

function addWorkflowIdToUrl(workflowId) {
    window.history.pushState({}, '', `?page=future_workflow_editor&workflow=${parseInt(workflowId)}`);
}

export default reducer;
