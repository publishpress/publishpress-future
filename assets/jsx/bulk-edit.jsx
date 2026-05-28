import { FutureActionPanelBulkEdit } from './components';
import { createStore } from './data';
import { createRoot } from '@wordpress/element';
import { select, dispatch } from '@wordpress/data';

const { inlineEditPost } = window;

const {
    postTypeDefaultConfig,
    defaultDate,
    postType,
    isNewPost,
    actionsSelectOptions,
    is12Hour,
    timeFormat,
    startOfWeek,
    strings,
    taxonomyName,
    nonce,
    statusesSelectOptions,
    hideCalendarByDefault,
    hiddenFields,
    wpTimezone
} = window.publishpressFutureBulkEditConfig;

const storeName = 'publishpress-future/future-action-bulk-edit';
const delayToUnmountAfterSaving = 1000;

// We create a copy of the WP inline set bulk function
const wpInlineSetBulk = inlineEditPost.setBulk;
const wpInlineEditRevert = inlineEditPost.revert;


/**
 * We override the function with our own code so we can detect when
 * the inline edit row is displayed to recreate the React component.
 */
inlineEditPost.setBulk = function (id) {
    // Call the original WP edit function.
    wpInlineSetBulk.apply(this, arguments);

    if (select(storeName)) {
        dispatch(storeName).setPostId(0);
        dispatch(storeName).setAction(postTypeDefaultConfig.expireType);
        dispatch(storeName).setDate(postTypeDefaultConfig.defaultDate);
        dispatch(storeName).setTaxonomy(postTypeDefaultConfig.taxonomy);
        dispatch(storeName).setTerms(postTypeDefaultConfig.terms);
        dispatch(storeName).setChangeAction('no-change');
    } else {
        createStore({
            name: storeName,
            defaultState: {
                action: postTypeDefaultConfig.expireType,
                newStatus: postTypeDefaultConfig.newStatus,
                date: defaultDate,
                taxonomy: postTypeDefaultConfig.taxonomy,
                terms: postTypeDefaultConfig.terms,
                changeAction: 'no-change',
            }
        });
    }

    const container = document.getElementById("publishpress-future-bulk-edit");

    if (! container) {
        return;
    }

    const root = createRoot(container);

    const saveButton = document.querySelector('#bulk_edit');
    if (saveButton) {
        saveButton.onclick = function() {
            setTimeout(() => {
                root.unmount();
            }, delayToUnmountAfterSaving);
        };
    }

    const component = (
        <FutureActionPanelBulkEdit
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
            nonce={nonce}
            hideCalendarByDefault={hideCalendarByDefault}
            hiddenFields={hiddenFields}
            wpTimezone={wpTimezone}
        />
    );

    root.render(component);

    inlineEditPost.revert = function () {
        root.unmount();

        // Call the original WP revert function.
        wpInlineEditRevert.apply(this, arguments);
    };
};
