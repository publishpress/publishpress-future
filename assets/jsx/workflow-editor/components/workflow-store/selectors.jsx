import { getNodeById as getNodeByIdUtils, getNodeOutgoers, getNodeOutputSchema, getNodeType } from "../../utils";

export const getPostType = (state) => {
    return state.postType;
};

export const getNodes = (state) => {
    return state.workflow.flow.nodes;
};

export const getEdges = (state) => {
    return state.workflow.flow.edges;
};

export const getNodeById = (state, id) => {
    return getNodeByIdUtils(id, state.workflow.flow.nodes);
};

export const getEdgeById = (state, id) => {
    return state.workflow.flow.edges.find(edge => edge.id === id);
};

export const getSelectedNodes = (state) => {
    return state.selectedNodes;
};

export const getSelectedEdges = (state) => {
    return state.selectedEdges;
};

export const hasSelectedNodes = (state) => {
    return state.selectedNodes.length > 0;
};

export const hasSelectedEdges = (state) => {
    return state.selectedEdges.length > 0;
};

export const getWorkflowStatus = (state) => {
    return state.workflow.status;
}

export const getWorkflow = (state) => {
    return state.workflow;
};

export const getEditedWorkflowAttributes = (state) => {
    return state.editedWorkflowAttributes;
};

export const getEditedWorkflowAttribute = (state, key) => {
    const attributes = state.workflow;

    if (!attributes.hasOwnProperty(key)) {
        return state.workflow[key];
    }

    return state.workflow[key];
};

export const isLoadingWorkflow = (state) => {
    return !! state.isLoadingWorkflow;
}

export const isCreatingWorkflow = (state) => {
    return !! state.isCreatingWorkflow;
}

export const isEditedWorkflowDirty = (state) => {
    return Object.keys(state.editedWorkflowAttributes).length > 0;
}

export const isNewWorkflow = (state) => {
    return !! state.isNewWorkflow;
}

export const getInitialViewport = (state) => {
    return state.initialViewport;
}

export const isSavingWorkflow = (state) => {
    return !! state.isSavingWorkflow;
}

export const isPublishedWorkflow = (state) => {
    return state.workflow.status === 'publish';
}

export const isDeletingWorkflow = (state) => {
    return !! state.isDeletingWorkflow;
}

export const isAutosavingWorkflow = (state) => {
    return !! state.isAutosavingWorkflow;
}

export const isEditedWorkflowSaveable = (state) => {
    let title;

    if (state.editedWorkflowAttributes.hasOwnProperty('title')) {
        title = state.editedWorkflowAttributes.title;
    } else {
        title = state.workflow.title;
    }

    return Object.keys(state.editedWorkflowAttributes).length > 0
        && !state.isSavingWorkflow
        && !state.isAutosavingWorkflow
        && String(title).trim() !== '';
}

export const isCurrentWorkflowPublished = (state) => {
    return state.isCurrentWorkflowPublished;
}

export const getSelectedElementsCount = (state) => {
    return state.selectedNodes.length + state.selectedEdges.length;
}

export const getSelectedNodesCount = (state) => {
    return state.selectedNodes.length;
}

export const getSelectedEdgesCount = (state) => {
    return state.selectedEdges.length;
}

export const getDataTypes = (state) => {
    return state.dataTypes;
}

export const getDataTypeByName = (state, name) => {
    return state.dataTypes.find(dataType => dataType.name === name);
}

export function getGlobalVariables(state) {
    return state.globalVariables;
}

export function getGlobalVariable(state, name) {
    return state.globalVariables.find(variable => variable.name === name);
}

export function getTaxonomyTerms(state, taxnomy) {
    return state.taxonomyTerms[taxnomy] || [];
}

export function getBaseSlugCounts(state) {
    return state.baseSlugCounts;
}

export function getNodeErrors(state, nodeId) {
    return state.nodeErrors[nodeId] || [];
}

export function getAllNodeErrors(state) {
    return state.nodeErrors || {};
}

export function getDraggingFromHandle(state) {
    return state.draggingFromHandle;
}

export function isConnectingNodes(state) {
    return !!state.isConnectingNodes;
}

export function getRayDebugShowQueries(state) {
    return state.rayDebug.showQueries;
}

export function getRayDebugShowEmails(state) {
    return state.rayDebug.showEmails;
}

export function getRayDebugShowWordPressErrors(state) {
    return state.rayDebug.showWordPressErrors;
}

/**
 * Returns the source handles of the selected node that are connected to other nodes.
 * If multiple nodes are selected, it returns the handles of the first node.
 *
 * @param {Object} state - The state of the workflow.
 * @returns {Array} The handles of the selected node.
 */
export function getConnectedSourceHandlesOfSelectedNode(state) {
    const selectedNodes = getSelectedNodes(state);

    if (selectedNodes.length === 0) {
        return [];
    }

    const selectedNode = selectedNodes[0];
    const edges = getEdges(state);
    const outputEdges = edges.filter(edge => edge.source === selectedNode);
    let sourceHandles = outputEdges.map(edge => edge.sourceHandle);

    // Remove duplicates
    sourceHandles = sourceHandles.filter((handle, index, self) => self.indexOf(handle) === index);

    return sourceHandles;
}
