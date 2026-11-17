/*
 * Copyright (c) 2025, Ramble Ventures
 */

export const TrueFalseControl = function (props) {
    const { Fragment } = wp.element;

    const onChange = (e) => {
        if (props.onChange) {
            props.onChange(
                e.target.value === props.trueValue && jQuery(e.target).is(':checked')
            );
            // Check only the true radio... using the field name? or directly the ID
        }
    };

    const groupLabel = props.label || props.legend;

    return (
        <fieldset className="pp-a11y-fieldset" style={{ border: 0, margin: 0, padding: 0, minWidth: 0 }}>
            {groupLabel && (
                <legend className="screen-reader-text">{groupLabel}</legend>
            )}
            <input
                type="radio"
                name={props.name}
                id={props.name + '-true'}
                value={props.trueValue}
                defaultChecked={props.selected}
                onChange={onChange}
            />

            <label htmlFor={props.name + '-true'}>{props.trueLabel}</label>
            &nbsp;&nbsp;
            <input
                type="radio"
                name={props.name}
                defaultChecked={!props.selected}
                id={props.name + '-false'}
                value={props.falseValue}
                onChange={onChange}
            />
            <label
                htmlFor={props.name + '-false'}>{props.falseLabel}</label>

            <p className="description">{props.description}</p>
        </fieldset>
    )
}
