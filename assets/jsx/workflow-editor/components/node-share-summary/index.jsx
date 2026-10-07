import { Button, PanelRow, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { useCallback, useMemo, useState } from '@wordpress/element';
import PersistentPanelBody from '../persistent-panel-body';
import { generateNodeSummaryText } from '../workflow-share-summary/generate-workflow-summary';
import { copyTextToClipboard } from '../../utils/copy-text-to-clipboard';

export const NodeShareSummaryPanel = ({ node, nodeType }) => {
    const [copyFeedback, setCopyFeedback] = useState('');

    const { createSuccessNotice, createErrorNotice } = useDispatch('core/notices');

    const settingsSignature = useMemo(() => {
        return JSON.stringify(node?.data?.settings || {});
    }, [node?.data?.settings]);

    const summaryText = useMemo(() => {
        return generateNodeSummaryText({
            node,
            nodeType,
        });
    }, [
        node?.id,
        node?.data?.label,
        node?.data?.slug,
        node?.data?.name,
        node?.data?.elementaryType,
        settingsSignature,
        nodeType,
    ]);

    const onCopySummary = useCallback(async () => {
        try {
            await copyTextToClipboard(summaryText);

            createSuccessNotice(
                __('Step summary copied to clipboard.', 'post-expirator'),
                {
                    type: 'snackbar',
                    isDismissible: true,
                }
            );

            setCopyFeedback(__('Copied!', 'post-expirator'));
        } catch (error) {
            createErrorNotice(
                __('Unable to copy step summary. Please copy manually.', 'post-expirator')
            );

            setCopyFeedback(__('Copy failed', 'post-expirator'));
        }
    }, [summaryText, createSuccessNotice, createErrorNotice]);

    return (
        <div>
            <PersistentPanelBody
                className="edit-post-post-status workflow-editor-dev-panel workflow-editor-share-summary-panel"
                title={__('Step Summary', 'post-expirator')}
                initialOpen={true}
            >
                <PanelRow>
                    <p>
                        {__(
                            'Copy this plain-text summary to share in conversations. It includes the step type and non-default settings.',
                            'post-expirator'
                        )}
                    </p>
                </PanelRow>
                <PanelRow>
                    <TextareaControl
                        label={__('Summary', 'post-expirator')}
                        value={summaryText}
                        readOnly={true}
                        rows={12}
                        className="workflow-editor-share-summary-textarea"
                        onChange={() => {}}
                    />
                </PanelRow>
                <PanelRow>
                    <Button
                        variant="secondary"
                        onClick={onCopySummary}
                        disabled={!summaryText}
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

export default NodeShareSummaryPanel;
