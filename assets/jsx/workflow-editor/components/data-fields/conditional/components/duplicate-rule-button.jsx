import { Button, Dashicon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const DuplicateRuleButton = ({ handleOnClick, ...props }) => {
    return (
        <Button
            onClick={handleOnClick}
            variant="secondary"
            className="conditional-editor-modal-duplicate-rule"
            title={__('Duplicate Rule', 'post-expirator')}
        >
            <Dashicon icon="admin-page" size={16} />
        </Button>
    );
};
