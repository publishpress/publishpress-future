import { __ } from "@wordpress/i18n";
import {
    useMemo
} from "@wordpress/element";
import InlineSetting from "../inline-setting";
import { TextControl, SelectControl, Button } from "@wordpress/components";
import ExpressionBuilder from "../expression-builder";

export const ArgumentItem = ({
    name,
    label,
    popoverLabel,
    defaultValue,
    onChange,
    variables = [],
    onClosePopover,
    isLoading,
    autoOpen,
    withExpression = false
}) => {
    defaultValue = {
        name: "",
        value: "integer",
        type: "integer",
        ...defaultValue
    };

    const dataTypeOptions = [
        { label: __('Integer', 'post-expirator'), value: 'integer' },
        { label: __('String', 'post-expirator'), value: 'string' },
        { label: __('Boolean', 'post-expirator'), value: 'boolean' },
        { label: __('Object', 'post-expirator'), value: 'object' },
        { label: __('Array', 'post-expirator'), value: 'array' },
        { label: __('Post', 'post-expirator'), value: 'post' },
        { label: __('User', 'post-expirator'), value: 'user' },
    ];

    const onChangeName = (value) => {
        value = value.replace(/[^a-zA-Z0-9_]/g, '').trim();
        onChange({ ...defaultValue, name: value });
    }

    const onChangeValue = (value) => {
        onChange({ ...defaultValue, value: value, type: value });
    }

    const onChangeExpression = (settingName, value) => {
        onChange({ ...defaultValue, expression: value });
    }

    const getDataTypeLabel = () => {
        const dataType = defaultValue?.type || defaultValue?.value || 'integer';
        return dataTypeOptions.find(option => option.value === dataType)?.label;
    }

    const getExpressionPreview = () => {
        return defaultValue?.expression?.expression ? `${defaultValue.expression.expression}` : __('null', 'post-expirator');
    }

    const getValuePreview = () => {
        if (defaultValue?.expression?.expression) {
            return `${defaultValue.name}: ${getExpressionPreview()} (${getDataTypeLabel()})`;
        }

        return `${defaultValue.name}: (${getDataTypeLabel()})`;
    }

    return (
        <InlineSetting
            name={name}
            label={label}
            popoverLabel={popoverLabel}
            valuePreview={getValuePreview()}
            onClosePopover={onClosePopover}
            isLoading={isLoading}
            autoOpen={autoOpen}
        >
            <>
                <TextControl
                    label={__('Argument name', 'post-expirator')}
                    value={defaultValue.name}
                    onChange={onChangeName}
                    autoFocus={autoOpen}
                />
                <SelectControl
                    label={__('Data type', 'post-expirator')}
                    value={defaultValue?.type || defaultValue?.value}
                    options={dataTypeOptions}
                    onChange={onChangeValue}
                />
                {withExpression && (
                    <ExpressionBuilder
                        name="expression"
                        label={__('Value', 'post-expirator')}
                        defaultValue={defaultValue?.expression}
                        onChange={onChangeExpression}
                        variables={variables}
                    />
                )}

                <p className="description margin-top">
                    {__('The argument name should only contain letters, numbers and underscores.', 'post-expirator')}
                </p>
            </>
        </InlineSetting>
    )
}

export default ArgumentItem;
