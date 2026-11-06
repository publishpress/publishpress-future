/*
 * Copyright (c) 2025, Ramble Ventures
 */
import { useEffect, useRef } from "@wordpress/element";
import { speak } from "@wordpress/a11y";

/**
 * Set aria-describedby / aria-invalid on a control rendered by a WP component
 * that overrides those attributes (SelectControl, TextControl only set
 * aria-describedby for their own `help` prop).
 *
 * @param {string}           controlId     DOM id of the control.
 * @param {string|string[]}  describedByIds Ids of the describing elements. Falsy entries are ignored.
 * @param {boolean}          isInvalid     Whether the control has a validation error.
 */
export const useControlA11yAttributes = (controlId, describedByIds, isInvalid = false) => {
    const ids = (Array.isArray(describedByIds) ? describedByIds : [describedByIds]).filter(Boolean).join(' ');

    useEffect(() => {
        if (!controlId) {
            return;
        }

        const element = document.getElementById(controlId);

        if (!element) {
            return;
        }

        // Keep any aria-describedby the control already had (from PHP or the WP component).
        if (!element.hasAttribute('data-pe-base-describedby')) {
            element.setAttribute('data-pe-base-describedby', element.getAttribute('aria-describedby') || '');
        }

        const baseIds = element.getAttribute('data-pe-base-describedby');
        const allIds = [baseIds, ids].filter(Boolean).join(' ');

        if (allIds) {
            element.setAttribute('aria-describedby', allIds);
        } else {
            element.removeAttribute('aria-describedby');
        }

        if (isInvalid) {
            element.setAttribute('aria-invalid', 'true');
        } else {
            element.removeAttribute('aria-invalid');
        }
    });
};

/**
 * Announce a message through the wp.a11y live region when it changes.
 *
 * @param {string}  message   Message to announce. Nothing is announced when empty.
 * @param {string}  politeness 'polite' or 'assertive'.
 * @param {number}  delay     Debounce delay in milliseconds.
 */
export const useSpeakOnChange = (message, politeness = 'polite', delay = 0) => {
    const lastSpoken = useRef('');

    useEffect(() => {
        if (!message) {
            lastSpoken.current = '';
            return;
        }

        if (message === lastSpoken.current) {
            return;
        }

        const timer = setTimeout(() => {
            lastSpoken.current = message;
            speak(message, politeness);
        }, delay);

        return () => clearTimeout(timer);
    }, [message, politeness, delay]);
};
