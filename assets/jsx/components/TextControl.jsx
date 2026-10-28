/*
 * Copyright (c) 2025, Ramble Ventures
 */
import { Fragment } from "@wordpress/element";
import { TextControl as WPTextControl } from "@wordpress/components";
import { Spinner } from "./";
import { stripTags } from "../utils";
import { useControlA11yAttributes } from "./a11yUtils";

export const TextControl = function (props) {
    let description;

    const descriptionId = props.description ? props.name + '-description' : undefined;

    if (props.unescapedDescription) {
        // If using this option, the HTML has to be escaped before injected into the JS interface.
        description = <p className="description" id={descriptionId} dangerouslySetInnerHTML={{ __html: stripTags(props.description) }}></p>;
    } else {
        description = <p className="description" id={descriptionId}>{props.description}</p>;
    }

    useControlA11yAttributes(props.name, [descriptionId, props.ariaDescribedBy], props.isInvalid);

    const onChange = function (value) {
        if (props.onChange) {
            props.onChange(value);
        }
    };

    let className = props.className ? props.className : '';

    if (props.loading) {
        className += ' publishpress-future-loading publishpress-future-loading-input';
    }

    return (
        <Fragment>
            <div className={className}>
                <WPTextControl
                    type="text"
                    label={props.label}
                    hideLabelFromVision={props.hideLabelFromVision}
                    name={props.name}
                    id={props.name}
                    className={props.className}
                    value={props.value}
                    placeholder={props.placeholder}
                    onChange={onChange}
                />

                {props.loading && <Spinner/>}

                {description}
            </div>
        </Fragment>
    )
}
