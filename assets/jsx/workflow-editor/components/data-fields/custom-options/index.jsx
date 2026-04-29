import { __, sprintf } from "@wordpress/i18n";
import { Button } from "@wordpress/components";
import { __experimentalVStack as VStack } from "@wordpress/components";
import { DescriptionText } from "../description-text";
import { useState } from "@wordpress/element";
import OptionItem from "./option-item";
import "./style.css";

const DEFAULT_OPTIONS = [
    {
        name: 'dismiss',
        label: __('Dismiss', 'post-expirator'),
        hint: __('Dismiss the notification', 'post-expirator'),
    },
];

export function CustomOptions({
    name,
    label,
    defaultValue,
    onChange,
    settings,
    variables = [],
    helpUrl,
    description,
    canChangeNameCallback,
    cantChangeNameDescription,
    onNameChangeCallback,
    maxOptions = 10,
}) {
    const [autoOpenItem, setAutoOpenItem] = useState(null);

    const onChangeSetting = ({ value }) => {
        if (onChange) {
            onChange(name, value);
        }
    }

    if (!defaultValue || !Array.isArray(defaultValue) || defaultValue.length === 0) {
        defaultValue = DEFAULT_OPTIONS;
    }

    const getDefaultName = () => {
        let index = defaultValue.length + 1;
        let name = `option${index}`;

        while (defaultValue.some(option => option.name === name)) {
            index++;
            name = `option${index}`;
        }

        return {
            name,
            index,
        };
    }

    const onClickAddOption = () => {
        if (defaultValue.length >= maxOptions) {
            return;
        }

        const defaultName = getDefaultName();
        const name = defaultName.name;
        const nameIndex = defaultName.index;

        const defaultLabel = sprintf(__('Option %s', 'post-expirator'), nameIndex);

        onChangeSetting({ value: [...defaultValue, { name, label: defaultLabel, hint: '' }] });
        setAutoOpenItem(defaultValue.length);
    }

    const onClickRemoveOption = (index) => {
        onChangeSetting({ value: defaultValue.filter((_, i) => i !== index) });
    }

    const onChangeOption = (index, value) => {
        const newOptions = defaultValue.map((option, i) => i === index ? value : option);
        onChangeSetting({ value: newOptions });
    }

    const hasOnlyOneOption = defaultValue.length === 1;

    return (
        <>
            <VStack className="workflow-editor-panel__row-options-container">
                <label className="workflow-editor-panel__row-options-label">
                    {label}
                </label>
                {defaultValue.map((option, index) => (
                    <div key={`option-${index}`} className="workflow-editor-panel__row-options">
                        <OptionItem
                            name={`options[${index}].name`}
                            label={`#${index + 1}`}
                            popoverLabel={sprintf(__('Option #%s', 'post-expirator'), index + 1)}
                            defaultValue={option}
                            onChange={(value) => onChangeOption(index, value)}
                            autoOpen={autoOpenItem === index}
                            onClosePopover={() => setAutoOpenItem(null)}
                            withExpression={settings?.withExpression}
                            variables={variables}
                            canChangeName={canChangeNameCallback ? canChangeNameCallback(option) : true}
                            cantChangeNameDescription={cantChangeNameDescription}
                            onNameChangeCallback={onNameChangeCallback}
                        />
                        {! hasOnlyOneOption && (
                            <Button onClick={() => onClickRemoveOption(index)} isSmall={true} iconSize={16} icon={'trash'} />
                        )}
                    </div>
                ))}

                {defaultValue.length === 0 && (
                    <div className="workflow-editor-panel__row-options-empty">
                        {__('No options added to the action.', 'post-expirator')}
                    </div>
                )}

                {defaultValue.length < maxOptions && (
                    <Button onClick={onClickAddOption} iconSize={16} icon={'plus'} variant="tertiary">
                        {__('Add a new option', 'post-expirator')}
                    </Button>
                )}

                {description && (
                    <DescriptionText text={description} helpUrl={helpUrl} />
                )}

                {defaultValue.length >= maxOptions && (
                    <DescriptionText
                        text={
                            sprintf(
                                __('You have reached the maximum number of options. You can add up to %s options.', 'post-expirator'),
                                maxOptions
                            )
                        }
                    />
                )}
            </VStack>
        </>
    );
}

export default CustomOptions;
