import { PanelRow, SelectControl } from '@wordpress/components';
import { __ } from '@publishpress/i18n';
import { useSelect, dispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

import { FutureActionPanelAfterActionField } from '../components/FutureActionPanelAfterActionField';

const { apiUrl, nonce, workflows } = futureWorkflows;

const Fields = ({ storeName }) => {
    const defaultWorkflow = workflows.length > 0 ? workflows[0].value : 0;

    const {
        action,
        workflowId,
        postId,
        currentStoreWorkflowId,
    } = useSelect((select) => {

        const classicEditor = select('publishpress-future/future-action');
        const blockEditor = select('core/edit-post');
        const quickEdit = select('publishpress-future/future-action-quick-edit');
        let postId = 0;

        if (classicEditor && classicEditor.getPostId) {
            postId = classicEditor.getPostId();
        } else if (blockEditor && blockEditor.getCurrentPostId) {
            postId = blockEditor.getCurrentPostId();
        } else if (quickEdit && quickEdit.getPostId) {
            postId = quickEdit.getPostId();
        }

        return {
            action: select(storeName).getAction(),
            workflowId: select(storeName).getExtraDataByName('workflowId') || defaultWorkflow,
            postId: postId,
            currentStoreWorkflowId: select(storeName).getExtraDataByName('workflowId'),
        };
    });

    const {
        setExtraDataByName,
    } = dispatch(storeName);

    const handleActionChange = (value) => {
        setExtraDataByName('workflowId', value);
    }

    useEffect(() => {
        if (postId) {
            try {
                wp.apiFetch({
                    url: `${apiUrl}/post-expiration/${postId}`,
                    headers: {
                        'X-WP-Nonce': nonce,
                    },
                }).then((data) => {
                    setExtraDataByName('workflowId', data.extraData.workflowId);
                });
            } catch (error) {
                console.error(error);
            }
        }
    }, []);



    if (workflowId && workflowId !== currentStoreWorkflowId) {
        setExtraDataByName('workflowId', workflowId);
    }

    return (
        <>
            {workflows.length > 0 && action === 'trigger-workflow' &&
                <PanelRow className='future-action-panel-content future-action-full-width'>
                    <SelectControl
                        label={__('Workflow to trigger', 'post-expirator')}
                        value={workflowId}
                        options={workflows}
                        onChange={handleActionChange}
                        className='future-action-workflow'
                    />
                    <input type='hidden' name='future_action_pro_workflow' value={workflowId} />
                </PanelRow>
            }

            { workflows.length === 0 && action === 'trigger-workflow' &&
                <PanelRow className='future-action-panel-content future-action-full-width'>
                    <p>{__('No compatible workflows available.', 'post-expirator')}</p>
                </PanelRow>
            }
        </>
    );
}


const LegacyActionFields = () => (
    <FutureActionPanelAfterActionField>
        {({ storeName }) => {
            return <Fields storeName={storeName} />;
        }}
    </FutureActionPanelAfterActionField>

);

wp.plugins.registerPlugin('legacy-action-plugin', { render: LegacyActionFields, scope: 'publishpress-future' });
