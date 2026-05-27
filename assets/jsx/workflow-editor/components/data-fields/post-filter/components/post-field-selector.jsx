import { useContext } from '@wordpress/element';
import { select } from '@wordpress/data';
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { store as workflowStore } from '../../../workflow-store';
import { FieldExpressionBuilder } from '../../conditional/components/field-expression-builder';
import { getExpandedStepScopedVariables } from '../../../../utils';
/**
 * Field selector component for post queries
 */
export const PostFieldSelector = ({ value, handleOnChange, options, context, ...props }) => {
    const nodes = select(workflowStore).getSelectedNodes();
    const node = select(workflowStore).getNodeById(nodes[0]);

    context.variables = getExpandedStepScopedVariables(node);

    return <FieldExpressionBuilder
        value={value}
        handleOnChange={handleOnChange}
        context={context}
        singleVariableOnly={false}
        {...props}
    />;
};
