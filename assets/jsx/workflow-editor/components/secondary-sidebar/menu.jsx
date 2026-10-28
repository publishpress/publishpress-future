import { useState, useCallback, useMemo } from '@wordpress/element';
import { __, sprintf } from "@wordpress/i18n";
import { speak } from '@wordpress/a11y';
import { useReactFlow, addEdge, MarkerType } from 'reactflow';
import { VisuallyHidden, SearchControl } from '@wordpress/components';
import { useSelect, select } from '@wordpress/data';
import { Tips } from './tips';
import { NodesTab } from './nodes-tab';
import InserterTabs from './tabs';
import {
    INSERTER_TAB_ACTIONS,
    INSERTER_TAB_TRIGGERS
} from '../../constants';
import { store as editorStore } from '../editor-store';
import { useDispatch } from '@wordpress/data';
import InserterSearchResults from './inserter-search-results';
import { Popover } from '@wordpress/components';
import InserterPreviewPanel from './inserter-preview-panel';
import { store as workflowStore } from '../workflow-store';
import { createNewNode, getNodeById } from '../../utils';

const NEW_NODE_VERTICAL_GAP = 80;
const DEFAULT_NODE_HEIGHT = 80;

/**
 * Find the first source handle of a node that has no outgoing edge.
 */
function getFirstFreeSourceHandleId(node, nodeType, edges) {
    const sourceHandles = nodeType?.handleSchema?.source || [];

    const freeHandle = sourceHandles.find((handle) => {
        if (! handle?.id || handle?.type) {
            // Skip dynamic handles, their ids depend on the node settings.
            return false;
        }

        return ! edges.some((edge) => edge.source === node.id && edge.sourceHandle === handle.id);
    });

    return freeHandle?.id || null;
}

