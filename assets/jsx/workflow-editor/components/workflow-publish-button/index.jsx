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

export function WorkflowPublishButton({
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
				isDirty: isEditedWorkflowDirty(),
				isNew: isNewWorkflow(),
				isPublished: isCurrentWorkflowPublished(),
				isSaving: isSavingWorkflow(),
				isSaveable: isEditedWorkflowSaveable(),
			};
		},
		[]
	);

	const {
		saveAsCurrentStatus,
		publishWorkflow,
	} = useDispatch(workflowStore);

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

	/* translators: button label text should, if possible, be under 16 characters. */
	const label = !isPublished ? __('Publish', 'post-expirator') : __('Update', 'post-expirator');

	const shortLabel = label;

	const isSaved = forceSavedMessage || (!isNew && !isDirty);
	const isSavedState = isSaving || isSaved;
	const isDisabled = isSaving || isSaved || !isSaveable;

	let text;

	if (isSaving) {
		text = isAutosaving ? __('Autosaving...', 'post-expirator') : __('Updating...', 'post-expirator');
	} else if (isSaved) {
		text = __('Updated', 'post-expirator');
	} else if (isLargeViewport) {
		text = label;
	} else if (showIconLabels) {
		text = shortLabel;
	}

	const onClick = () => {
		if (isDisabled) {
			return;
		}

		if (isPublished) {
			saveAsCurrentStatus();
		} else {
			publishWorkflow();
		}
	}

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
			onClick={onClick}
			variant={'primary'}
			label={label}
			aria-disabled={isDisabled}
		>
			{text}
		</Button>
	);
}

export default WorkflowPublishButton;
