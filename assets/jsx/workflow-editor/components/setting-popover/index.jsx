import {
    Popover,
    __experimentalVStack as VStack,
    __experimentalHStack as HStack,
    Button,
 } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export const SettingPopover = ({ onClose, title, children, className, offset = 40, placement = "left-start", ...props }) => {
    return (
        <Popover
            onClose={onClose}
            placement={placement}
            offset={offset}
            className={`workflow-editor-setting-popover ${className}`}
            {...props}
        >
            <VStack>
                <HStack>
                    <h2 className="components-truncate components-text components-heading block-editor-inspector-popover-header__heading">
                        {title}
                    </h2>
                    <Button
                        icon={'no-alt'}
                        isSmall={true}
                        label={__('Close', 'post-expirator')}
                        showTooltip
                        className="block-editor-inspector-popover-header__action"
                        onClick={onClose}
                    />
                </HStack>
            </VStack>

            {children}
        </Popover>
    );
};

export default SettingPopover;
