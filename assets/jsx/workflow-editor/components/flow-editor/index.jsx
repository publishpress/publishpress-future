import { useSelect, useDispatch, select } from "@wordpress/data";
import { store as workflowStore } from "../workflow-store";
import { store as editorStore } from "../editor-store";
import ReactFlow, {
    Background,
    Controls,
    MiniMap,
    applyNodeChanges,
    applyEdgeChanges,
    updateEdge,
    addEdge,
    useReactFlow,
    useOnSelectionChange,
    useOnViewportChange,
    MarkerType,
} from "reactflow";
import {
    useCallback,
    useRef,
    useEffect,
    Platform,
    useMemo,
} from "@wordpress/element";
import { EVENT_DROP_NODE, FEATURE_CONTROLS, FEATURE_MINI_MAP, NODE_TYPE_PLACEHOLDER, SLOT_SCOPE_WORKFLOW_EDITOR } from "../../constants";
import GenericNode from "../node-types/generic";

import { AUTO_LAYOUT_DEFAULT_DIRECTION } from "./auto-layout/constants";
import {
    SIDEBAR_NODE_EDGE,
    SIDEBAR_WORKFLOW,
} from "../settings-sidebar/constants";
import TriggerNode from "../node-types/trigger";
import NodeValidator from "../node-validator";
import { GenericEdge } from "../edge-types";
import { TriggerPlaceholder } from "../node-types/trigger-placeholder";
import { createNewNode, getId } from "../../utils";
import NodePlaceholder from "../node-types/node-placeholder";
import AutoLayout from "./auto-layout";
import { __ } from "@wordpress/i18n";
import { getNodeVariablesTree, filterVariablesTreeByDataType, getNodeById } from "../../utils";
import { CUSTOM_EVENT_HANDLES_COUNT_CHANGED } from "../../constants";

const GRID_SIZE = 10;

