import NodeIcon from '../node-icon';
import { PanelRow, Button } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import PersistentPanelBody from '../persistent-panel-body';
import { useState } from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';

import './style.css';

export function NodeValidationPanel({ errors = {} }) {
    const [openDetails, setOpenDetails] = useState({});
    const instanceId = useInstanceId(NodeValidationPanel, 'workflow-editor-validation-details');

    const toggleDetails = (key) => {
        setOpenDetails((previous) => ({
            ...previous,
            [key]: ! previous[key],
        }));
    };

    const iconSize = 18;

    const nodeErrors = Object.values(errors);

    const hasNotifications = nodeErrors.length > 0;

    return (
        <PersistentPanelBody title={__('Validation', 'post-expirator')} className="workflow-editor-validation-panel">
            {hasNotifications && (
                <>
                    {nodeErrors.map((error, index) => {
                        const detailsKey = `${error.error}-${index}`;
                        const isDetailsOpen = !! openDetails[detailsKey];
                        const detailsId = `${instanceId}-${index}`;

                        return (
                            <PanelRow key={`${error.error}-${index}`} className="workflow-editor-validation-notification workflow-editor-error">
                                <NodeIcon icon={'error'} size={iconSize} />
                                <div>
                                    <div className="workflow-editor-validation-notification-header">
                                        <div className="workflow-editor-validation-notification-message">{error.message}</div>

                                        {error.details && (
                                            <Button
                                                className="workflow-editor-validation-notification-details-toggle"
                                                onClick={() => toggleDetails(detailsKey)}
                                                aria-expanded={isDetailsOpen}
                                                aria-controls={detailsId}
                                                label={isDetailsOpen ? __('Hide error details', 'post-expirator') : __('Show error details', 'post-expirator')}
                                                showTooltip
                                            >
                                                <NodeIcon icon={isDetailsOpen ? 'arrow-down' : 'arrow-right'} size={iconSize} />
                                            </Button>
                                        )}
                                    </div>
                                    {error.details && isDetailsOpen && (
                                        <div id={detailsId} className="workflow-editor-validation-notification-details">{error.details}</div>
                                    )}
                                </div>
                            </PanelRow>
                        );
                    })}
                </>
            )}

            {!hasNotifications && (
                <PanelRow className="workflow-editor-validation-notification workflow-editor-success">
                    <NodeIcon icon={'yes-alt'} size={iconSize} />
                    {__('All checks have passed for this step.', 'post-expirator')}
                </PanelRow>
            )}
        </PersistentPanelBody>
    );
}

export default NodeValidationPanel;
