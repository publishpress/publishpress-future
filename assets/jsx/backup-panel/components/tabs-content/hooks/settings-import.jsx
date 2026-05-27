import { __ } from '@wordpress/i18n';
import { Dashicon } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';

export const useSettingsImport = () => {
    const { createSuccessNotice, createErrorNotice } = useDispatch('core/notices');

    const importSettings = async ({ workflowsToImport, settingsToImport }) => {
        const formData = new FormData();
        formData.append('data', JSON.stringify({
            workflows: workflowsToImport,
            settings: settingsToImport
        }));

        const wpNonce = wp.apiFetch.nonceMiddleware ? wp.apiFetch.nonceMiddleware.nonce : '';

        try {
            const response = await fetch(
                `${futureBackupPanelData.apiRoot}publishpress-future/v1/backup/import`,
                {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': wpNonce
                    },
                    body: formData,
                    credentials: 'same-origin'
                }
            );

            const data = await response.json();

            if (data.ok) {
                createSuccessNotice(
                    data.message || __('Settings imported successfully.', 'post-expirator'),
                    {
                        type: 'snackbar',
                        isDismissible: true,
                        actions: [],
                        autoDismiss: true,
                        explicitDismiss: true,
                        icon: <Dashicon icon="yes" />,
                    }
                );
                return true;
            }

            throw new Error(data.message);
        } catch (error) {
            createErrorNotice(
                error.message || __('Failed to import settings.', 'post-expirator'),
                {
                    type: 'snackbar',
                    isDismissible: true,
                    actions: [],
                    autoDismiss: true,
                    explicitDismiss: true,
                    icon: <Dashicon icon="warning" />,
                }
            );
            return false;
        }
    };

    return { importSettings };
};

export default useSettingsImport;
