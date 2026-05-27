import { useSelect, useDispatch } from '@wordpress/data';
import { CheckboxControl } from '@wordpress/components';
import { store } from '../store';
import { useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export function Fieldset({context, postId, apiUrl, nonce, workflowNonce, actionNonce, onChange, wrapper}) {
    const {
        workflowsWithManualTrigger,
        workflowsEnabledForPost
    } = useSelect((select) => {
        return {
            workflowsWithManualTrigger: select(store).getWorkflowsWithManualTrigger(),
            workflowsEnabledForPost: select(store).getWorkflowsEnabledForPost(),
        };
    });

    const {
        updateWorkflowsEnabledForPost,
        setWorkflowsWithManualTrigger,
        setWorkflowsEnabledForPost
    } = useDispatch(store);

    useEffect(() => {
        setWorkflowsWithManualTrigger([]);
        setWorkflowsEnabledForPost([]);

        apiFetch({
            url: `${apiUrl}/posts/workflow-settings/${postId}`,
            headers: {
                'X-WP-Nonce': nonce,
                'X-PP-Workflow-Nonce': workflowNonce,
            },
        }).then((response) => {
            setWorkflowsWithManualTrigger(response.workflowsWithManualTrigger);
            setWorkflowsEnabledForPost(response.manuallyEnabledWorkflows);
        });
    }, [postId]);

    const handleChange = (workflowId, checked) => {
        updateWorkflowsEnabledForPost(workflowId, checked);

        if (onChange) {
            onChange(workflowId, checked);
        }
    }

    const controls = Object.keys(workflowsWithManualTrigger).map((key) => {
        const workflow = workflowsWithManualTrigger[key];
        const checked = workflowsEnabledForPost.includes(workflow.workflowId);

        return (
            <CheckboxControl
                key={'manual-workflow-trigger-' + key}
                label={workflow.label}
                name='future_workflow_manual_trigger[]'
                checked={checked}
                value={workflow.workflowId}
                onChange={(value) => handleChange(workflow.workflowId, value)}
            />
        );
    });

    if (! wrapper) {
        wrapper = ({children}) => {
            return children;
        }
    }

    const WrapperElement = wrapper;

    return (
        <WrapperElement
            workflowsWithManualTrigger={workflowsWithManualTrigger}
            workflowsEnabledForPost={workflowsEnabledForPost}
        >
            {controls.length > 0 && (
                <div id={`post-expirator-${context}-wrapper`}>
                    <input type='hidden' name='future_workflow_view' value={context} />
                    {context === 'quick-edit' && actionNonce && (
                        <input type='hidden' name='_future_action_nonce' value={actionNonce} />
                    )}
                    {controls}
                </div>
            )}
        </WrapperElement>
    );
}
