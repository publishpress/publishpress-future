import { useRef, useState } from '@wordpress/element';
import { dispatch } from '@wordpress/data';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import { Dashicon } from '@wordpress/components';

export const useExport = ({
    apiUrl,
    exportActionWorkflows,
    exportActionSettings,
    workflows,
    settings,
    successNotice,
    errorNotice,
}) => {
    const [isExporting, setIsExporting] = useState(false);

    const apiRequestControllerRef = useRef(new AbortController());

    const { apiFetch } = wp;

    const handleJsonDataDownload = (resultData) => {
        // Create a blob with the JSON data
        const jsonData = JSON.stringify(resultData, null, 2);
        const blob = new Blob([jsonData], { type: 'application/json' });

        // Create download link and trigger click
        const downloadUrl = URL.createObjectURL(blob);
        const date = new Date().toISOString().replace(':', '-').split('.')[0];
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `publishpress-future-backup-${date}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Clean up the URL object
        URL.revokeObjectURL(downloadUrl);
    };

    const handleExport = async () => {
        setIsExporting(true);

        const controller = apiRequestControllerRef.current;

        if (controller) {
            controller.abort();
        }

        apiRequestControllerRef.current = new AbortController();
        const { signal } = apiRequestControllerRef.current;

        const { createSuccessNotice, createErrorNotice } = dispatch('core/notices');

        apiFetch({
            path: addQueryArgs(apiUrl),
            method: 'POST',
            data: {
                exportActionWorkflows: exportActionWorkflows,
                exportActionSettings: exportActionSettings,
                workflows: workflows,
                settings: settings,
            },
            signal,
        }).then((result) => {
            setIsExporting(false);
            handleJsonDataDownload(result.data);

            createSuccessNotice(
                successNotice,
                {
                    type: 'snackbar',
                    isDismissible: true,
                    actions: [
                        {
                            label: __('Download', 'post-expirator'),
                            onClick: () => {
                                handleJsonDataDownload(result.data);
                            },
                        },
                    ],
                    icon: <Dashicon icon="yes" />,
                    autoDismiss: true,
                    explicitDismiss: true,
                }
            );
        }).catch((error) => {
            if (error.name === 'AbortError') {
                return;
            }

            createErrorNotice(
                errorNotice,
                {
                    type: 'snackbar',
                    isDismissible: true,
                    actions: [],
                    icon: <Dashicon icon="no" />,
                    autoDismiss: true,
                    explicitDismiss: true,
                }
            );

            setIsExporting(false);
        });
    };

    const handleExportCancel = () => {
        const controller = apiRequestControllerRef.current;

        if (controller) {
            controller.abort();
        }
    };


    return {
        handleExport,
        handleExportCancel,
        isExporting,
    };
};


export default useExport;
