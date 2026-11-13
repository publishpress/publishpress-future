/*
 * Copyright (c) 2025, Ramble Ventures
 */
import { __ } from "@wordpress/i18n";

export const Spinner = function (props) {
    const label = props.label || __('Loading…', 'post-expirator');

    return (
        <span className="publishpress-future-spinner" role="status">
            {/* The text goes last so the CSS div:nth-child() animation delays still match. */}
            <div aria-hidden="true"></div><div aria-hidden="true"></div><div aria-hidden="true"></div><div aria-hidden="true"></div>
            <span className="screen-reader-text">{label}</span>
        </span>
    );
}
