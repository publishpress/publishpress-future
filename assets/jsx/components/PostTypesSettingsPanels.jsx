/*
 * Copyright (c) 2025, Ramble Ventures
 */

import { PostTypeSettingsPanel } from "./";
import { useState, useEffect } from "@wordpress/element";
import {
    SelectControl,
    __experimentalHStack as HStack
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export const PostTypesSettingsPanels = function (props) {
    const [currentTab, setCurrentTab] = useState(Object.keys(props.settings)[0]);
    const [selectedPostType, setSelectedPostType] = useState(null);
    const isPro = props.isPro;

    useEffect(() => {
        // Get post type from URL on component mount
        const urlParams = new URLSearchParams(window.location.search);
        const postTypeParam = urlParams.get('post_type');

        if (postTypeParam && props.settings[postTypeParam]) {
            setSelectedPostType(postTypeParam);
            setCurrentTab(postTypeParam);
        }
    }, []);

    let panels = [];

    for (const [postType, postTypeSettings] of Object.entries(props.settings)) {
        panels.push(
            <PostTypeSettingsPanel
                legend={postTypeSettings.label}
                text={props.text}
                isPro={isPro}
                postType={postType}
                postTypeLabel={postTypeSettings.label}
                settings={postTypeSettings}
                expireTypeList={props.expireTypeList}
                taxonomiesList={props.taxonomiesList[postType]}
                statusesList={props.statusesList[postType]}
                key={`${postType}-panel`}
                onDataIsValid={props.onDataIsValid}
                onDataIsInvalid={props.onDataIsInvalid}
                onValidationStarted={props.onValidationStarted}
                onValidationFinished={props.onValidationFinished}
                isVisible={currentTab === postType}
            />
        );
    }

    const onSelectPostType = (postType) => {
        setSelectedPostType(postType);
        setCurrentTab(postType);

        // Update URL with the selected post type
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('post_type', postType);
        window.history.pushState({}, '', newUrl);
    }

    const postTypeOptions = Object.keys(props.settings).map((postType) => ({
        label: props.settings[postType].label,
        value: postType,
    }));

    return (
        <div>
            <div className="pe-post-type-select">
                <HStack
                    style={{
                        justifyContent: 'flex-start',
                        alignItems: 'stretch',
                        background: '#fff',
                        padding: '10px',
                        border: '1px solid #ccc',
                        marginBottom: '10px'
                    }}
                >
                    <label style={{ lineHeight: '33px' }}>{__('Select a post type to edit:', 'post-expirator')}</label>
                    <SelectControl
                        value={selectedPostType}
                        options={postTypeOptions}
                        onChange={onSelectPostType}
                    />
                </HStack>
            </div>

            {panels}
        </div>
    );
}

