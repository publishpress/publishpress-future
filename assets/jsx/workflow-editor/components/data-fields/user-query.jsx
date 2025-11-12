import { FormTokenField, RadioControl, PanelRow } from "@wordpress/components";
import { __ } from "@publishpress/i18n";
import { useEffect } from "@wordpress/element";
import { InlineMultiSelect } from "../inline-multi-select";
import { __experimentalVStack as VStack } from "@wordpress/components";


export function UserQuery({
    name,
    label,
    defaultValue,
    onChange,
    settings,
}) {
    const userRoles = futureWorkflowEditor.userRoles;

    const onChangeSetting = ({ settingName, value }) => {
        const newValue = { ...defaultValue };
        newValue[settingName] = value;

        if (onChange) {
            onChange(name, newValue);
        }
    }

    const acceptsInput = settings && settings?.acceptsInput === true;
    const isUserRoleRequired = settings && settings?.isUserRoleRequired === true;
    const defaultUserSource = acceptsInput ? 'input' : 'custom';
    const showCustomQueryFields = defaultValue?.userSource === 'custom' || ! acceptsInput;

    // Set default setting
    useEffect(() => {
        if (!defaultValue) {
            defaultValue = {
                userSource: defaultUserSource,
                userRole: [],
                userRoleAfter: [],
                userId: [],
            };

            onChangeSetting({ settingName: "userSource", value: defaultUserSource });
        }
    }, []);

    let userRoleFieldLabel = settings?.labels?.userRole || __('User Role', 'post-expirator');
    userRoleFieldLabel = isUserRoleRequired ? userRoleFieldLabel + ' *' : userRoleFieldLabel;

    let userRoleAfterFieldLabel = settings?.labels?.userRoleAfter || null;

    const descriptions = {
        userRole: settings?.userRoleDescription || null,
        userRoleAfter: settings?.userRoleAfterDescription || null,
        userId: settings?.userIdDescription || null,
    };

    return (
        <>
            <VStack>
                {acceptsInput && (
                    <RadioControl
                        label={__('User selection', 'post-expirator')}
                        selected={defaultValue?.userSource || defaultUserSource}
                        options={[
                            { label: __('User received as input', 'post-expirator'), value: 'input' },
                            { label: __('Custom query', 'post-expirator'), value: 'custom' },
                        ]}
                        onChange={(value) => onChangeSetting({ settingName: "userSource", value })}
                    />
                )}

                {/* More than one post input? */}
                {showCustomQueryFields && (
                    <>
                        <InlineMultiSelect
                            label={userRoleFieldLabel}
                            value={defaultValue?.userRole || []}
                            suggestions={userRoles}
                            expandOnFocus={true}
                            autoSelectFirstMatch={true}
                            onChange={(value) => onChangeSetting({ settingName: "userRole", value })}
                        />

                        {descriptions?.userRole && (
                            <p className="description">{descriptions.userRole}</p>
                        )}

                        {userRoleAfterFieldLabel && (
                            <>
                                <InlineMultiSelect
                                    label={userRoleAfterFieldLabel}
                                    value={defaultValue?.userRoleAfter || []}
                                    suggestions={userRoles}
                                    expandOnFocus={true}
                                    autoSelectFirstMatch={true}
                                    onChange={(value) => onChangeSetting({ settingName: "userRoleAfter", value })}
                                />

                                {descriptions?.userRoleAfter && (
                                    <p className="description">{descriptions.userRoleAfter}</p>
                                )}
                            </>
                        )}

                        <FormTokenField
                            label={__('User ID', 'post-expirator')}
                            value={defaultValue?.userId || []}
                            onChange={(value) => onChangeSetting({ settingName: "userId", value })}
                        />

                        {descriptions?.userId && (
                            <p className="description">{descriptions.userId}</p>
                        )}

                        <PanelRow>
                            <p className="description">
                                {__('Separate multiple values with commas or Enter key.', 'post-expirator')}
                            </p>
                        </PanelRow>

                        {isUserRoleRequired && (
                            <PanelRow>
                                <p className="description">{__('* Required field', 'post-expirator')}</p>
                            </PanelRow>
                        )}


                    </>
                )}
            </VStack>
        </>
    );
}

export default UserQuery;
