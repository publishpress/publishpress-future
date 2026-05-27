import { __ } from "@wordpress/i18n";
import { TextControl } from "@wordpress/components";
import { __experimentalVStack as VStack } from "@wordpress/components";
import { DescriptionText } from "./description-text";

export function Text({
    name,
    label,
    defaultValue,
    onChange,
    settings,
    variables = [],
    helpUrl,
    description,
}) {
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
                <TextControl
                    label={label}
                    value={defaultValue || ""}
                    onChange={(value) => onChangeSetting({ value })}
                    placeholder={placeholder}
                />

                {description && (
                    <DescriptionText text={description} helpUrl={helpUrl} />
                )}
            </VStack>
        </>
    );
}

export default Text;
