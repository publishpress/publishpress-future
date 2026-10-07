import { Button, PanelRow, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { useCallback, useMemo, useState } from '@wordpress/element';
import { store as workflowStore } from '../workflow-store';
import { store as editorStore } from '../editor-store';
import PersistentPanelBody from '../persistent-panel-body';
import { generateWorkflowSummaryText } from './generate-workflow-summary';
import { copyTextToClipboard } from '../../utils/copy-text-to-clipboard';

export const WorkflowShareSummaryPanel = () => {
    const [copyFeedback, setCopyFeedback] = useState('');

    const {
        workflow,
        getNodeTypeByName,
        isLoadingWorkflow,
    } = useSelect((select) => {
        return {
            workflow: select(workflowStore).getWorkflow(),
            getNodeTypeByName: select(editorStore).getNodeTypeByName,
            isLoadingWorkflow: select(workflowStore).isLoadingWorkflow(),
        };
    });

    const { createSuccessNotice, createErrorNotice } = useDispatch('core/notices');

    const flowSignature = useMemo(() => {
        const nodes = workflow?.flow?.nodes || [];
        const edges = workflow?.flow?.edges || [];

        return JSON.stringify({ nodes, edges });
    }, [workflow?.flow?.nodes, workflow?.flow?.edges]);

    const summaryText = useMemo(() => {
        return generateWorkflowSummaryText({
            workflow,
            getNodeTypeByName,
        });
    }, [
        workflow?.title,
        workflow?.description,
        workflow?.status,
        flowSignature,
        getNodeTypeByName,
    ]);

    const onCopySummary = useCallback(async () => {
        try {
            await copyTextToClipboard(summaryText);

            createSuccessNotice(
                __('Workflow summary copied to clipboard.', 'post-expirator'),
                {
                    type: 'snackbar',
                    isDismissible: true,
                }
            );

            setCopyFeedback(__('Copied!', 'post-expirator'));
        } catch (error) {
            createErrorNotice(
                __('Unable to copy workflow summary. Please copy manually.', 'post-expirator')
            );

            setCopyFeedback(__('Copy failed', 'post-expirator'));
        }
    }, [summaryText, createSuccessNotice, createErrorNotice]);

    return (
        <div>
            <PersistentPanelBody
                className="edit-post-post-status workflow-editor-dev-panel workflow-editor-share-summary-panel"
                title={__('Workflow Summary', 'post-expirator')}
                initialOpen={true}
                disabled={isLoadingWorkflow}
            >
                <PanelRow>
                    <p>
                        {__(
                            'Copy this plain-text summary to share in conversations. It includes the workflow flow and non-default step settings.',
                            'post-expirator'
                        )}
                    </p>
                </PanelRow>
                <PanelRow>
                    <TextareaControl
                        label={__('Summary', 'post-expirator')}
                        value={summaryText}
                        readOnly={true}
                        rows={18}
                        className="workflow-editor-share-summary-textarea"
                        onChange={() => {}}
                    />
                </PanelRow>
                <PanelRow>
                    <Button
                        variant="secondary"
                        onClick={onCopySummary}
                        disabled={isLoadingWorkflow || !summaryText}
                    >
                        {__('Copy to Clipboard', 'post-expirator')}
                    </Button>
                    {copyFeedback && (
                        <span className="workflow-editor-share-summary-copy-feedback">
                            {copyFeedback}
                        </span>
                    )}
                </PanelRow>
            </PersistentPanelBody>
        </div>
    );
};

export default WorkflowShareSummaryPanel;
