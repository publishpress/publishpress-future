import { __ } from "@wordpress/i18n";
import InlineSetting from "../inline-setting";
import { TextControl } from "@wordpress/components";
import ExpressionBuilder from "../expression-builder";

export const OptionItem = ({
    name,
    label,
    popoverLabel,
    defaultValue,
    onChange,
    variables = [],
    onClosePopover,
    isLoading,
    autoOpen,
    withExpression = false,
    canChangeName = true,
    cantChangeNameDescription = '',
    onNameChangeCallback,
}) => {
    defaultValue = {
        name: "",
        label: "",
        hint: "",
        ...defaultValue
    };

    const onChangeName = (value) => {
        value = value.replace(/[^a-zA-Z0-9_]/g, '').trim();
        onChange({ ...defaultValue, name: value });

        if (onNameChangeCallback) {
            onNameChangeCallback(value);
        }
    }

    const onChangeLabel = (value) => {
        onChange({ ...defaultValue, label: value });
    }

    const onChangeHint = (value) => {
        onChange({ ...defaultValue, hint: value });
    }

    const onChangeExpression = (settingName, value) => {
        onChange({ ...defaultValue, expression: value });
    }

    const getExpressionPreview = () => {
        return defaultValue?.expression?.expression ? `${defaultValue.expression.expression}` : __('null', 'post-expirator');
    }

    const getValuePreview = () => {
        if (defaultValue?.expression?.expression) {
            return getExpressionPreview();
        }

        return defaultValue.label;
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
                    label={__('Name', 'post-expirator')}
                    value={defaultValue.name}
                    onChange={onChangeName}
                    autoFocus={autoOpen}
                    readOnly={!canChangeName}
                />
                <TextControl
                    label={__('Label', 'post-expirator')}
                    value={defaultValue.label}
                    onChange={onChangeLabel}
                />
                <TextControl
                    label={__('Hint', 'post-expirator')}
                    value={defaultValue.hint}
                    onChange={onChangeHint}
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

                {cantChangeNameDescription && !canChangeName && (
                    <p className="description margin-top">
                        {cantChangeNameDescription}
                    </p>
                )}

                {canChangeName && (
                    <p className="description margin-top">
                        {__('The option name should only contain letters, numbers and underscores.', 'post-expirator')}
                    </p>
                )}
            </>
        </InlineSetting>
    )
}

export default OptionItem;
