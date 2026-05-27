import { __ } from "@wordpress/i18n";
import { __experimentalVStack as VStack } from "@wordpress/components";
import { TextareaControl } from "@wordpress/components";

export function Textarea({ name, label, defaultValue, settings, onChange, variables = [] }) {

    const onChangeSetting = ({ value }) => {
        if (onChange) {
            onChange(name, value);
        }
    }

    if (!defaultValue) {
        defaultValue = '';
    }

    const placeholder = settings?.placeholder || '';

    return (
        <>
            <VStack>
                <TextareaControl
                    label={label}
                    value={defaultValue || ""}
                    onChange={(value) => onChangeSetting({ value })}
                    placeholder={placeholder}
                />
            </VStack>
        </>
    );
}

export default Textarea;
