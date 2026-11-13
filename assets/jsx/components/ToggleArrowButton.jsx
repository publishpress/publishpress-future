import { Button } from '@wordpress/components';

export const ToggleArrowButton = function (props) {
    const onClick = function () {
        if (props.onClick) {
            props.onClick();
        }
    };

    const iconExpanded = props.iconExpanded ? props.iconExpanded : 'arrow-up-alt2';
    const iconCollapsed = props.iconCollapsed ? props.iconCollapsed : 'arrow-down-alt2';

    const icon = props.isExpanded ? iconExpanded : iconCollapsed;

    const title = props.isExpanded ? props.titleExpanded : props.titleCollapsed;

    return (
        <Button
            isSmall
            label={title}
            showTooltip
            aria-expanded={!! props.isExpanded}
            icon={icon}
            onClick={onClick}
            className={props.className}
        />
    )
}
