import {
    BaseEdge,
    EdgeLabelRenderer,
    getBezierPath,
    MarkerType,
} from 'reactflow';
import { store as workflowStore } from '../workflow-store';
import { useSelect, useDispatch } from '@wordpress/data';
import { Popover, Toolbar, ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export function GenericEdge({
    id,
    sourceX,
    sourceY,
    targetX,
    targetY,
    sourcePosition,
    targetPosition,
    style = {},
    selected,
    markerEnd,
}) {
    const {
        isSingularElementSelected,
    } = useSelect((select) => {
        const selectedElementsCount = select(workflowStore).getSelectedElementsCount();

        return {
            isSingularElementSelected: selectedElementsCount === 1,
        };
    });

    const {
        removeEdge,
    } = useDispatch(workflowStore);

    const [edgePath, labelX, labelY] = getBezierPath({
        sourceX,
        sourceY,
        sourcePosition,
        targetX,
        targetY,
        targetPosition,
    });

    const onEdgeClick = () => {
        removeEdge(id);
    }

    const edgeStyle = {
        ...style,
        strokeWidth: 2,
    }

    return (
        <>
            <BaseEdge path={edgePath} markerEnd={markerEnd} style={edgeStyle} animated={false} />
            {selected && isSingularElementSelected && (
                <EdgeLabelRenderer>
                    <div
                        style={{
                            position: 'absolute',
                            transform: `translate(-50%, -50%) translate(${labelX}px,${labelY}px)`,
                            fontSize: 12,
                            // everything inside EdgeLabelRenderer has no pointer events by default
                            // if you have an interactive element, set pointer-events: all
                            pointerEvents: 'all',
                        }}
                        className='nodrag nopan react-flow__edge-toolbar-anchor'
                    >
                        <Popover position='top'>
                            <Toolbar className="components-accessible-toolbar block-editor-block-contextual-toolbar react-flow__edge-toolbar">
                                <ToolbarGroup>
                                    <ToolbarButton
                                        icon='trash'
                                        title={__('Delete', 'web-stories')}
                                        onClick={onEdgeClick}
                                    />
                                </ToolbarGroup>
                            </Toolbar>
                        </Popover>
                    </div>
                </EdgeLabelRenderer>
            )}
        </>
    );
}

export default GenericEdge;
