import { MenuGroup, MenuItem } from '@wordpress/components';
import { __, _x } from '@wordpress/i18n';
import { useViewportMatch } from '@wordpress/compose';
import { displayShortcut } from '@wordpress/keycodes';
import { MoreMenuFeatureToggle } from './menu-feature-toggle';
import {
	FEATURE_FULLSCREEN_MODE,
	FEATURE_DEVELOPER_MODE,
	FEATURE_WELCOME_GUIDE,
	FEATURE_ADVANCED_SETTINGS,
	FEATURE_MINI_MAP,
	FEATURE_CONTROLS,
} from '../../constants';

export const MoreMenuItemsView = () => {
	const isLargeViewport = useViewportMatch('medium');
	if (!isLargeViewport) {
		return null;
	}

	return (
		<>
			<MenuGroup label={_x('View', 'noun', 'post-expirator')}>
				<MoreMenuFeatureToggle
					scope="core/edit-post"
					feature={FEATURE_FULLSCREEN_MODE}
					label={__('Fullscreen mode', 'post-expirator')}
					info={__('Work without distraction', 'post-expirator')}
					messageActivated={__('Fullscreen mode activated', 'post-expirator')}
					messageDeactivated={__('Fullscreen mode deactivated', 'post-expirator')}
					shortcut={displayShortcut.secondary('f')}
				/>
				<MoreMenuFeatureToggle
					scope="core/edit-post"
					feature={FEATURE_ADVANCED_SETTINGS}
					label={__('Advanced Settings', 'post-expirator')}
					info={__('Display advanced settings for the workflow, triggers and steps', 'post-expirator')}
					messageActivated={__('Advanced settings mode activated', 'post-expirator')}
					messageDeactivated={__('Advanced settings mode deactivated', 'post-expirator')}
					shortcut={displayShortcut.secondary('a')}
				/>
				<MoreMenuFeatureToggle
					scope="core/edit-post"
					feature={FEATURE_MINI_MAP}
					label={__('Display a mini map', 'post-expirator')}
					info={__('Display a mini map in the bottom of the editor, triggers and steps', 'post-expirator')}
					messageActivated={__('Mini map activated', 'post-expirator')}
					messageDeactivated={__('Mini map deactivated', 'post-expirator')}
				/>
				<MoreMenuFeatureToggle
					scope="core/edit-post"
					feature={FEATURE_CONTROLS}
					label={__('Display the controls panel', 'post-expirator')}
					info={__('Display the controls panel with buttons to zoon in, zoom out, fit the view and lock the viewport', 'post-expirator')}
					messageActivated={__('Mini map activated', 'post-expirator')}
					messageDeactivated={__('Mini map deactivated', 'post-expirator')}
				/>
			</MenuGroup>
			<MenuGroup label={__('Tools', 'post-expirator')}>
				<MoreMenuFeatureToggle
					scope="core/edit-post"
					feature={FEATURE_DEVELOPER_MODE}
					label={__('Developer mode', 'post-expirator')}
					info={__('Work in developer mode', 'post-expirator')}
					messageActivated={__('Developer mode activated', 'post-expirator')}
					messageDeactivated={__('Developer mode deactivated', 'post-expirator')}
					shortcut={displayShortcut.secondary('d')}
				/>
				<MoreMenuFeatureToggle
					scope="core/edit-post"
					feature={FEATURE_WELCOME_GUIDE}
					label={__('Welcome guide', 'post-expirator')}
					info={__('Display the welcome guide', 'post-expirator')}
					messageActivated={__('Welcome guide activated', 'post-expirator')}
					messageDeactivated={__('Welcome guide deactivated', 'post-expirator')}
				/>
				<MenuItem
					icon="external"
					onClick={() => {
						window.open('https://publishpress.com/docs-category/future/', '_blank');
					}}
				>
					{__('Help', 'post-expirator')}
				</MenuItem>
			</MenuGroup>
		</>
	);
}
