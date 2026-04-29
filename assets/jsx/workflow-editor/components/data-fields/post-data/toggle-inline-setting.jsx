import { __ } from "@wordpress/i18n";
import {
    useCallback
} from "@wordpress/element";
import {
    CheckboxControl
} from "@wordpress/components";
import InlineSetting from "../inline-setting";

export const ToggleInlineSetting = ({
    name,
    label,
    valuePreview,
    onClosePopover,
    checkboxLabel,
    defaultValue,
    onChange,
    children,
    onUncheckUpdate,
    isLoading
}) => {
    const onChangeCheckbox = useCallback((value) => {
        defaultValue.update = value;
        onChange(name, defaultValue);

        if ((! value) && onUncheckUpdate) {
            onUncheckUpdate();
        }
    }, [onChange, name, defaultValue, onUncheckUpdate]);

    return (
        <>
            <InlineSetting
                name={name}
                label={label}
                valuePreview={valuePreview}
                onClosePopover={onClosePopover}
                isLoading={isLoading}
                children={(
                    <>
                        <CheckboxControl
                            label={checkboxLabel}
                            checked={defaultValue.update}
                            onChange={onChangeCheckbox}
                        />

                        {defaultValue.update && (
                            <>
                                {children}
                            </>
                        )}
                    </>
                )}
            />
        </>
    )
}

export default ToggleInlineSetting;
