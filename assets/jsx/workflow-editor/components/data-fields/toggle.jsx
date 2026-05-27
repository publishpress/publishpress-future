import { __ } from "@wordpress/i18n";
import { ToggleControl } from "@wordpress/components";
import { __experimentalVStack as VStack } from "@wordpress/components";

export function Toggle({ name, label, defaultValue, onChange, settings, description, variables = [] }) {

    const onChangeSetting = ({ value }) => {
        if (onChange) {
            onChange(name, value);
        }
    }

    return (
        <>
            <VStack>
                <ToggleControl
                    label={label}
                    checked={defaultValue || false}
                    onChange={(value) => onChangeSetting({ value })}
                />

                {description && (
                    <p className="description">{description}</p>
                )}
            </VStack>
        </>
    );
}

export default Toggle;