export const FlowEditor = (props) => {
    const {
        nodes,
        edges,
        selectedNodes,
        selectedEdges,
        hasActiveSideBar,
        activeComplementaryArea,
        initialViewport,
        isMiniMapFeatureActive,
        isControlsFeatureActive,
        isLoadingWorkflow,
        isConnectingNodes,
        getNodeTypeByName,
        globalVariables,
    } = useSelect((select) => {
        const activeComplementaryArea = select(
            "core/interface",
        ).getActiveComplementaryArea(SLOT_SCOPE_WORKFLOW_EDITOR);

        return {
            nodes: select(workflowStore).getNodes(),
            edges: select(workflowStore).getEdges(),
            selectedNodes: select(workflowStore).getSelectedNodes(),
            selectedEdges: select(workflowStore).getSelectedEdges(),
            activeComplementaryArea: activeComplementaryArea,
            hasActiveSideBar:
                activeComplementaryArea !== null &&
                activeComplementaryArea !== "null/undefined",
            initialViewport: select(workflowStore).getInitialViewport(),
            isMiniMapFeatureActive: select(editorStore).isFeatureActive(FEATURE_MINI_MAP),
            isControlsFeatureActive: select(editorStore).isFeatureActive(FEATURE_CONTROLS),
            isLoadingWorkflow: select(workflowStore).isLoadingWorkflow(),
            isConnectingNodes: select(workflowStore).isConnectingNodes(),
            getNodeTypeByName: select(editorStore).getNodeTypeByName,
            globalVariables: select(workflowStore).getGlobalVariables(),
        };
    });

    const {
        setNodes,
        setEdges,
        addNode,
        setSelectedNodes,
        setSelectedEdges,
        removeEdge,
        setEditedWorkflowAttribute,
        removePlaceholderNodes,
        setDraggingFromHandle,
        setIsConnectingNodes,
        unselectAll,
    } = useDispatch(workflowStore);

    const {
        openGeneralSidebar,
    } = useDispatch(editorStore);

    const {
        createSuccessNotice,
    } = useDispatch('core/notices');

    const reactFlowWrapperRef = useRef(null);
    const reactFlowInstance = useReactFlow();
    const { setViewport } = useReactFlow();
    const connectingNodeId = useRef(null);

    const proOptions = {
        // TODO: Change this to true after we start supporting the pro version of ReactFlow.
        hideAttribution: false,
    };

    const editorStyle = {
        backgroundColor: "#ffffff",
    };

    const nodeTypes = useMemo(() => ({
        generic: GenericNode,
        trigger: TriggerNode,
        triggerPlaceholder: TriggerPlaceholder,
        nodePlaceholder: NodePlaceholder,
    }), []);

    const edgeTypes = useMemo(() => ({
        genericEdge: GenericEdge,
    }), []);

    /**
     * This is used to update the edges that are connected to the node
     * when the handles count changes. This is required to avoid the
     * edges "connected" to the node or handle that do not exist anymore.
     */
    const handleHandlesCountChanged = useCallback((event) => {
        // Update all the edges that are connected to the node.
        const { nodeId, handles } = event.detail;
        const availableHandlesIds = handles.map((handle) => handle.id);
        const allEdges = select(workflowStore).getEdges();

        // Get all the edges that are connected to the node at the source handle.
        const edgesToUpdate = allEdges.filter((edge) => {
            return edge.source === nodeId && ! availableHandlesIds.includes(edge.sourceHandle);
        });

        // Remove the edges from the hidden handles.
        edgesToUpdate.forEach((edge) => {
            removeEdge(edge.id);
            setSelectedNodes([nodeId]);

        });
    }, []);

    useEffect(() => {
        document.addEventListener(CUSTOM_EVENT_HANDLES_COUNT_CHANGED, handleHandlesCountChanged);

        return () => {
            document.removeEventListener(CUSTOM_EVENT_HANDLES_COUNT_CHANGED, handleHandlesCountChanged);
        };
    }, []);

    const updateFlowInEditedWorkflow = useCallback(() => {
        // We need to delay the update of the flow to avoid missing the changes.
        setTimeout(() => {
            setEditedWorkflowAttribute("flow", reactFlowInstance.toObject());
        }, 400);
    }, [reactFlowInstance]);

    useOnViewportChange({
        onEnd: () => {
            updateFlowInEditedWorkflow();
        },
    });

    useEffect(() => {
        if (initialViewport) {
            setViewport(initialViewport);
        }
    }, [initialViewport]);

    const onNodesChange = useCallback(
        (changes) => {
            // TODO: Try to use the changes for handling the undo/redo state.
            setNodes(applyNodeChanges(changes, nodes));

            changes.forEach((change) => {
                const item = getNodeById(change.id, nodes);

                if (! item) {
                    return;
                }

                setDefaultNodeSettings(item);
            });

            updateFlowInEditedWorkflow();
        },
        [nodes],
    );

    const onEdgesChange = useCallback(
        (changes) => {
            // TODO: Try to use the changes for handling the undo/redo state.
            setEdges(applyEdgeChanges(changes, edges));
            updateFlowInEditedWorkflow();
        },
        [edges],
    );

    const onEdgeUpdate = useCallback(
        (oldEdge, newConnection) => {
            setEdges(updateEdge(oldEdge, newConnection, edges));
            updateFlowInEditedWorkflow();
        },
        [edges],
    );

    const onConnect = useCallback(
        (params) => {
            // Prevents the user from connecting a node to itself.
            if (params.source === params.target) {
                return;
            }

            params = {
                ...params,
                type: 'genericEdge',
                id: `${params.source}-${params.sourceHandle}-${params.target}-${params.targetHandle}`,
                markerEnd: {
                    type: MarkerType.ArrowClosed,
                },
            };

            setEdges(addEdge(params, edges));

            setDefaultNodeSettings(params.target);

            updateFlowInEditedWorkflow();
        },
        [edges, nodes, setDefaultNodeSettings, updateFlowInEditedWorkflow, setEdges, addEdge],
    );

    // This is used to create a new node when the user connects a node to the pane.
    const onConnectStart = useCallback((event, { nodeId, handleId, handleType }) => {
        connectingNodeId.current = nodeId;

        setDraggingFromHandle({
            sourceId: nodeId,
            handleId,
            handleType,
        })
      }, []);

    // This is used to create a new node when the user connects a node to the pane.
    const onConnectEnd = useCallback((event) => {
        event.stopPropagation();

        if (!connectingNodeId.current) {
            return;
        }

        const targetIsPane = event.target.classList.contains("react-flow__pane");

        if (targetIsPane && ! isConnectingNodes) {
            const width = event.target.offsetWidth;

            // Convert the screen element size to the flow position.
            const offset = reactFlowInstance.screenToFlowPosition({
                x: width / 2,
                y: 0,
            });

            const position = reactFlowInstance.screenToFlowPosition({
                x: event.clientX - offset.x,
                y: event.clientY,
            });

            const item = {
                id: getId(),
                type: 'nodePlaceholder',
                data: {
                    name: 'core/node-placeholder',
                    elementaryType: NODE_TYPE_PLACEHOLDER,
                },
                position,
            };

            addNode(item);

            setDefaultNodeSettings(item);
        }

        setIsConnectingNodes(false);
    }, [reactFlowInstance.screenToFlowPosition, isConnectingNodes, addNode]);

    const isValidConnection = useCallback((connection) => {
        setIsConnectingNodes(true);

        return true;
    }, []);

    const onDragOver = useCallback((event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = "move";
    }, []);

    const onDrop = useCallback(
        (event) => {
            event.preventDefault();

            const position = reactFlowInstance.screenToFlowPosition({
                x: event.clientX,
                y: event.clientY,
            });

            const dataTransferItem = event.dataTransfer.getData(EVENT_DROP_NODE);
            const item = JSON.parse(dataTransferItem);

            createNewNode({
                item,
                position,
                reactFlowInstance
            });

            setDefaultNodeSettings(item);

            removePlaceholderNodes();
        },
        [reactFlowInstance, nodes],
    );

    const setDefaultNodeSettings = useCallback((item) => {
        if (typeof item === 'string') {
            const itemId = item;
            item = getNodeById(itemId, nodes);

            if (! item) {
                return;
            }
        }

        if (! item.data) {
            return;
        }

        const nodeType = getNodeTypeByName(item.data.name);

        if (! nodeType) {
            return;
        }

        const settingsSchema = nodeType.settingsSchema;

        if (! settingsSchema) {
            return;
        }

        const variables = getNodeVariablesTree(item, globalVariables);

        settingsSchema.forEach((schema) => {
            schema?.fields.forEach((field) => {
                if (! field.default) {
                    return;
                }

                if (typeof field.default !== 'object') {
                    return;
                }

                Object.keys(field.default).forEach((key) => {
                    const defaultItem = field.default[key];

                    if (! defaultItem.rule) {
                        return;
                    }

                    if (defaultItem.rule === 'first') {
                        if (! Array.isArray(defaultItem.dataType)) {
                            defaultItem.dataType = [defaultItem.dataType];
                        }

                        const filteredVariables = filterVariablesTreeByDataType(variables, defaultItem.dataType);

                        if (filteredVariables.length === 0) {
                            return;
                        }

                        item.data.settings[field.name][key] = filteredVariables[0].id;
                    }
                });
            });
        });
    }, [nodes, getNodeTypeByName, globalVariables]);

    const onNodesDelete = useCallback(() => {
        unselectAll();



        updateFlowInEditedWorkflow();
    });

    const onEdgesDelete = useCallback(() => {
        unselectAll();
        updateFlowInEditedWorkflow();
    });

    const fitView = useCallback(() => {
        reactFlowInstance.fitView();
    }, [reactFlowInstance]);

    useOnSelectionChange({
        onChange: ({ nodes, edges }) => {
            // Avoid selecting the placeholder node.
            if (nodes.length > 0 && nodes[0].type === 'triggerPlaceholder') {
                setSelectedNodes([]);
                setSelectedEdges([]);

                return;
            }

            setSelectedNodes(nodes.map((node) => node.id));
            setSelectedEdges(edges.map((edge) => edge.id));

            if (!hasActiveSideBar) {
                return;
            }

            if (nodes.length === 0 && edges.length === 0) {
                openGeneralSidebar(SIDEBAR_WORKFLOW);
            }

            if (nodes.length > 0 || edges.length > 0) {
                openGeneralSidebar(SIDEBAR_NODE_EDGE);
            }
        },
    });

    const onAutoLayout = useCallback(() => {
        createSuccessNotice(
            __('Applying auto layout.', 'post-expirator'),
            {
                isDismissible: true,
                type: 'snackbar'
            }
        )

        import("./auto-layout/elk").then(({ useLayoutedElements }) => {
            const applyLayout = useLayoutedElements({
                nodes,
                edges,
                onLayout: (layoutedNodes, layoutedEdges) => {
                    setNodes(layoutedNodes);
                    setEdges(layoutedEdges);
                },
                onAnimationFrame: () => {
                    fitView();

                    updateFlowInEditedWorkflow();
                },
            });

            applyLayout({ direction: AUTO_LAYOUT_DEFAULT_DIRECTION });

            createSuccessNotice(
                __('Auto layout applied.', 'post-expirator'),
                {
                    isDismissible: true,
                    type: 'snackbar'
                }
            )
        });
    }, [nodes, edges, fitView, updateFlowInEditedWorkflow]);

    useEffect(() => {
        const sidebarIsActiveByDefault = Platform.select({
            web: true,
            native: false,
        });

        if (sidebarIsActiveByDefault) {
            openGeneralSidebar(SIDEBAR_WORKFLOW);
        }
    }, []);

    // Fix the behavior when the sidebar is closed and opened again, making sure a sidebar is loaded.
    useEffect(() => {
        if (activeComplementaryArea === "null/undefined") {
            const sidebar =
                selectedNodes.length > 0 || selectedEdges.length > 0
                    ? SIDEBAR_NODE_EDGE
                    : SIDEBAR_WORKFLOW;

            openGeneralSidebar(sidebar);
        }
    }, [activeComplementaryArea, selectedEdges, selectedNodes]);

    return (
        <div className="reactflow-wrapper" ref={reactFlowWrapperRef}>
            <AutoLayout onLayout={onAutoLayout} />
            {! isLoadingWorkflow && (
                <ReactFlow
                    nodes={nodes}
                    edges={edges}
                    onNodesChange={onNodesChange}
                    onEdgesChange={onEdgesChange}
                    onEdgeUpdate={onEdgeUpdate}
                    onConnectStart={onConnectStart}
                    onConnectEnd={onConnectEnd}
                    onConnect={onConnect}
                    isValidConnection={isValidConnection}
                    onDrop={onDrop}
                    onDragOver={onDragOver}
                    nodesDraggable={true}
                    proOptions={proOptions}
                    fitView={fitView}
                    style={editorStyle}
                    snapToGrid={true}
                    snapGrid={[GRID_SIZE, GRID_SIZE]}
                    nodeTypes={nodeTypes}
                    edgeTypes={edgeTypes}
                    onNodesDelete={onNodesDelete}
                    onEdgesDelete={onEdgesDelete}
                    connectionLineStyle={{ stroke: "#c2c2c2", strokeWidth: 2, strokeDasharray: '3,4', }}
                >
                    {isMiniMapFeatureActive && (
                        <MiniMap
                            pannable
                            zoomable
                            nodeColor={(node) => {
                                if (node.type === "generic") return "#FFCC00";
                            }}
                        />
                    )}

                    {isControlsFeatureActive && (
                        <Controls />
                    )}

                    <Background variant="dots" color="#ccc" gap={GRID_SIZE} />
                </ReactFlow>
            )}
            <NodeValidator />
        </div>
    );
};

export default FlowEditor;
