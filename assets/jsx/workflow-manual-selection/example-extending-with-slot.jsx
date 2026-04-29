import { __, sprintf } from '@wordpress/i18n';
import { useSelect, dispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

import { FutureActionPanelTop } from '../../../../lib/vendor/publishpress/publishpress-future/assets/jsx/components/FutureActionPanelTop';
import { CheckboxControl } from '@wordpress/components';

// Example on how to extend using Slot

const Fields = ({ storeName }) => {
    const availableWorkflows = futureWorkflowManualSelection.workflows;

    const {
        workflowSelections,
    } = useSelect((select) => {
        const workflowSelections = availableWorkflows.map((workflow) => {
            const isSelected = select(storeName).getExtraDataByName('workflowManualSelection' + workflow.value) || 0;

            return isSelected ? workflow.value : null;
        });
        return {
            workflowSelections,
        };
    });

    const {
        setExtraDataByName,
    } = dispatch(storeName);

    const handleActionChange = (workflowId, value) => {
        setExtraDataByName('workflowManualSelection' + workflowId, value);
    }

    useEffect(() => {
        availableWorkflows.forEach((workflow) => {
            const enabled = workflowSelections.includes(workflow.value);

            handleActionChange(workflow.value, enabled);
        });
    }, []);

    const checkboxFields = availableWorkflows.map((workflow) => {
        const isChecked = workflowSelections.includes(workflow.value);

        return (
            <CheckboxControl
                key={workflow.value}
                label={sprintf(__('Enable workflow %s', 'post-expirator'), workflow.label)}
                checked={isChecked}
                onChange={(value) => handleActionChange(workflow.value, value)}
            />
        );
    });

    return (
        <>
            {checkboxFields}
        </>
    );
}


const LegacyPanelTop = () => (
    <FutureActionPanelTop>
        {({ storeName }) => {
            return <Fields storeName={storeName} />;
        }}
    </FutureActionPanelTop>

);

wp.plugins.registerPlugin('workflow-manual-selection-plugin', { render: LegacyPanelTop, scope: 'publishpress-future' });
