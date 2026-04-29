import { __ } from '@wordpress/i18n';

export const useFileValidation = () => {
    const validateFile = async ({ file, onError, onSuccess }) => {
        const fileExtension = file.name.split('.').pop();

        if (fileExtension !== 'json') {
            onError(__('Invalid file type. Please upload a .json file.', 'post-expirator'));
            return;
        }

        try {
            onSuccess();
        } catch (error) {
            onError(error.message);
        }
    };

    return { validateFile };
};

export default useFileValidation;
