import ExpressionBuilder from '../../expression-builder';

export const ConditionalExpressionBuilder = ({
    value,
    handleOnChange,
    context,
    readOnlyPreview,
    singleVariableOnly,
    autoComplete = false,
    completers = [],
}) => {
    const onChange = (name, value) => {
        if (handleOnChange) {
            handleOnChange(value.expression);
        }
    }

    if (value === 'global.site') {
        value = '';
    }

    return <div className="conditional-expression-builder">
        <ExpressionBuilder
            name={context.name}
            label={context.label}
            defaultValue={{expression: value}}
            onChange={onChange}
            variables={context.variables}
            isInline={true}
            readOnlyPreview={readOnlyPreview || false}
            singleVariableOnly={singleVariableOnly || false}
            oneLinePreview={true}
            wrapOnPreview={false}
            wrapOnEditor={false}
            autoComplete={autoComplete}
            completers={completers}
        />
    </div>;
};
