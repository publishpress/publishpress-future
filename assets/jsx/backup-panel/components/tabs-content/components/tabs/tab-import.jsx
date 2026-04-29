import {
    Button,
    __experimentalHStack as HStack,
    __experimentalVStack as VStack,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { SettingsList } from '../settings-list';
import { useState, useEffect } from '@wordpress/element';

export const TabImport = ({
    isImporting,
    handleImport,
    onCancel,
    fileContent,
    onSelectWorkflows,
    onSelectSettings,
    settingsOptions
}) => {
    const [selectedWorkflows, setSelectedWorkflows] = useState([]);
    const [selectedSettings, setSelectedSettings] = useState([]);
    const [workflows, setWorkflows] = useState([]);
    const [settings, setSettings] = useState([]);
    const [importActionWorkflows, setImportActionWorkflows] = useState(true);
    const [importActionSettings, setImportActionSettings] = useState(true);

    useEffect(() => {
        if (! fileContent) {
            return;
        }

        if (fileContent.workflows && fileContent.workflows.length > 0) {
            setWorkflows(fileContent.workflows);
        }

        if (fileContent.settings && Object.keys(fileContent.settings).length > 0) {
            let filteredSettings = [];

            Object.keys(fileContent.settings).forEach((setting) => {
                const settingData = settingsOptions.find((option) => option.id === setting);

                if (settingData) {
                    filteredSettings.push(settingData);
                }
            });

            setSettings(filteredSettings);
        }
    }, [fileContent]);

    useEffect(() => {
        if (! importActionWorkflows) {
            setSelectedWorkflows([]);
        }

        if (! importActionSettings) {
            setSelectedSettings([]);
        }
    }, [importActionWorkflows, importActionSettings]);

    return (
        <>
            <p>{__('Select the content you want to import.', 'post-expirator')}</p>

            <VStack className="pe-settings-tab__export">
                {workflows.length > 0 && (
                    <SettingsList
                        items={workflows}
                        label={__('Action Workflows', 'post-expirator')}
                        checked={importActionWorkflows}
                        onCheckboxChange={(value) => setImportActionWorkflows(value)}
                        selectedItems={selectedWorkflows}
                        onSelectItems={(items) => {
                            setSelectedWorkflows(items);
                            onSelectWorkflows(items);
                        }}
                        className="pe-settings-tab__export-workflows"
                    />
                )}

                {settings.length > 0 && (
                    <SettingsList
                        items={settings}
                        label={__('Action Settings', 'post-expirator')}
                        checked={importActionSettings}
                        onCheckboxChange={(value) => setImportActionSettings(value)}
                        selectedItems={selectedSettings}
                        onSelectItems={(items) => {
                            setSelectedSettings(items);
                            onSelectSettings(items);
                        }}
                        className="pe-settings-tab__export-settings"
                    />
                )}

                {workflows.length === 0 && settings.length === 0 && (
                    <p>{__('No workflows or settings found in the file.', 'post-expirator')}</p>
                )}
            </VStack>

            <HStack className="pe-settings-tab__import-file-upload-actions">
                <Button isPrimary
                    isBusy={isImporting}
                    onClick={handleImport}
                    disabled={(selectedWorkflows.length === 0 && selectedSettings.length === 0)}
                >
                    {__('Import', 'post-expirator')}
                </Button>

                <Button variant="secondary" onClick={onCancel}>
                    {__('Cancel and select a different file', 'post-expirator')}
                </Button>
            </HStack>
        </>
    );
};

export default TabImport;
