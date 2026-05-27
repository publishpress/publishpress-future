import { Handle, Position, useUpdateNodeInternals } from 'reactflow';
import { memo, useEffect, useRef } from '@wordpress/element';
import NodeIcon from '../node-icon';
import { useSelect, useDispatch } from "@wordpress/data";
import { store as workflowStore } from "../workflow-store";
import { store as editorStore } from "../editor-store";
import { __ } from '@wordpress/i18n';
import { Toolbar, ToolbarGroup, ToolbarButton, Popover } from '@wordpress/components';
import PlayIcon from "../icons/play";
import { SIDEBAR_NODE_EDGE } from '../settings-sidebar/constants';
import { useIsPro } from '../../contexts/pro-context';
import jsonLogic from "json-logic-js";
import { CUSTOM_EVENT_HANDLES_COUNT_CHANGED } from '../../constants';
import { getNodeHandleSchema } from '../../utils';

export const GenericNode = memo(({ id, data, isConnectable, selected, nodeTypeIcon }) => {
    const {
        nodeHasErrors,
        isAdvancedSettingsEnabled,
        isSingularElementSelected,
        getNodeTypeByName,
    } = useSelect((select) => {
        const nodeErrors = select(workflowStore).getNodeErrors(id);
        const selectedElementsCount = select(workflowStore).getSelectedElementsCount();

        return {
            nodeHasErrors: Object.keys(nodeErrors).length > 0,
            isAdvancedSettingsEnabled: true,
            isSingularElementSelected: selectedElementsCount === 1,
            getNodeTypeByName: select(editorStore).getNodeTypeByName,
        }
    });

    const updateNodeInternals = useUpdateNodeInternals();

    const isPro = useIsPro();

    const {
        removeNode,
    } = useDispatch(workflowStore);

    const {
        openGeneralSidebar,
    } = useDispatch(editorStore);

    const previousHandlesCountRef = useRef();
    const previousSourceHandlesRef = useRef();

    let nodeType = getNodeTypeByName(data.name);

    if (! nodeType || ! nodeType.handleSchema) {
        nodeType = {
            "id": Math.floor(Math.random() * 1000000),
            "type": "generic",
            "elementaryType": "action",
            "name": data.name,
            "label": sprintf(__('Unknown node: %s', 'post-expirator'), data.name),
            "description": __('This is a placeholder node for a node that does not exist.', 'post-expirator'),
            "baseSlug": "deletePost",
            "initiatlAttributes": [],
            "category": "post",
            "disabled": false,
            "isDisabled": false,
            "frecency": 1,
            "icon": {
                "src": "media-document",
                "background": "#ffffff",
                "foreground": "#1e1e1e"
            },
            "version": 1,
            "settingsSchema": [],
            "outputSchema": [
                {
                    "name": "input",
                    "type": "input",
                    "label": "Step input",
                    "description": "The input data for this step."
                }
            ],
            "className": "react-flow__node-unknownNode",
            "handleSchema": {
                "target": [
                    {
                        "id": "input",
                        "left": "50%"
                    }
                ],
                "source": [
                    {
                        "id": "output",
                        "left": "50%",
                        "label": "Next"
                    }
                ]
            },
            "isProFeature": false,
            "validationSchema": {
                "connections": {
                    "rules": []
                },
                "settings": {
                    "rules": []
                }
            }
        };
    }

    const nodeDescription = data?.label;
    const nodeLabel = nodeType.label || __('Node', 'post-expirator');
    const nodeClassName = nodeType?.className || 'react-flow__node-genericNode';

    let targetHandles;
    let handlesToDisplay;

    if (nodeType.handleSchema) {
        if (nodeType.handleSchema.target) {
            const originalTargetHandles = getNodeHandleSchema(nodeType, data, 'target');
            handlesToDisplay = filterHandlesByConditions(originalTargetHandles, data);

            useEffect(() => {
                if (previousHandlesCountRef.current !== handlesToDisplay.length) {
                    previousHandlesCountRef.current = handlesToDisplay.length;

                    const event = new CustomEvent(CUSTOM_EVENT_HANDLES_COUNT_CHANGED, {
                        detail: {
                            nodeId: id,
                            handlesCount: handlesToDisplay.length,
                            handles: handlesToDisplay,
                            originalHandles: originalTargetHandles,
                            type: 'target',
                        },
                    });

                    document.dispatchEvent(event);

                    updateNodeInternals(id);
                }
            }, [handlesToDisplay, updateNodeInternals]);

            targetHandles = handlesToDisplay.map((handle, index) => {
                const left = calculateLeftPosition(index, handlesToDisplay.length);

                if (! handle?.id) {
                    return null;
                }

                return (
                    <Handle
                        key={handle.id + '_target'}
                        type="target"
                        position={Position.Top}
                        id={handle.id}
                        style={{ left: `${left}`}}
                        isConnectable={isConnectable}
                        className={'handle-target-' + handle.id}
                    />
                );
            });
        }
    }

    let sourceHandles = null;
    let handleAreas = null;

    if (nodeType.handleSchema) {
        if (nodeType.handleSchema.source) {
            const originalSourceHandles = getNodeHandleSchema(nodeType, data, 'source');
            handlesToDisplay = filterHandlesByConditions(originalSourceHandles, data);

            useEffect(() => {
                if (previousHandlesCountRef.current !== handlesToDisplay.length) {
                    previousHandlesCountRef.current = handlesToDisplay.length;

                    const event = new CustomEvent(CUSTOM_EVENT_HANDLES_COUNT_CHANGED, {
                        detail: {
                            nodeId: id,
                            handlesCount: handlesToDisplay.length,
                            handles: handlesToDisplay,
                            originalHandles: originalSourceHandles,
                            type: 'source',
                        },
                    });

                    document.dispatchEvent(event);

                    updateNodeInternals(id);
                }

                // Detect if the handles ids have changed
                if (previousSourceHandlesRef.current) {
                    const handlesChanged = previousSourceHandlesRef.current.some((prevHandle, index) => {
                        const currentHandle = handlesToDisplay[index];
                        return !currentHandle || prevHandle.id !== currentHandle.id;
                    });

                    if (handlesChanged) {
                        previousSourceHandlesRef.current = handlesToDisplay;

                        updateNodeInternals(id);
                    }
                }
                if (! previousSourceHandlesRef.current) {
                    previousSourceHandlesRef.current = handlesToDisplay;
                }
            }, [handlesToDisplay, updateNodeInternals]);

            sourceHandles = handlesToDisplay.map((handle, index) => {
                const left = calculateLeftPosition(index, handlesToDisplay.length);

                if (! handle?.id) {
                    return null;
                }

                return (
                    <Handle
                        key={handle.id + '_source'}
                        type="source"
                        position={Position.Bottom}
                        id={handle.id}
                        style={{ left: `${left}` }}
                        isConnectable={isConnectable}
                        className={'handle-source-' + handle.id}
                    />
                );
            });

            handleAreas = handlesToDisplay.map((handle) => {
                if (! handle?.id) {
                    return null;
                }

                return (
                    <div
                        key={handle.id + 'handleArea'}
                        className={'react-flow__node-handle-name handle-area-source-' + handle.id}
                    >
                        {handle.label}
                    </div>
                );
            });
        }
    }

    let topText = __('Step', 'post-expirator');
    if (data.elementaryType === 'action') {
        topText = __('Action', 'post-expirator');
    } else if (data.elementaryType === 'advanced') {
        topText = __('Advanced', 'post-expirator');
    } else if (data.elementaryType === 'trigger') {
        topText = __('Trigger', 'post-expirator');
    }

    const nodeAttributes = [];

    const onClickDeleteNode = () => {
        removeNode(id);
    };

    const onDoubleClick = () => {
        if (isSingularElementSelected) {
            openGeneralSidebar(SIDEBAR_NODE_EDGE);
        }
    }

    if (! nodeTypeIcon) {
        nodeTypeIcon = <PlayIcon size={8} />;
    }


    // Unfocus the tolbar button when the node is selected
    const nodeRef = useRef(null);
    useEffect(() => {
        if (selected && isSingularElementSelected) {
            setTimeout(() => {
                jQuery(nodeRef.current.parentNode).focus();
            }, 100);
        }
    }, [selected, isSingularElementSelected]);

    return (
        <>
            {selected && isSingularElementSelected && (
                <>
                    <Popover placement="top-start" offset={14}>
                        <Toolbar
                            label={__('Step actions', 'post-expirator')}
                            className="components-accessible-toolbar block-editor-block-contextual-toolbar react-flow__node-toolbar"
                        >
                            <ToolbarGroup>
                                <ToolbarButton
                                    icon={'trash'}
                                    label={__('Delete', 'post-expirator')}
                                    onClick={onClickDeleteNode}
                                    accessibleWhenDisabled={true}
                                />
                            </ToolbarGroup>
                        </Toolbar>
                    </Popover>
                </>
            )}

            <div
                className={"react-flow__node-body " + nodeClassName}
                onDoubleClick={onDoubleClick}
                ref={nodeRef}
            >
                {targetHandles}
                <div className='react-flow__node-top'>
                    {nodeTypeIcon}
                    {topText}: <span className='node-slug'>{data.slug}</span>
                </div>

                <div className={nodeDescription ? 'react-flow__node-inner-body with-description' : 'react-flow__node-inner-body'}>

                    {(nodeHasErrors || (nodeType.isProFeature && !isPro)) && (
                        <div className='react-flow__node-marker-wrapper'>
                            {nodeType.isProFeature && !isPro && (
                                <div className='react-flow__node-pro-badge'
                                    title={__('Currently this step is being skipped. Upgrade to Pro to unlock this feature.', 'post-expirator')}
                                >
                                    <NodeIcon icon={'lock'} iconSize={8} />
                                </div>
                            )}

                            {nodeHasErrors && (
                                <div className='react-flow__node-error'
                                    title={__('This node has errors', 'post-expirator')}
                                >
                                    <NodeIcon icon={'exclamation'} iconSize={8} />
                                </div>
                            )}

                        </div>
                    )}

                    <div className="react-flow__node-header">
                        <NodeIcon icon={nodeType.icon.src} iconSize={14} />
                        <div className="react-flow__node-label">{nodeLabel}</div>
                    </div>

                    {nodeDescription && (
                        <div className="react-flow__node-description">{nodeDescription}</div>
                    )}

                    {isAdvancedSettingsEnabled && nodeAttributes.length > 0 &&
                        <div className='react-flow__node-content'>
                            <table>
                                <tbody>
                                    {nodeAttributes.map((attribute) => {
                                        return (
                                            <tr key={'attribute_' + attribute.id}>
                                                <th>{attribute.label}</th>
                                                <td>{attribute.value}</td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    }
                </div>

                <div className='react-flow__node-handle-area'>
                    {handleAreas}
                </div>

                {sourceHandles}
            </div>
        </>
    );
});

export default GenericNode;

function filterHandlesByConditions(handles, data) {
    return handles?.filter((handle) => {
        if (handle?.conditions) {
            return jsonLogic.apply(handle.conditions, data.settings);
        }

        return true;
    });
}

function calculateLeftOffset(handlesCount) {
    const leftOffset = 100 / handlesCount / 2;

    return leftOffset;
}

function calculateLeftPosition(index, handlesCount) {
    const leftOffset = calculateLeftOffset(handlesCount);
    const left = leftOffset + ((100 / handlesCount) * index);

    return `${left}%`;
}
