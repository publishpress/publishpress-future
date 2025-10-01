import { createStore } from './data';
import { FutureActionPanelBlockEditor } from './components';
import { select } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { useEffect } from '@wordpress/element';

const {
    actionsSelectOptions,
    is12Hour,
    timeFormat,
    startOfWeek,
    strings,
    taxonomyName,
    postTypeDefaultConfig,
    defaultDate,
    statusesSelectOptions,
    hideCalendarByDefault,
    hiddenFields,
    wpTimezone
} = window.publishpressFutureBlockEditorConfig;

const storeName = 'publishpress-future/future-action';

const BlockEditorFutureActionPlugin = () => {

    useEffect(() => {
        createStore({
            name: storeName,
            defaultState: {
                postId: publishpressFutureBlockEditorConfig.postId,
                autoEnable: postTypeDefaultConfig.autoEnable,
                action: postTypeDefaultConfig.expireType,
                newStatus: postTypeDefaultConfig.newStatus,
                date: defaultDate,
                taxonomy: postTypeDefaultConfig.taxonomy,
                terms: postTypeDefaultConfig.terms,
            }
        });
    }, []);

    return (
        <FutureActionPanelBlockEditor
            postType={select('core/editor').getCurrentPostType()}
            isCleanNewPost={select('core/editor').isCleanNewPost()}
            actionsSelectOptions={actionsSelectOptions}
            statusesSelectOptions={statusesSelectOptions}
            is12Hour={is12Hour}
            timeFormat={timeFormat}
            startOfWeek={startOfWeek}
            storeName={storeName}
            strings={strings}
            taxonomyName={taxonomyName}
            postTypeDefaultConfig={postTypeDefaultConfig}
            hideCalendarByDefault={hideCalendarByDefault}
            hiddenFields={hiddenFields}
            wpTimezone={wpTimezone}
        />
    );
}

registerPlugin('publishpress-future-action', {
    render: BlockEditorFutureActionPlugin
});
