/*
 * Copyright (c) 2025, Ramble Ventures
 */
import { Fragment } from "@wordpress/element";
import { SelectControl as WPSelectControl } from "@wordpress/components";
import { useControlA11yAttributes } from "./a11yUtils";

export const SelectControl = function (props) {
    const onChange = (value) => {
        props.onChange(value);
    };

    const descriptionId = props.description ? props.name + '-description' : undefined;

    useControlA11yAttributes(props.name, [descriptionId, props.ariaDescribedBy], props.isInvalid);

    return (
        <Fragment>
            {props.options.length === 0 && (
                <div>{props.noItemFoundMessage}</div>
            )}

            {props.options.length > 0 && (
                <WPSelectControl
                    label={props.label}
                    hideLabelFromVision={props.hideLabelFromVision}
                    name={props.name}
                    id={props.name}
                    className={props.className}
                    value={props.selected}
                    onChange={onChange}
                    data-data={props.data}
                    options={props.options}
                />
            )}

            {props.children}

            <p className="description" id={descriptionId}>{props.description}</p>
        </Fragment>
    )
}
