import { memo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
    NODE_TYPE_ACTION,
    NODE_TYPE_ADVANCED,
    NODE_TYPE_TRIGGER,
    HANDLE_TYPE_SOURCE,
} from '../../constants';
import { store as workflowStore } from '../workflow-store';
import { useSelect } from '@wordpress/data';
import { Placeholder } from './placeholder';

export const NodePlaceholder = memo((props) => {
    const {
        draggingFromHandle,
    } = useSelect((select) => {
        const draggingFromHandle = select(workflowStore).getDraggingFromHandle();

        return {
            draggingFromHandle,
        };
    });

    const elementaryTypes = (draggingFromHandle.handleType === HANDLE_TYPE_SOURCE) ?
        [NODE_TYPE_ACTION, NODE_TYPE_ADVANCED] : [NODE_TYPE_TRIGGER, NODE_TYPE_ACTION, NODE_TYPE_ADVANCED];


    return (
        <Placeholder
            {...props}
            label={__('Add a step', 'post-expirator')}
            popoverIsOpen={true}
            searchLabel={__('Search for actions', 'post-expirator')}
            elementaryTypes={elementaryTypes}
        />
    );
});

export default NodePlaceholder;
