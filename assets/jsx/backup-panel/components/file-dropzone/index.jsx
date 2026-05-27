import { __ } from '@wordpress/i18n';
import { FormFileUpload } from '@wordpress/components';

export const FileDropzone = ({
    isDragging,
    onDrop,
    onDragOver,
    onDragEnter,
    onDragLeave,
    onFileSelect,
    file,
    formatFileSize
}) => {
    return (
        <div
            className={`pe-dropzone ${isDragging ? 'pe-dropzone--active' : ''}`}
            onDrop={onDrop}
            onDragOver={onDragOver}
            onDragEnter={onDragEnter}
            onDragLeave={onDragLeave}
        >
            <p>{__('Drop a .json file here', 'post-expirator')}</p>
            <p>{__('or', 'post-expirator')}</p>

            <FormFileUpload
                accept=".json,application/json,text/json,text/plain"
                onChange={onFileSelect}
                className="is-primary"
            >
                {__('Select a .json file', 'post-expirator')}
            </FormFileUpload>

            {file && (
                <div className="pe-settings-tab__import-file-upload-info">
                    <p>{__('Selected file', 'post-expirator')}: {file.name}</p>
                    <p>{__('File size', 'post-expirator')}: {formatFileSize(file.size)}</p>
                </div>
            )}
        </div>
    );
};
