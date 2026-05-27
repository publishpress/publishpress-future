import { __ } from "@wordpress/i18n";
import { RadioControl, __experimentalVStack as VStack } from "@wordpress/components";
import { ExpressionBuilder } from "./expression-builder";
import { useCallback, useEffect, useState } from "@wordpress/element";

export function DebugData({ name, label, defaultValue, onChange, variables = [] }) {
    const [selectedOption, setSelectedOption] = useState();
    const defaultDebugOption = {expression: "{{input}}"};

    const onChangeSetting = (name, value) => {
        if (onChange) {
            onChange(name, value);
        }
    }

    // Convert legacy data to new data
    if (defaultValue?.dataToOutput) {
        defaultValue = {expression: `{{${defaultValue.dataToOutput}}}`};

        if (defaultValue.expression === '{{all-input}}') {
            defaultValue.expression = "{{input}}";
        }
    }

    if (defaultValue?.expression && defaultValue?.expression === '{{custom-data}}') {
        defaultValue = {expression: defaultValue.customData};
    }

    useEffect(() => {
        if (defaultValue?.expression === "{{input}}") {
            setSelectedOption("input");
        } else {
            setSelectedOption("custom-data");
        }
    }, []);

    const radioOptions = [
        {
            label: __("All received input", "post-expirator"),
            value: "input",
        },
        {
            label: __("Custom data", "post-expirator"),
            value: "custom-data",
        },
    ];

    const onChangeRadio = useCallback((value) => {
        setSelectedOption(value);

        if (value === "input") {
            onChangeSetting(name, {expression: "{{input}}"});
        } else {
            defaultValue.expression = '';
            onChangeSetting(name, {expression: defaultValue.expression});
        }
    }, [onChangeSetting, name, defaultValue]);

    return (
        <>
            <VStack>
                <RadioControl
                    options={radioOptions}
                    label={__("Select the data to output", "post-expirator")}
                    selected={selectedOption}
                    onChange={onChangeRadio}
                />

                {selectedOption === "custom-data" && (
                    <ExpressionBuilder
                        name={name}
                        label={label}
                        defaultValue={defaultValue || defaultDebugOption}
                        onChange={onChangeSetting}
                        variables={variables}
                    />
                )}
            </VStack>
        </>
    );
}

export default DebugData;
