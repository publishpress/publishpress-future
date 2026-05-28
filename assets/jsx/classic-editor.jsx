import { FutureActionPanelClassicEditor } from './components';
import { createStore } from './data';
import { isGutenbergEnabled } from './utils';
import { select } from '@wordpress/data';
import { createRoot } from '@wordpress/element';

const {
    postType,
    isNewPost,
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
} = window.publishpressFutureClassicEditorConfig;

const gutenbergEnabled = isGutenbergEnabled();
const container = document.getElementById('publishpress-future-classic-editor');

if (! gutenbergEnabled) {
    const storeName = 'publishpress-future/future-action';

    if (!select(storeName)) {
        createStore({
            name: storeName,
            defaultState: {
                postId: document.getElementById('post_ID') ? parseInt(document.getElementById('post_ID').value, 10) : 0,
                autoEnable: postTypeDefaultConfig.autoEnable,
                action: postTypeDefaultConfig.expireType,
                newStatus: postTypeDefaultConfig.newStatus,
                date: defaultDate,
                taxonomy: postTypeDefaultConfig.taxonomy,
                terms: postTypeDefaultConfig.terms,
            }
        });
    }

    if (container) {
        const component = (
            <FutureActionPanelClassicEditor
                storeName={storeName}
                postType={postType}
                isNewPost={isNewPost}
                actionsSelectOptions={actionsSelectOptions}
                statusesSelectOptions={statusesSelectOptions}
                is12Hour={is12Hour}
                timeFormat={timeFormat}
                startOfWeek={startOfWeek}
                strings={strings}
                taxonomyName={taxonomyName}
                hideCalendarByDefault={hideCalendarByDefault}
                hiddenFields={hiddenFields}
                wpTimezone={wpTimezone}
            />
        );

        createRoot(container).render(component);
    }
}
