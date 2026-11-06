import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// react-querybuilder passes the accessible text as `title`, not `label`.
export const CombinatorSelector = ({ label, title, value, options, handleOnChange }) => {
    return <SelectControl
        label={label || title || __('Combinator', 'post-expirator')}
        hideLabelFromVision
        value={value}
        options={options}
        onChange={handleOnChange}
    />;
};
