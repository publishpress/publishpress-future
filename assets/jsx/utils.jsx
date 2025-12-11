export const compact = (array) => {
    if (!array) {
        return [];
    }

    if (! Array.isArray(array) && typeof array === 'object') {
        array = Object.values(array);
    }

    return array.filter((item) => {
        return item !== null && item !== undefined && item !== '';
    });
}

export const debugLogFactory = (config) => {
    return (description, ...message) => {
        if (console && config.isDebugEnabled) {
            console.debug('[Future]', description, ...message);
        }
    }
}

export const isGutenbergEnabled = () => {
    return document.body.classList.contains('block-editor-page');
}

export const getElementByName = (name) => {
    return document.getElementsByName(name)[0];
}

export const getFieldByName = (name, postId) => {
    return document.querySelector(`#the-list tr#post-${postId} .column-expirationdate input#future_action_${name}-${postId}`);
}

export const getFieldValueByName = (name, postId) => {
    const field = getFieldByName(name, postId);

    if (!field) {
        return null;
    }

    return field.value;
};

export const getFieldValueByNameAsArrayOfInt = (name, postId) => {
    const field = getFieldByName(name, postId);

    if (!field || !field.value) {
        return [];
    }

    if (typeof field.value === 'number') {
        field.value = field.value.toString();
    }

    return field.value.split(',').map(term => parseInt(term));
};

export const getFieldValueByNameAsBool = (name, postId) => {
    const field = getFieldByName(name, postId);

    if (!field) {
        return false;
    }

    return field.value === '1' || field.value === 'true';
}

export const getActionSettingsFromColumnData = (postId) => {
    const columnData = document.querySelector(`#post-expire-column-${postId}`);

    if (!columnData) {
        return {};
    }

    return {
        enabled: columnData.dataset.actionEnabled === '1',
        action: columnData.dataset.actionType,
        date: columnData.dataset.actionDate,
        dateUnix: columnData.dataset.actionDateUnix,
        taxonomy: columnData.dataset.actionTaxonomy,
        terms: columnData.dataset.actionTerms,
        newStatus: columnData.dataset.actionNewStatus,
    };
}

/**
 * This function is used to determine if a value is a number, including strings.
 *
 * @param {*} value
 * @returns
 */
export const isNumber = (value) => {
    return !isNaN(value);
}

export function stripTags(string) {
    if (typeof string !== 'string') {
        return '';
    }

    const div = document.createElement('div');
    div.textContent = string;
    return div.innerHTML;
}
