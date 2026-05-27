import { __ } from "@wordpress/i18n";
import {
    useMemo,
    useCallback
} from "@wordpress/element";
import {
    __experimentalVStack as VStack,
    RadioControl,
    CheckboxControl,
    ExternalLink
} from "@wordpress/components";
import ToggleInlineSetting from "./toggle-inline-setting";

export const PostDiscussionControl = ({
    name,
    label,
    defaultValue,
    onChange,
    checkboxLabel
}) => {
    defaultValue = {
        update: false,
        commentStatus: "closed",
        pingStatus: "closed",
        ...defaultValue
    };

    const valuePreview = useMemo(() => {
        const { commentStatus, pingStatus } = defaultValue;

        if (!defaultValue.update) {
            return __('Do not update', 'post-expirator');
        }

        const statusMap = {
            'open:closed': __('Comments only', 'post-expirator'),
            'closed:closed': __('Closed', 'post-expirator'),
            'closed:open': __('Pings only', 'post-expirator'),
            'open:open': __('Open', 'post-expirator')
        };

        const normalizedPingStatus = pingStatus || 'closed';

        const key = `${commentStatus}:${normalizedPingStatus}`;

        return statusMap[key] || __('Changed...', 'post-expirator');
    }, [defaultValue]);

    const radioOptions = [
        {
            label: __("Open", "post-expirator"),
            value: "open",
            description: __("Visitors can add new comments and replies.", "post-expirator"),
        },
        {
            label: __("Closed", "post-expirator"),
            value: "closed",
            description: __("Visitors can not add new comments or replies. Existing comments remain visible.", "post-expirator"),
        },
    ];

    const onChangeRadio = useCallback((value) => {
        onChange(name, {
            update: true,
            commentStatus: value,
            pingStatus: defaultValue.pingStatus
        });
    }, [onChange, name, defaultValue]);

    const onChangePingbacks = useCallback((value) => {
        onChange(name, {
            update: true,
            pingStatus: value ? 'open' : 'closed',
            commentStatus: defaultValue.commentStatus
        });
    }, [onChange, name, defaultValue]);

    return (
        <>
            <ToggleInlineSetting
                name={name}
                label={label}
                valuePreview={valuePreview}
                defaultValue={defaultValue}
                onChange={onChange}
                checkboxLabel={checkboxLabel}
                onUncheckUpdate={() => onChange(name, null)}
            >
                <VStack>
                    <RadioControl
                        options={radioOptions}
                        selected={defaultValue.commentStatus}
                        onChange={onChangeRadio}
                    />

                    <CheckboxControl
                        label={__("Enable pinbacks & trackbacks", "post-expirator")}
                        checked={defaultValue.pingStatus === 'open'}
                        onChange={onChangePingbacks}
                    />
                    <ExternalLink
                        href="https://wordpress.org/documentation/article/trackbacks-and-pingbacks/"
                    >
                        {__("Learn more about pinbacks & trackbacks", "post-expirator")}
                    </ExternalLink>
                </VStack>
            </ToggleInlineSetting>
        </>
    )
}

export default PostDiscussionControl;
