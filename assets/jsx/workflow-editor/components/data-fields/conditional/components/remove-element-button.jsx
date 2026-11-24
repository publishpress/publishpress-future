import { Button, Dashicon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const RemoveElementButton = ({ label, title, handleOnClick }) => {
    // react-querybuilder passes the visible text as `label` and the tooltip as `title`.
    const accessibleLabel = title || (typeof label === 'string' ? label : '') || __('Remove', 'post-expirator');

    return <Button
        onClick={handleOnClick}
        variant="secondary"
        className="conditional-editor-modal-remove-element"
        label={accessibleLabel}
        showTooltip
    >
        <Dashicon icon="trash" size={16} />
    </Button>;
};
