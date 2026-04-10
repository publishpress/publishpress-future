import {
    __ as wp__,
    sprintf as wpSprintf,
    isRTL as wpIsRTL,
    _n as wpN,
    _x as wpX,
    _nx as wpNx,
    _n_noop as wpNNoop,
    _nx_noop as wpNxNoop,
} from '@wordpress/i18n';


const data = (() => {
    const free = window.publishpressI18nConfig?.data || {};
    const pro = window.publishpressI18nProConfig?.data || {};

    const merged = {};

    const domains = new Set([
        ...Object.keys(free.locale_data || {}),
        ...Object.keys(pro.locale_data || {}),
    ]);

    domains.forEach((domain) => {
        merged[domain] = {
            ...((free.locale_data || {})[domain] || {}),
            ...((pro.locale_data || {})[domain] || {}),
        };
    });

    return {
        locale_data: merged
    };
})();

export const __ = (text, domain = null) => {
    if (!domain) {
        return wp__(text);
    }

    if (domain && data.locale_data?.[domain]?.[text] && data.locale_data[domain][text][0]) {
        return data.locale_data[domain][text][0];
    }

    return wp__(text, domain);
};


export const sprintf = (text, ...args) => {
    return wpSprintf(text, ...args);
};

export const isRTL = () => {
    return wpIsRTL();
};

export const _n = (single, plural, number, domain = null) => {
    single = __(single, domain);
    plural = __(plural, domain);

    return wpN(single, plural, number, domain);
};

export const _x = (text, context, domain = null) => {
    text = __(text, domain);

    return wpX(text, context, domain);
};

export const _nx = (single, plural, number, context, domain = null) => {
    single = __(single, domain);
    plural = __(plural, domain);

    return wpNx(single, plural, number, context, domain);
};

export const _n_noop = (single, plural, domain = null) => {
    single = __(single, domain);
    plural = __(plural, domain);

    return wpNNoop(single, plural, domain);
};

export const _nx_noop = (single, plural, context, domain = null) => {
    single = __(single, domain);
    plural = __(plural, domain);

    return wpNxNoop(single, plural, context, domain);
};

if (typeof window.publishpress === 'undefined') {
    window.publishpress = {};
}

if (typeof window.publishpress.i18n === 'undefined') {
    window.publishpress.i18n = {};
}

window.publishpress.i18n.__ = __;
window.publishpress.i18n.sprintf = sprintf;
window.publishpress.i18n.isRTL = isRTL;
window.publishpress.i18n._n = _n;
window.publishpress.i18n._x = _x;
window.publishpress.i18n._nx = _nx;
window.publishpress.i18n._n_noop = _n_noop;
window.publishpress.i18n._nx_noop = _nx_noop;

