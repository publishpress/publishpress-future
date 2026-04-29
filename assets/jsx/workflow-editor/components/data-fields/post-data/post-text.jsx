import { __ } from "@wordpress/i18n";
import {
    useMemo
} from "@wordpress/element";
import {
    __experimentalVStack as VStack,
} from "@wordpress/components";
import ToggleInlineSetting from "./toggle-inline-setting";
import { ExpressionBuilder } from "../expression-builder";

export const PostTextControl = ({
    name,
    label,
    defaultValue,
    onChange,
    variables = [],
    checkboxLabel
}) => {
    defaultValue = {
        expression: "",
        update: false,
        ...defaultValue
    };

    const valuePreview = useMemo(() => {
        if (!defaultValue.update) {
            return __('Do not update', 'post-expirator');
        }

        if (defaultValue.expression === '') {
            return __('Clear content', 'post-expirator');
        }

        return defaultValue.expression;
    }, [defaultValue]);

    return (
        <>
            <ToggleInlineSetting
                name={name}
                label={label}
                valuePreview={valuePreview}
                defaultValue={defaultValue}
                checkboxLabel={checkboxLabel}
                onChange={onChange}
                onUncheckUpdate={() => onChange(name, null)}
            >
                <VStack>
                    <ExpressionBuilder
                        name={name}
                        label={label}
                        defaultValue={defaultValue}
                        onChange={(settingName, value) => onChange(name, value)}
                        variables={variables}
                    />
                </VStack>
            </ToggleInlineSetting>
        </>
    )
}

export default PostTextControl;
