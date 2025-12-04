import { useMemo, useState, useEffect } from '@wordpress/element';
import { getVariableDataTypeByVariableName } from '../../../../utils';
import { ConditionalExpressionBuilder } from './conditional-expression-builder';
import postTypesAutocompleter from '../../expression-builder/autocompleters/post-types';
import postStatusesAutocompleter from '../../expression-builder/autocompleters/post-statuses';

export const ValueExpressionBuilder = ({ value, handleOnChange, context, rule }) => {
    const [completers, setCompleters] = useState([]);

    // Get the variable data type
    const variableDataType = useMemo(
        () => getVariableDataTypeByVariableName(rule.field, context.variables),
        [rule, context]
    );

    useEffect(() => {
        const newCompleters = [];

        switch (variableDataType) {
            case 'post_type':
                newCompleters.push(postTypesAutocompleter);
                break;
            case 'post_status':
                newCompleters.push(postStatusesAutocompleter);
                break;
        }

        setCompleters(newCompleters);
    }, [variableDataType]);

    if (rule.operator === 'null' || rule.operator === 'notNull' || rule.operator === 'isEmpty' || rule.operator === 'isNotEmpty') {
        return <div></div>;
    }

    return <ConditionalExpressionBuilder
        options={variableDataType}
        value={value}
        handleOnChange={handleOnChange}
        context={context}
        readOnlyPreview={false}
        autoComplete={true}
        completers={completers}
    />;
};
