import { __ } from "@wordpress/i18n";
import { useCallback, useMemo } from "@wordpress/element";
import {
    __experimentalVStack as VStack,
} from "@wordpress/components";
import DateOffset from "../date-offset";
import ToggleInlineSetting from "./toggle-inline-setting";

const formatDate = (date) => {
    if (date) {
        const dateFormat = futureWorkflowEditor.dateFormat;
        const timeFormat = futureWorkflowEditor.timeFormat;

        return wp.date.format(`${dateFormat} ${timeFormat}`, new Date(date));
    }

    return '';
}

export const PostDateControl = ({
    name,
    label,
    defaultValue,
    onChange,
    variables = [],
    checkboxLabel,
    onClosePopover
}) => {
    const defaultSpecificDate = new Date();
    defaultSpecificDate.setDate(defaultSpecificDate.getDate() + 3);

    defaultValue = {
        update: false,
        dateStrategy: "date",
        dateSource: "calendar",
        specificDate: defaultSpecificDate,
        dateOffset: "+7 days",
        ...defaultValue
    };

    const onChangeSetting = useCallback(({ settingName, value }) => {
        const newValue = { ...defaultValue, ...value };

        if (onChange) {
            onChange(name, newValue);
        }
    }, [defaultValue, name, onChange]);

    const valuePreview = () => {
        if (defaultValue.update) {
            if (defaultValue?.dateStrategy === "date" || defaultValue?.dateStrategy === "offset") {
                let previewText = '';

                if (defaultValue?.specificDate && defaultValue?.dateSource === 'calendar') {
                    previewText = formatDate(defaultValue.specificDate);
                }

                if (defaultValue.dateSource === 'event') {
                    previewText = __('When the trigger is activated', 'post-expirator');
                }

                if (defaultValue.dateSource === 'step') {
                    previewText = __('When the step is activated', 'post-expirator');
                }

                if (defaultValue.dateSource === 'custom') {
                    const expression = defaultValue.customDateSource?.expression;

                    if (expression) {
                        previewText = expression;
                    } else {
                        previewText = __('Custom date source', 'post-expirator');
                    }
                }

                if (defaultValue.dateStrategy === "offset") {
                    previewText += `, ${defaultValue.dateOffset}`;
                }

                return previewText;
            }
        }

        return __('Do not update', 'post-expirator');
    };

    return (
        <>
            <ToggleInlineSetting
                name={name}
                label={label}
                defaultValue={defaultValue}
                valuePreview={valuePreview()}
                checkboxLabel={checkboxLabel}
                onChange={onChange}
                onUncheckUpdate={() => onChange(name, null)}
            >
                <VStack>
                    <DateOffset
                        name={name}
                        label={label}
                        defaultValue={defaultValue}
                        onChange={(settingName, value) => onChangeSetting({ settingName: name, value })}
                        variables={variables}
                        settings={{
                            hideDateStrategy: ['now'],
                        }}
                    />
                </VStack>
            </ToggleInlineSetting>
        </>
    )
}

export default PostDateControl;
