import { __ } from '@wordpress/i18n';

export const postStatusesAutocompleter = {
    getCompletions: (editor, session, pos, prefix, callback) => {
        const { postStatuses } = futureWorkflowEditor;

        callback(null, postStatuses.map(postStatus => ({
                caption: postStatus.value,
                value: postStatus.value,
                meta: __('Post Status', 'post-expirator'),
            }))
        );
    },
};

export default postStatusesAutocompleter;
