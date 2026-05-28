import { FutureActionPanelQuickEdit } from './components';
import { createStore } from './data';
import { getActionSettingsFromColumnData } from './utils';
import { createRoot } from '@wordpress/element';
import { select, dispatch } from '@wordpress/data';

const { inlineEditPost } = window;

const {
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
} = window.publishpressFutureQuickEditConfig;

const storeName = 'publishpress-future/future-action-quick-edit';
const delayToUnmountAfterSaving = 1000;

// We create a copy of the WP inline edit post function
const wpInlineEdit = inlineEditPost.edit;
const wpInlineEditRevert = inlineEditPost.revert;

const getPostIdFromButton = (id) => {
    // If id is a string or a number, return it directly
    if (typeof id === 'string' || typeof id === 'number') {
        return id;
    }

    // Otherwise, assume it's an HTML element and extract the post ID
    const trElement = id.closest('tr');
    const trId = trElement.id;
    const postId = trId.split('-')[1];

    return postId;
}

/**
 * We override the function with our own code so we can detect when
 * the inline edit row is displayed to recreate the React component.
 */
inlineEditPost.edit = function (button, id) {
    // Call the original WP edit function.
    wpInlineEdit.apply(this, arguments);

    const postId = getPostIdFromButton(button);
    const data = getActionSettingsFromColumnData(postId);

    if (!data) {
        return;
    }

    const enabled = data.enabled;
    const action = data.action;
    const date = data.date;
    const taxonomy = data.taxonomy;
    const newStatus = data.newStatus;

    let terms = data.terms;

    if (typeof terms === 'string'){
        terms = terms.split(',');
    }

    // if store exists, update the state. Otherwise, create it.
    if (select(storeName)) {
        dispatch(storeName).setPostId(postId);
        dispatch(storeName).setEnabled(enabled);
        dispatch(storeName).setAction(action);
        dispatch(storeName).setDate(date);
        dispatch(storeName).setTaxonomy(taxonomy);
        dispatch(storeName).setTerms(terms);
        dispatch(storeName).setNewStatus(newStatus);
    } else {
        createStore({
            name: storeName,
            defaultState: {
                postId: postId,
                autoEnable: enabled,
                action: action,
                date: date,
                taxonomy: taxonomy,
                terms: terms,
                newStatus: newStatus,
            }
        });
    }

    const container = document.getElementById("publishpress-future-quick-edit");

    if (!container) {
        return;
    }

    const root = createRoot(container);

    const saveButton = document.querySelector('.inline-edit-save .save');
    if (saveButton) {
        saveButton.onclick = function() {
            setTimeout(() => {
                root.unmount();
            }, delayToUnmountAfterSaving);
        };
    }

    const component = (
        <FutureActionPanelQuickEdit
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
