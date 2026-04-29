/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
// import { MenuGroup } from '@wordpress/components';
import { MoreMenuDropdown } from './menu-dropdown';
import { useViewportMatch } from '@wordpress/compose';

// import PreferencesMenuItem from '../preferences-menu-item';
// import ToolsMoreMenuGroup from '../tools-more-menu-group';
import { MoreMenuItemsView } from './menu-items-view';

const POPOVER_PROPS = {
    className: 'edit-post-more-menu__content',
};

export const MoreMenu = ({ showIconLabels }) => {
    const isLargeViewport = useViewportMatch('large');

    return (
        <MoreMenuDropdown
            className="edit-post-more-menu"
            popoverProps={POPOVER_PROPS}
            toggleProps={{
                showTooltip: !showIconLabels,
                ...(showIconLabels && { variant: 'tertiary' }),
            }}
        >
            {({ onClose }) => (
                <>
                    <MoreMenuItemsView />
                    {/* <ToolsMoreMenuGroup.Slot fillProps={ { onClose } } />
					<MenuGroup>
						<PreferencesMenuItem />
					</MenuGroup> */}
                </>
            )}
        </MoreMenuDropdown>
    );
};
