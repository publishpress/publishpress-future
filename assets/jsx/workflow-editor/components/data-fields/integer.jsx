import { __ } from "@wordpress/i18n";
import { TextControl } from "@wordpress/components";
import { __experimentalVStack as VStack } from "@wordpress/components";
import { DescriptionText } from "./description-text";

export function Integer({
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
        value = parseInt(value);

        if (onChange) {
            onChange(name, value);
        }
    }

    defaultValue = parseInt(defaultValue);

    if (isNaN(defaultValue)) {
        defaultValue = 0;
    }

    const placeholder = settings?.placeholder || '';

    const handleKeyDown = (event) => {
        const shiftPressed = event.shiftKey;

        const increment = shiftPressed ? 10 : 1;

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            const newValue = (parseInt(defaultValue) || 0) + increment;
            onChangeSetting({ value: newValue });
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            const newValue = (parseInt(defaultValue) || 0) - increment;
            onChangeSetting({ value: newValue });
        }
    }

    return (
        <>
            <VStack>
                <TextControl
                    label={label}
                    value={defaultValue || ""}
                    onChange={(value) => onChangeSetting({ value })}
                    placeholder={placeholder}
                    onKeyDown={handleKeyDown}
                />

                {description && (
                    <DescriptionText text={description} helpUrl={helpUrl} />
                )}
            </VStack>
        </>
    );
}

export default Integer;
