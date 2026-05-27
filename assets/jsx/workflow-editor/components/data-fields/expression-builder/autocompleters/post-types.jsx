import { __ } from '@wordpress/i18n';

export const postTypesAutocompleter = {
    getCompletions: (editor, session, pos, prefix, callback) => {
        const { postTypes } = futureWorkflowEditor;

        callback(null, postTypes.map(postType => ({
                caption: postType.value,
                value: postType.value,
                meta: __('Post Type', 'post-expirator'),
            }))
        );
    },
};

export default postTypesAutocompleter;
