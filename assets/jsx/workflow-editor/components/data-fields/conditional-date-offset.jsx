import { __ } from "@wordpress/i18n";
import { ToggleControl } from "@wordpress/components";
import { __experimentalVStack as VStack } from "@wordpress/components";
import DateOffset from "./date-offset";

export function ConditionalDateOffset({ name, label, defaultValue, onChange, settings, description, variables = [] }) {

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
                    <DateOffset
                        name={name}
                        label={__("When to expire", "post-expirator")}
                        defaultValue={defaultValue}
                        onChange={onChange}
                        settings={settings}
                        description={description}
                    />
                )}
            </VStack>
        </>
    );
}

export default ConditionalDateOffset;
