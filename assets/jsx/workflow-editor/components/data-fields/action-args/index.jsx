import { __, sprintf } from "@wordpress/i18n";
import { TextControl, Button, SelectControl } from "@wordpress/components";
import { __experimentalVStack as VStack } from "@wordpress/components";
import { DescriptionText } from "../description-text";
import { useEffect, useState } from "@wordpress/element";
import ArgumentItem from "./argument-item";
import "./style.css";

export function ActionArgs({
    name,
    label,
    defaultValue,
    onChange,
    settings,
    variables = [],
    helpUrl,
    description,
}) {
    const [autoOpenItem, setAutoOpenItem] = useState(null);

    const onChangeSetting = ({ value }) => {
        if (onChange) {
            onChange(name, value);
        }
    }

    if (!defaultValue || !Array.isArray(defaultValue)) {
        defaultValue = [];
    }

    // If the args are empty or have no value, set the default value
    useEffect(() => {
        if (defaultValue.length === 0) {
            return;
        }

        defaultValue.forEach((arg) => {
            if (!arg.value) {
                arg.value = defaultDataType;
            }
        });
    }, []);

    const defaultDataType = 'integer';

    const onClickAddArg = () => {
        onChangeSetting({ value: [...defaultValue, { name: '', value: defaultDataType, type: defaultDataType }] });
        setAutoOpenItem(defaultValue.length);
    }

    const onClickRemoveArg = (index) => {
        onChangeSetting({ value: defaultValue.filter((_, i) => i !== index) });
    }

    const onChangeArg = (index, value) => {
        const newArgs = defaultValue.map((arg, i) => i === index ? value : arg);
        onChangeSetting({ value: newArgs, type: newArgs });
    }

    return (
        <>
            <VStack className="workflow-editor-panel__row-args-container">
                <label className="workflow-editor-panel__row-args-label">
                    {label}
                </label>
                {defaultValue.map((arg, index) => (
                    <div key={`arg-${index}`} className="workflow-editor-panel__row-args">
                        <ArgumentItem
                            name={`args[${index}].name`}
                            label={`#${index + 1}`}
                            popoverLabel={sprintf(__('Argument #%s', 'post-expirator'), index + 1)}
                            defaultValue={arg}
                            onChange={(value) => onChangeArg(index, value)}
                            autoOpen={autoOpenItem === index}
                            onClosePopover={() => setAutoOpenItem(null)}
                            withExpression={settings?.withExpression}
                            variables={variables}
                        />
                        <Button onClick={() => onClickRemoveArg(index)} isSmall={true} iconSize={16} icon={'trash'} />
                    </div>
                ))}

                {defaultValue.length === 0 && (
                    <div className="workflow-editor-panel__row-args-empty">
                        {__('No arguments added to the action.', 'post-expirator')}
                    </div>
                )}

                <Button onClick={onClickAddArg} iconSize={16} icon={'plus'} variant="tertiary">
                    {__('Add a new argument', 'post-expirator')}
                </Button>


                {description && (
                    <DescriptionText text={description} helpUrl={helpUrl} />
                )}
            </VStack>
        </>
    );
}

export default ActionArgs;
