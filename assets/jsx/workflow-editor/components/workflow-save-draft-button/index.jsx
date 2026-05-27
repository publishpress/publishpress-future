/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	__unstableGetAnimateClassName as getAnimateClassName,
} from '@wordpress/components';
import { usePrevious, useViewportMatch } from '@wordpress/compose';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon, check, cloud, cloudUpload } from '@wordpress/icons';
import { displayShortcut } from '@wordpress/keycodes';
import { Button } from '@wordpress/components';

import { store as workflowStore } from '../workflow-store';

export function WorkflowSaveDraftButton({
	forceIsDirty,
	forceIsSaving,
	showIconLabels = false,
}) {
	const [forceSavedMessage, setForceSavedMessage] = useState(false);
	const isLargeViewport = useViewportMatch('small');

	const {
		isAutosaving,
		isDirty,
		isNew,
		isPublished,
		isSaveable,
		isSaving,
	} = useSelect(
		(select) => {
			const {
				isNewWorkflow,
				isCurrentWorkflowPublished,
				isEditedWorkflowDirty,
				isSavingWorkflow,
				isEditedWorkflowSaveable,
				isAutosavingWorkflow,
			} = select(workflowStore);

			return {
				isAutosaving: isAutosavingWorkflow(),
				isDirty: forceIsDirty || isEditedWorkflowDirty(),
				isNew: isNewWorkflow(),
				isPublished: isCurrentWorkflowPublished(),
				isSaving: forceIsSaving || isSavingWorkflow(),
				isSaveable: isEditedWorkflowSaveable(),
			};
		},
		[forceIsDirty, forceIsSaving]
	);

	const { saveAsDraft } = useDispatch(workflowStore);

	const wasSaving = usePrevious(isSaving);

	useEffect(() => {
		let timeoutId;

		if (wasSaving && !isSaving) {
			setForceSavedMessage(true);
			timeoutId = setTimeout(() => {
				setForceSavedMessage(false);
			}, 1000);
		}

		return () => clearTimeout(timeoutId);
	}, [isSaving]);

	if (isPublished) {
		return;
	}

	/* translators: button label text should, if possible, be under 16 characters. */
	const label = __('Save draft', 'post-expirator');

	/* translators: button label text should, if possible, be under 16 characters. */
	const shortLabel = __('Save', 'post-expirator');

	const isSaved = forceSavedMessage || (!isNew && !isDirty);
	const isSavedState = isSaving || isSaved;
	const isDisabled = isSaving || isSaved || !isSaveable;

	let text;

	if (isSaving) {
		text = isAutosaving ? __('Autosaving', 'post-expirator') : __('Saving', 'post-expirator');
	} else if (isSaved) {
		text = __('Saved', 'post-expirator');
	} else if (isLargeViewport) {
		text = label;
	} else if (showIconLabels) {
		text = shortLabel;
	}

	const onClick = () => {
		saveAsDraft();
	};

	// Use common Button instance for all saved states so that focus is not
	// lost.
	return (
		<Button
			className={
				isSaveable || isSaving
					? classnames({
						'editor-post-save-draft': !isSavedState,
						'editor-post-saved-state': isSavedState,
						'is-saving': isSaving,
						'is-autosaving': isAutosaving,
						'is-saved': isSaved,
						[getAnimateClassName({
							type: 'loading',
						})]: isSaving,
					})
					: undefined
			}
			onClick={isDisabled ? undefined : onClick}
			shortcut={displayShortcut.primary('s')}
			variant={isLargeViewport ? 'tertiary' : undefined}
			icon={isLargeViewport ? undefined : cloudUpload}
			label={label}
			aria-disabled={isDisabled}
		>
			{isSavedState && <Icon icon={isSaved ? check : cloud} />}
			{text}
		</Button>
	);
}

export default WorkflowSaveDraftButton;
