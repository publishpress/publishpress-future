import { memo, useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Popover, SearchControl } from '@wordpress/components';
import { NodesTab } from '../secondary-sidebar/nodes-tab';
import { store as editorStore } from '../editor-store';
import { store as workflowStore } from '../workflow-store';
import { useSelect, useDispatch } from '@wordpress/data';
import {
    FEATURE_INSERTER,
    HANDLE_TYPE_SOURCE,
    INSERTER_TAB_TRIGGERS,
    NODE_TYPE_ACTION,
    NODE_TYPE_ADVANCED,
    NODE_TYPE_TRIGGER
} from '../../constants';
import InserterSearchResults from '../secondary-sidebar/inserter-search-results';
import { useReactFlow, addEdge, MarkerType } from 'reactflow';
import { createNewNode, getNodeById } from '../../utils';
import PlusIcon from '../icons/plus';


export const Placeholder = memo(({id, label, popoverIsOpen = false, searchLabel, elementaryTypes}) => {
    const nodeLabel = label || '';

    const nodeId = useRef(null);
    const searchControlRef = useRef(null);

    const reactFlowInstance = useReactFlow();

    const [inserterIsOpen, setInserterIsOpen] = useState(popoverIsOpen);
    const [filterValue, setFilterValue] = useState('');

    const {
        nodes,
        edges,
        items,
        categories,
        isSidebarInserterOpen,
        draggingFromHandle,
        getNodeTypeByName,
    } = useSelect((select) => {
        const triggerNodes = select(editorStore).getTriggerNodes();
        const actionNodes = select(editorStore).getActionNodes();
        const advancedNodes = select(editorStore).getAdvancedNodes();

        let items = [];
        if (! elementaryTypes) {
            items = [...triggerNodes, ...actionNodes, ...advancedNodes];
        } else {
            if (elementaryTypes.includes(NODE_TYPE_TRIGGER)) {
                items = items.concat(triggerNodes);
            }
            if (elementaryTypes.includes(NODE_TYPE_ACTION)) {
                items = items.concat(actionNodes);
            }
            if (elementaryTypes.includes(NODE_TYPE_ADVANCED)) {
                items = items.concat(advancedNodes);
            }
        }

        return {
            nodes: select(workflowStore).getNodes(),
            edges: select(workflowStore).getEdges(),
            items,
            categories: select(editorStore).getTriggerCategories(),
            isSidebarInserterOpen: select(editorStore).isFeatureActive(FEATURE_INSERTER),
            draggingFromHandle: select(workflowStore).getDraggingFromHandle(),
            getNodeTypeByName: select(editorStore).getNodeTypeByName,
        };
    });

    const {
        setEdges,
        setDraggingFromHandle,
        removePlaceholderNodes,
    } = useDispatch(workflowStore);

    const {
        enableFeature,
    } = useDispatch(editorStore);

    const onClickAddButton = (event) => {
        event.stopPropagation();

        setInserterIsOpen(true);
    }

    const onSelectItem = (item) => {
        const placeholderNode = getNodeById(nodeId.current, nodes);

        const position = {
            x: placeholderNode.position.x,
            y: placeholderNode.position.y,
        };

        const newNode = createNewNode({
            item,
            position,
            reactFlowInstance
        });

        if (draggingFromHandle?.sourceId) {
            // Get the node type of the target node
            const nodeType = getNodeTypeByName(newNode.data.name);

            let firstTargetHandleId;
            let newEdgeParams;

            if (draggingFromHandle.handleType === HANDLE_TYPE_SOURCE) {
                // When dragging from a source handle we are connecting to a lower level handle, to the target handle.
                firstTargetHandleId = nodeType.handleSchema.target[0].id;
                newEdgeParams = {
                    source: draggingFromHandle.sourceId,
                    sourceHandle: draggingFromHandle.handleId,
                    target: newNode.id,
                    targetHandle: firstTargetHandleId,
                    type: 'genericEdge',
                    id: `${draggingFromHandle.sourceId}-${draggingFromHandle.handleId}-${newNode.id}-${firstTargetHandleId}`,
                    markerEnd: {
                        type: MarkerType.ArrowClosed,
                    },
                };
            } else {
                // When dragging from a target handle we are connecting to a higher level handle, to the source handle.
                firstTargetHandleId = nodeType.handleSchema.source[0].id;
                newEdgeParams = {
                    source: newNode.id,
                    sourceHandle: firstTargetHandleId,
                    target: draggingFromHandle.sourceId,
                    targetHandle: draggingFromHandle.handleId,
                    type: 'genericEdge',
                    id: `${newNode.id}-${firstTargetHandleId}-${draggingFromHandle.sourceId}-${draggingFromHandle.handleId}`,
                    markerEnd: {
                        type: MarkerType.ArrowClosed,
                    },
                };
            }

            setEdges(addEdge(newEdgeParams, edges));

            setDraggingFromHandle({sourceId: null, sourceHandle: null, handleType: null});
        }
    }

    const onClosePopup = () => {
        setInserterIsOpen(false);
        removePlaceholderNodes();
    }

    const onBrowseAll = () => {
        enableFeature(FEATURE_INSERTER);
        removePlaceholderNodes();
    }

    useEffect(() => {
        if (! nodeId.current) {
            nodeId.current = id;
        }

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' || event.keyCode === 27) {
                onClosePopup();
            }
        });
    }, []);

    if (! searchLabel) {
        searchLabel = __('Search for steps', 'post-expirator');
    }

    useEffect(() => {
        if (inserterIsOpen) {
            searchControlRef.current?.focus();
        } else {
            // If open the inserter by default, we remove the placeholder when it is closed
            if (popoverIsOpen) {
                removePlaceholderNodes();
            }
        }
    }, [inserterIsOpen]);

    return (
        <>
            {inserterIsOpen && (
                <Popover placement="bottom-start" offset={14} className='react-flow__node-inserter-popover'>
                    <div className="react-flow__node-inserter-popover-content">
                        <SearchControl
                            ref={searchControlRef}
                            className="block-editor-inserter__search"
                            onChange={(value) => {
                                setFilterValue(value);
                            }}
                            value={filterValue}
                            label={__('Search for triggers and steps', 'post-expirator')}
                            placeholder={__('Search')}
                        />

                        {!!filterValue && (
                            <InserterSearchResults
                                filterValue={filterValue}
                                onSelect={onSelectItem}
                                filterTypes={elementaryTypes}
                            />
                        )}

                        {!filterValue && (
                            <div className="block-editor-inserter__block-list">
                                <NodesTab
                                    type={INSERTER_TAB_TRIGGERS}
                                    onSelect={onSelectItem}
                                    items={items}
                                    showMostUsedNodes={true}
                                    categories={[]}
                                />
                            </div>
                        )}
                    </div>
                    <Button
                        onClick={onBrowseAll}
                        className="react-flow__node-inserter-popover-close"
                    >{__('Browse all')}</Button>
                </Popover>
            )}
            <div className={"react-flow__node-body react-flow__node-triggerPlaceholderNode"} onClick={onClickAddButton}>
                <div className='react-flow__node-inner-body'>
                    <div className='react-flow__node-header'>
                        <div className='icon'>
                            <PlusIcon size={12} />
                        </div>
                        <div className="react-flow__node-label">{nodeLabel}</div>
                    </div>
                </div>
            </div>
        </>
    );
});

export default Placeholder;
