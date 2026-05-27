import { JsonPreview } from '../../../json-preview';
import {
    Button,
    __experimentalHStack as HStack,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';


export const TabPreview = ({ content, onCancel }) => {
    return <>
        <JsonPreview content={content} />
        <HStack className="pe-settings-tab__import-file-upload-actions">
            <Button variant="secondary" onClick={onCancel}>
                {__('Cancel and select a different file', 'post-expirator')}
            </Button>
        </HStack>
    </>;
};

export default TabPreview;
