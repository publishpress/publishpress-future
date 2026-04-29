import { __ } from '@wordpress/i18n';
import {
    TabPanel,
    __experimentalVStack as VStack,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { SettingsTab } from '../settings-tab';
import { FileDropzone } from '../file-dropzone';
import { formatFileSize, readJsonFile } from '../../utils/file';
import { TabImport } from './components/tabs/tab-import';
import { TabPreview } from './components/tabs/tab-preview';
import { useSettingsImport } from './hooks/settings-import';
import { useFileValidation } from './hooks/file-validation';

import './style.css';

const ImportTab = () => {
    const [isImporting, setIsImporting] = useState(false);
    const [file, setFile] = useState(null);
    const [fileContent, setFileContent] = useState(null);
    const [validFile, setValidFile] = useState(false);
    const [validationError, setValidationError] = useState(null);
    const [isDragging, setIsDragging] = useState(false);
    const [selectedWorkflowsData, setSelectedWorkflowsData] = useState([]);
    const [selectedSettingsData, setSelectedSettingsData] = useState([]);

    const { importSettings } = useSettingsImport();
    const { validateFile } = useFileValidation();

    const handleImport = async () => {
        setIsImporting(true);

        const result = await importSettings({
            workflowsToImport: selectedWorkflowsData,
            settingsToImport: selectedSettingsData
        });
        setIsImporting(false);

        if (result) {
            setIsImporting(false);
            setFile(null);
            setFileContent(null);
            setValidFile(false);
            setValidationError(null);
            setSelectedWorkflowsData([]);
            setSelectedSettingsData({});
        }
    };

    const handleFileSelect = async (file) => {
        setFile(file);
        validateFile({ file, onError: handleValidationError, onSuccess: handleValidationSuccess });
        setFileContent(await readJsonFile(file));
    };

    const handleCancel = () => {
        setFile(null);
        setFileContent(null);
        setValidFile(false);
        setValidationError(null);
        setSelectedWorkflowsData([]);
        setSelectedSettingsData({});
    };

    const handleValidationError = (message) => {
        setValidFile(false);
        setValidationError(message);
    };

    const handleValidationSuccess = () => {
        setValidFile(true);
        setValidationError(null);
    };

    const onSelectWorkflows = (items) => {
        let workflowsData = [];

        items.forEach((workflowId) => {
            const data = fileContent?.workflows.find((workflow) => workflow.id === workflowId);

            if (data) {
                workflowsData.push(data);
            }
        });
        setSelectedWorkflowsData(workflowsData);
    };

    const onSelectSettings = (items) => {
        let settingsData = {};

        items.forEach((settingName) => {
            const data = fileContent?.settings[settingName];

            if (data) {
                settingsData[settingName] = data;
            }
        });

        setSelectedSettingsData(settingsData);
    };

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

    return (
        <SettingsTab
            title={__('Import', 'post-expirator')}
            description={__('Import the plugin settings or workflows from a .json file.', 'post-expirator')}
        >
            <VStack className="pe-settings-tab__import-file-upload">
                {!validFile && (
                    <FileDropzone
                        isDragging={isDragging}
                        onDrop={(e) => {
                            e.preventDefault();
                            handleFileSelect(e.dataTransfer.files[0]);
                        }}
                        onDragOver={(e) => e.preventDefault()}
                        onDragEnter={(e) => {
                            e.preventDefault();
                            setIsDragging(true);
                        }}
                        onDragLeave={(e) => {
                            e.preventDefault();
                            if (e.currentTarget === e.target) {
                                setIsDragging(false);
                            }
                        }}
                        onFileSelect={(event) => {
                            handleFileSelect(event.currentTarget.files[0]);
                        }}
                        file={file}
                        formatFileSize={formatFileSize}
                    />
                )}

                {validFile && (
                    <>
                        <VStack className="pe-settings-tab__import-file-upload-info">
                            <div><span className="pe-settings-tab__import-file-upload-info-label">{__('Selected file', 'post-expirator')}:</span> {file.name}</div>
                            <div><span className="pe-settings-tab__import-file-upload-info-label">{__('File size', 'post-expirator')}:</span> {formatFileSize(file.size)}</div>
                        </VStack>

                        <TabPanel
                            className="pe-settings-tab__import-tabs"
                            tabs={[
                                {
                                    name: 'content',
                                    title: __('Select Content to Import', 'post-expirator'),
                                    className: 'pe-settings-tab__import-preview-tab pe-settings-tab__import-preview-tab-content',
                                },
                                {
                                    name: 'json',
                                    title: __('Raw Data Preview', 'post-expirator'),
                                    className: 'pe-settings-tab__import-preview-tab pe-settings-tab__import-preview-tab-json',
                                },
                            ]}
                        >
                            {(tab) => {
                                if (tab.name === 'content') {
                                    return <TabImport
                                        isImporting={isImporting}
                                        handleImport={handleImport}
                                        onCancel={handleCancel}
                                        fileContent={fileContent}
                                        onSelectWorkflows={onSelectWorkflows}
                                        onSelectSettings={onSelectSettings}
                                        settingsOptions={settingsOptions}
                                    />;
                                }

                                if (tab.name === 'json') {
                                    return <TabPreview
                                        content={fileContent}
                                        onCancel={handleCancel}
                                    />;
                                }

                                return null;
                            }}
                        </TabPanel>
                    </>
                )}

                {validationError && (
                    <p className="error">{validationError}</p>
                )}
            </VStack>
        </SettingsTab>
    );
};

export default ImportTab;
