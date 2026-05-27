import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '../../../editor-store';
import { __ } from '@wordpress/i18n';
import { CheckboxControl } from '@wordpress/components';

export const NotToggle = ({ checked, handleOnChange }) => {
    const [isNot, setIsNot] = useState(checked || false);

    useEffect(() => {
        setIsNot(checked || false);
    }, [checked]);

    const handleToggle = () => {
        const newValue = !isNot;
        setIsNot(newValue);
        handleOnChange(newValue);
    }

    const {
        currentConditionalQuery,
    } = useSelect((select) => ({
        currentConditionalQuery: select(editorStore).getCurrentConditionalQuery(),
    }));

    return (
        <>
            {currentConditionalQuery && currentConditionalQuery.rules.length > 0 && (
                <CheckboxControl
                    label={__('Not', 'post-expirator')}
                    checked={isNot}
                    onChange={handleToggle}
                    className={isNot ? 'is-checked' : ''}
                />
            )}
        </>
    );
};
