import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { queryFields } from './query-fields';

/**
 * Field selector component for post queries
 */
export const PostFieldSelector = ({ value, handleOnChange, options, context, ...props }) => {
    return <SelectControl
        value={value}
        options={queryFields}
        onChange={handleOnChange}
    />;
};
