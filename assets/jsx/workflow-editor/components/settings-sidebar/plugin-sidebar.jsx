/**
 * WordPress dependencies
 */
import { ComplementaryArea } from '@wordpress/interface';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { store as keyboardShortcutsStore } from '@wordpress/keyboard-shortcuts';

/**
 * Internal dependencies
 */
import { store as workflowStore } from '../workflow-store';
import { store as editorStore } from '../editor-store';
import { SHORTCUT_TOGGLE_SIDEBAR } from '../keyboard-shortcuts/constants';
import { FEATURE_SHOW_ICON_LABELS, SLOT_SCOPE_WORKFLOW_EDITOR } from '../../constants';

export function PluginSidebarEditPost({ className, ...props }) {
	const {
		workflowTitle,
		shortcut,
		showIconLabels
	} = useSelect((select) => {
		return {
			workflowTitle: select(workflowStore).getEditedWorkflowAttribute('title'),
			shortcut: select(
				keyboardShortcutsStore
			).getShortcutRepresentation(SHORTCUT_TOGGLE_SIDEBAR),
			showIconLabels: select(editorStore).isFeatureActive(
				FEATURE_SHOW_ICON_LABELS
			),
		};
	}, []);

	return (
		<ComplementaryArea
			panelClassName={className}

			className="edit-post-sidebar"
			smallScreenTitle={workflowTitle || __('(no name)', 'post-expirator')}
			scope={SLOT_SCOPE_WORKFLOW_EDITOR}
			toggleShortcut={shortcut}
			showIconLabels={showIconLabels}
			{...props}
		/>
	);
}

export default PluginSidebarEditPost;
