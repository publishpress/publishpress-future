/*
 * Copyright (c) 2025, Ramble Ventures
 */
import { Fragment } from "@wordpress/element";

/**
 * A settings table row.
 *
 * Pass `id` with the id of the row's control so the row label is
 * programmatically associated with it. When the row holds a group of
 * controls (checkbox list, radio group), pass `labelId` instead and use it as
 * `aria-labelledby` on the group.
 */
export const SettingRow = function (props) {
    const labelProps = {};

    if (props.id) {
        labelProps.htmlFor = props.id;
    }

    if (props.labelId) {
        labelProps.id = props.labelId;
    }

    return (
        <tr valign="top">
            <th scope="row">
                {props.id ? (
                    <label {...labelProps}>{props.label}</label>
                ) : (
                    <span className="pe-setting-row-label" {...labelProps}>{props.label}</span>
                )}
            </th>
            <td>
                {props.children}
            </td>
        </tr>
    )
}
