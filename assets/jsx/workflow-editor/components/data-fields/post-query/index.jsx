import { FormTokenField, RadioControl, PanelRow } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useEffect, useMemo, useCallback } from "@wordpress/element";
import { InlineMultiSelect } from "../../inline-multi-select";
import { __experimentalVStack as VStack } from "@wordpress/components";


export function PostQuery({
    name,
    label,
    defaultValue,
    onChange,
    settings,
    variables,
}) {
    const postTypes = futureWorkflowEditor.postTypes;
    const postStatuses = futureWorkflowEditor.postStatuses;
    const postTermsOptions = futureWorkflowEditor.postTerms;
    const onChangeSetting = ({ settingName, value }) => {
        const newValue = { ...defaultValue };
        newValue[settingName] = value;

        if (onChange) {
            onChange(name, newValue);
        }
    }

    const acceptsInput = settings && settings?.acceptsInput === true;
    const isPostTypeRequired = settings && settings?.isPostTypeRequired === true;
    const defaultPostSource =acceptsInput ? 'input' : 'custom';
    const showCustomQueryFields = defaultValue?.postSource === 'custom' || ! acceptsInput;
    const hidePostStatus = settings && settings?.hidePostStatus === true;

    // Set default setting
    useEffect(() => {
        if (!defaultValue) {
            defaultValue = {
                postSource: defaultPostSource,
                postType: [],
                postId: [],
                postStatus: [],
                postAuthor: [],
                postTerms: [],
            };

            onChangeSetting({ settingName: "postSource", value: defaultPostSource });
        }
    }, []);

    const postTypeFieldLabel = isPostTypeRequired ? __('Post Type', 'post-expirator') + ' *' : __('Post Type', 'post-expirator');

    const descriptions = {
        postType: settings?.postTypeDescription || null,
        postId: settings?.postIdDescription || null,
        postStatus: settings?.postStatusDescription || null,
        postAuthor: settings?.postAuthorDescription || null,
        postTerms: settings?.postTermsDescription || null,
    };

    const injectUserVariablesIntoPostAuthors = useCallback(() => {
        let userVariables = variables.filter(variable => variable.type === 'user');
        userVariables = userVariables.map(variable => ({
            label: variable.label,
            value: `{{${variable.name}.id}}`,
        }));

        const postAuthors = [...userVariables, ...futureWorkflowEditor.postAuthors];
        return postAuthors;
    }, [variables]);

    const postAuthorOptions = useMemo(() => injectUserVariablesIntoPostAuthors(), [injectUserVariablesIntoPostAuthors]);

    return (
        <>
            <VStack>
                {acceptsInput && (
                    <RadioControl
                        label={__('Post selection', 'post-expirator')}
                        selected={defaultValue?.postSource || defaultPostSource}
                        options={[
                            { label: __('Post received as input', 'post-expirator'), value: 'input' },
                            { label: __('Custom query', 'post-expirator'), value: 'custom' },
                        ]}
                        onChange={(value) => onChangeSetting({ settingName: "postSource", value })}
                    />
                )}

                {/* More than one post input? */}
                {showCustomQueryFields && (
                    <>
                        <InlineMultiSelect
                            label={postTypeFieldLabel}
                            value={defaultValue?.postType || []}
                            suggestions={postTypes}
                            expandOnFocus={true}
                            autoSelectFirstMatch={true}
                            onChange={(value) => onChangeSetting({ settingName: "postType", value })}
                        />

                        {descriptions?.postType && (
                            <p className="description">{descriptions.postType}</p>
                        )}

                        <FormTokenField
                            label={__('Post ID', 'post-expirator')}
                            value={defaultValue?.postId || []}
                            onChange={(value) => onChangeSetting({ settingName: "postId", value })}
                        />

                        {descriptions?.postId && (
                            <p className="description">{descriptions.postId}</p>
                        )}

                        {!hidePostStatus && (
                            <>
                                <InlineMultiSelect
                                    label={__('Post Status', 'post-expirator')}
                                    value={defaultValue?.postStatus || []}
                                    suggestions={postStatuses}
                                    expandOnFocus={true}
                                    autoSelectFirstMatch={true}
                                    onChange={(value) => onChangeSetting({ settingName: "postStatus", value })}
                                />

                                {descriptions?.postStatus && (
                                    <p className="description">{descriptions.postStatus}</p>
                                )}
                            </>
                        )}

                        <InlineMultiSelect
                            label={__('Post Author', 'post-expirator')}
                            value={defaultValue?.postAuthor || []}
                            suggestions={postAuthorOptions}
                            expandOnFocus={true}
                            autoSelectFirstMatch={true}
                            onChange={(value) => onChangeSetting({ settingName: "postAuthor", value })}
                        />

                        {descriptions?.postAuthor && (
                            <p className="description">{descriptions.postAuthor}</p>
                        )}

                        <InlineMultiSelect
                            label={__('Post Terms', 'post-expirator')}
                            value={defaultValue?.postTerms || []}
                            suggestions={postTermsOptions}
                            expandOnFocus={true}
                            autoSelectFirstMatch={true}
                            onChange={(value) => onChangeSetting({ settingName: "postTerms", value })}
                        />

                        {descriptions?.postTerms && (
                            <p className="description">{descriptions.postTerms}</p>
                        )}


                        <PanelRow>
                            <p className="description">
                                {__('Separate multiple values with commas or Enter key.', 'post-expirator')}
                            </p>
                        </PanelRow>

                        {isPostTypeRequired && (
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

export default PostQuery;
