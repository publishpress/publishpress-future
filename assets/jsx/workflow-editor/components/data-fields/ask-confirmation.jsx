import { __ } from "@wordpress/i18n";
import { ToggleControl } from "@wordpress/components";
import { __experimentalVStack as VStack } from "@wordpress/components";
import ExpressionBuilder from "./expression-builder";

export function AskForConfirmation({ name, label, defaultValue, onChange, settings, description, variables = [] }) {

    const onChangeSetting = ({ settingName, value }) => {
        if (onChange) {
            const newSettings = {
                ...defaultValue,
                [settingName]: value,
            };

            onChange(name, newSettings);
        }
    }

    return (
        <>
            <VStack>
                <ToggleControl
                    label={label}
                    checked={defaultValue?.enabled || false}
                    onChange={(value) => onChangeSetting({ settingName: "enabled", value })}
                />

                {description && (
                    <p className="description">{description}</p>
                )}

                {defaultValue?.enabled && (
                    <ExpressionBuilder
                        name="confirmationMessage"
                        label="Confirmation message"
                        defaultValue={defaultValue?.message}
                        onChange={(settingName, value) => onChangeSetting({ settingName: "message", value })}
                        variables={variables}
                    />
                )}
            </VStack>
        </>
    );
}

export default AskForConfirmation;
