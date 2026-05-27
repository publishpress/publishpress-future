import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import {
    Button,
    __experimentalVStack as VStack,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

const { apiFetch } = wp;
import { SettingsTab } from '../settings-tab';
import { SettingsList } from './components/settings-list';
import { useExport } from './hooks/export';

const ExportTab = () => {
    const [exportActionWorkflows, setExportActionWorkflows] = useState(true);
    const [exportActionSettings, setExportActionSettings] = useState(true);
    const [isLoadingWorkflows, setIsLoadingWorkflows] = useState(false);
    const [workflows, setWorkflows] = useState([]);
    const [selectedWorkflows, setSelectedWorkflows] = useState([]);
    const [selectedSettings, setSelectedSettings] = useState(['postTypesDefaults', 'general', 'notifications', 'display', 'admin', 'advanced']);

    const settingsOptions = [
        {
            title: __('Post Types', 'post-expirator'),
            id: 'postTypesDefaults',
        },
        {
            title: __('General', 'post-expirator'),
            id: 'general',
        },
        {
            title: __('Notifications', 'post-expirator'),
            id: 'notifications',
        },
        {
            title: __('Display', 'post-expirator'),
            id: 'display',
        },
        {
            title: __('Admin', 'post-expirator'),
            id: 'admin',
        },
        {
            title: __('Advanced', 'post-expirator'),
            id: 'advanced',
        },
    ];

    const {
        handleExport,
        handleExportCancel,
        isExporting,
    } = useExport({
        apiUrl: addQueryArgs(`publishpress-future/v1/backup/export`),
        exportActionWorkflows,
        exportActionSettings,
        workflows: selectedWorkflows,
        settings: selectedSettings,
        successNotice: __('Settings exported successfully.', 'post-expirator'),
        errorNotice: __('Failed to export settings.', 'post-expirator'),
    });

    useEffect(() => {
        setIsLoadingWorkflows(true);

        apiFetch({
            path: addQueryArgs(`publishpress-future/v1/backup/workflows`),
        }).then((result) => {
            setWorkflows(result.workflows);
            setSelectedWorkflows(result.workflows.map((workflow) => workflow.id));
            setIsLoadingWorkflows(false);
        });
    }, []);

    return (
        <SettingsTab
            title={__('Export', 'post-expirator')}
            description={__('Export the plugin settings and workflows to a .json file. This file can be imported later to restore the data or migrate to another site.', 'post-expirator')}
        >
            <VStack className="pe-settings-tab__export">
                <SettingsList
                    items={workflows}
                    label={__('Action Workflows', 'post-expirator')}
                    isLoading={isLoadingWorkflows}
                    checked={exportActionWorkflows}
                    onCheckboxChange={(value) => setExportActionWorkflows(value)}
                    selectedItems={selectedWorkflows}
                    onSelectItems={setSelectedWorkflows}
                    className="pe-settings-tab__export-workflows"
                />

                <SettingsList
                    items={settingsOptions}
                    label={__('Action Settings', 'post-expirator')}
                    checked={exportActionSettings}
                    onCheckboxChange={(value) => setExportActionSettings(value)}
                    selectedItems={selectedSettings}
                    onSelectItems={setSelectedSettings}
                    className="pe-settings-tab__export-settings"
                />
            </VStack>

            {(exportActionWorkflows || exportActionSettings) && (
                <Button isPrimary onClick={handleExport} isBusy={isExporting} disabled={isExporting}>
                    {isExporting ? __('Exporting...', 'post-expirator') : __('Export', 'post-expirator')}
                </Button>
            )}

            {isExporting && <Button isLink onClick={handleExportCancel} className="pe-settings-tab__export-cancel-button">{__('Cancel', 'post-expirator')}</Button>}
        </SettingsTab>
    );
};

export default ExportTab;
