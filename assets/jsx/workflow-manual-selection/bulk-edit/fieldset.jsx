import { useSelect, useDispatch } from '@wordpress/data';
import { CheckboxControl, SelectControl } from '@wordpress/components';
import { store } from '../store';
import { useEffect, useState, useMemo } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

export function Fieldset({context, onChange}) {
    const {
        workflowsWithManualTrigger,
    } = useSelect((select) => {
        return {
            workflowsWithManualTrigger: select(store).getWorkflowsWithManualTrigger(),
        };
    });

    const {
        setWorkflowsWithManualTrigger,
    } = useDispatch(store);

    const [selectedWorkflows, setSelectedWorkflows] = useState([]);
    const [strategy, setStrategy] = useState('no-change');

    const {
        postType,
        nonce,
        workflowNonce,
        apiUrl
    } = window.futureWorkflowManualSelection;

    useEffect(() => {
        apiFetch({
            url: `${apiUrl}/workflows/with-manual-trigger/${postType}`,
            headers: {
                'X-WP-Nonce': nonce,
                'X-PP-Workflow-Nonce': workflowNonce,
            },
        }).then((response) => {
            setWorkflowsWithManualTrigger(response.workflowsWithManualTrigger);
        });
    }, []);

    const handleChange = (workflowId, checked) => {
        setSelectedWorkflows((prevSelectedWorkflows) => {
            if (checked) {
                return [...prevSelectedWorkflows, workflowId];
            }

            return prevSelectedWorkflows.filter((id) => id !== workflowId);
        });
    }

    const controls = useMemo(() => {
        return Object.keys(workflowsWithManualTrigger).map((key) => {
            const workflow = workflowsWithManualTrigger[key];

            return (
                <CheckboxControl
                    key={'manual-workflow-trigger-' + key}
                    label={workflow.label}
                    name='future_workflow_manual_trigger[]'
                    checked={selectedWorkflows.includes(workflow.workflowId)}
                    value={workflow.workflowId}
                    onChange={(value) => handleChange(workflow.workflowId, value)}
                />
            );
        });
    }, [workflowsWithManualTrigger, selectedWorkflows]);

    const strategyOptions = [
        {
            label: __('— No Change —', 'post-expirator'),
            value: 'no-change',
        },
        {
            label: __('Change manually enabled workflows', 'post-expirator'),
            value: 'change',
        },
    ];

    return (
        <>
            {controls.length > 0 && (
                <div id={`post-expirator-${context}-wrapper`}>
                    <input type='hidden' name='future_workflow_view' value={context} />

                    <SelectControl
                        label={__('Action Workflows', 'post-expirator')}
                        name='future_workflow_manual_strategy'
                        value={strategy}
                        options={strategyOptions}
                        onChange={(value) => setStrategy(value)}
                    />

                    {strategy !== 'no-change' && controls}
                </div>
            )}
        </>
    );
}