export function InserterMenu({
    onSelect,
    showInserterHelpPanel,
    showMostUsedNodes,
    __experimentalFilterValue = '',
    shouldFocusNode = true,
}) {
    const [filterValue, setFilterValue] = useState(
        __experimentalFilterValue
    );

    const {
        currentInserterTab,
        hoveredItem,
    } = useSelect((select) => {
        return {
            currentInserterTab: select(editorStore).getCurrentInserterTab(),
            hoveredItem: select(editorStore).getHoveredItem(),
        };
    });

    const {
        setCurrentInserterTab,
        setHoveredItem,
    } = useDispatch(editorStore);

    const reactFlowInstance = useReactFlow();

    const {
        setNodes,
        setEdges,
        removePlaceholderNodes,
    } = useDispatch(workflowStore);

    /**
     * Insert a step from the inserter using click or keyboard (Enter).
     * Uses the same node creation path as the drag and drop (createNewNode).
     */
    const onInsert = useCallback((item) => {
        if (! item || item.isDisabled || item.disabled) {
            return;
        }

        const nodes = select(workflowStore).getNodes();
        const edges = select(workflowStore).getEdges();
        const selectedNodeIds = select(workflowStore).getSelectedNodes() || [];
        const selectedNode = selectedNodeIds.length === 1 ? getNodeById(selectedNodeIds[0], nodes) : null;

        let position;

        if (selectedNode && selectedNode.position) {
            const internalNode = reactFlowInstance.getNode(selectedNode.id);
            const height = internalNode?.height || DEFAULT_NODE_HEIGHT;

            position = {
                x: selectedNode.position.x,
                y: selectedNode.position.y + height + NEW_NODE_VERTICAL_GAP,
            };
        } else {
            const flowElement = document.querySelector('.reactflow-wrapper .react-flow');
            const rect = flowElement ? flowElement.getBoundingClientRect() : null;

            position = reactFlowInstance.screenToFlowPosition({
                x: rect ? rect.left + (rect.width / 2) : window.innerWidth / 2,
                y: rect ? rect.top + (rect.height / 2) : window.innerHeight / 2,
            });
        }

        const newNode = createNewNode({
            item,
            position,
            reactFlowInstance,
        });

        removePlaceholderNodes();

        // Connect the new step to the selected step when it has a free output.
        let connectedTo = null;
        if (selectedNode) {
            const getNodeTypeByName = select(editorStore).getNodeTypeByName;
            const selectedNodeType = getNodeTypeByName(selectedNode.data?.name);
            const newNodeType = getNodeTypeByName(newNode.data.name) || item;
            const targetHandleId = newNodeType?.handleSchema?.target?.[0]?.id;
            const sourceHandleId = getFirstFreeSourceHandleId(selectedNode, selectedNodeType, edges);

            if (targetHandleId && sourceHandleId) {
                const newEdgeParams = {
                    source: selectedNode.id,
                    sourceHandle: sourceHandleId,
                    target: newNode.id,
                    targetHandle: targetHandleId,
                    type: 'genericEdge',
                    id: `${selectedNode.id}-${sourceHandleId}-${newNode.id}-${targetHandleId}`,
                    markerEnd: {
                        type: MarkerType.ArrowClosed,
                    },
                };

                setEdges(addEdge(newEdgeParams, edges));
                connectedTo = selectedNodeType?.label || selectedNode.data?.slug;
            }
        }

        // Select the new step so its settings show in the inspector.
        const currentNodes = select(workflowStore).getNodes();
        setNodes(currentNodes.map((node) => ({
            ...node,
            selected: node.id === newNode.id,
        })));

        if (connectedTo) {
            speak(
                sprintf(
                    /* translators: 1: the new step name, 2: the step it was connected to. */
                    __('Step added: %1$s, connected after %2$s.', 'post-expirator'),
                    item.label,
                    connectedTo
                ),
                'assertive'
            );
        } else {
            speak(
                sprintf(
                    /* translators: %s: the new step name. */
                    __('Step added: %s.', 'post-expirator'),
                    item.label
                ),
                'assertive'
            );
        }

        // Move the focus to the new node once React Flow has rendered it.
        setTimeout(() => {
            const nodeElement = document.querySelector(
                `.react-flow__node[data-id="${window.CSS && CSS.escape ? CSS.escape(newNode.id) : newNode.id}"]`
            );

            if (nodeElement) {
                nodeElement.focus();
            }
        }, 150);

        onSelect(item);
    }, [onSelect, reactFlowInstance, setNodes, setEdges, removePlaceholderNodes]);

    const onHover = useCallback((item) => {
        setHoveredItem(item);
    }, [setHoveredItem]);

    const triggersTab = useMemo(
        () => {
            const items = select(editorStore).getTriggerNodes();
            const categories = select(editorStore).getTriggerCategories();

            return (
                <>
                    <div className="block-editor-inserter__block-list">
                        <NodesTab
                            type={INSERTER_TAB_TRIGGERS}
                            onSelect={onInsert}
                            onHover={onHover}
                            showMostUsedNodes={showMostUsedNodes}
                            items={items}
                            categories={categories}
                        />
                    </div>
                    {showInserterHelpPanel && (
                        <div className="block-editor-inserter__tips">
                            <VisuallyHidden as="h2">
                                {__('A tip for using the workflow editor', 'post-expirator')}
                            </VisuallyHidden>
                            <Tips />
                        </div>
                    )}
                </>
            );
        },
        [
            onInsert,
            onHover,
            filterValue,
            showMostUsedNodes,
            showInserterHelpPanel,
        ]
    );

    const actionsTab = useMemo(
        () => {
            const actionItems = select(editorStore).getActionNodes();
            const advancedItems = select(editorStore).getAdvancedNodes();
            const items = [...actionItems, ...advancedItems];
            const categories = select(editorStore).getActionCategories();

            return (
                <>
                    <div className="block-editor-inserter__block-list">
                        <NodesTab
                            type={INSERTER_TAB_ACTIONS}
                            onSelect={onInsert}
                            onHover={onHover}
                            showMostUsedNodes={showMostUsedNodes}
                            items={items}
                            categories={categories}
                        />
                    </div>
                    {showInserterHelpPanel && (
                        <div className="block-editor-inserter__tips">
                            <VisuallyHidden as="h2">
                                {__('A tip for using the workflow editor', 'post-expirator')}
                            </VisuallyHidden>
                            <Tips />
                        </div>
                    )}
                </>
            );
        },
        [
            onInsert,
            onHover,
            filterValue,
            showMostUsedNodes,
            showInserterHelpPanel,
        ]
    );


    const getCurrentTab = useCallback(
        (tab) => {
            if (tab.name === INSERTER_TAB_TRIGGERS) {
                return triggersTab;
            }

            if (tab.name === INSERTER_TAB_ACTIONS) {
                return actionsTab;
            }
        },
        [triggersTab, actionsTab]
    );

    const onSelectTab = useCallback(
        (tab) => {
            setCurrentInserterTab(tab);
        },
        [setCurrentInserterTab]
    );

    return (
        <div className="block-editor-inserter__menu">
            <div className="block-editor-inserter__main-area show-as-tabs">
                { /* the following div is necessary to fix the sticky position of the search form */}
                <div className="block-editor-tabbed-sidebar">
                    <SearchControl
                        className="block-editor-inserter__search"
                        onChange={(value) => {
                            if (hoveredItem) setHoveredItem(null);
                            setFilterValue(value);
                        }}
                        value={filterValue}
                        label={__('Search for triggers and steps', 'post-expirator')}
                        placeholder={__('Search')}
                    />
                    <div className="block-editor-tabbed-sidebar__tabpanel">
                        {!!filterValue && (
                            <InserterSearchResults
                                filterValue={filterValue}
                                onSelect={onInsert}
                                onHover={onHover}
                            />
                        )}

                        {!filterValue && (
                            <InserterTabs
                                onSelect={onSelectTab}
                                initialTabName={currentInserterTab}
                            >
                                {getCurrentTab}
                            </InserterTabs>
                        )}
                    </div>
                </div>
            </div>
            {showInserterHelpPanel && hoveredItem && (
                <Popover
                    className='block-editor-inserter__preview-container__popover'
                    placement='right-start'
                    offset={16}
                    focusOnMount={false}
                    animate={false}
                >
                    <InserterPreviewPanel item={hoveredItem} />
                </Popover>
            )}
        </div>
    );
}
