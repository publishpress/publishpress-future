import { __ } from '@wordpress/i18n';
import { Button, CheckboxControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

export const SelectableList = ({ items, selectedItems, onSelect }) => {
    const handleSelectAll = () => {
        onSelect(items.map((item) => item.id));
    };

    const handleUnselectAll = () => {
        onSelect([]);
    };

    useEffect(() => {
        handleSelectAll();
    }, []);

    return (
        <>
            <div>
                <span className="pe-settings-tab__backup-actions">
                    <Button isLink onClick={handleSelectAll}>{__('Select all', 'post-expirator')}</Button> |
                    <Button isLink onClick={handleUnselectAll}>{__('Unselect all', 'post-expirator')}</Button>
                </span>
            </div>

            <ul>
                {items.map((item) => (
                    <li key={item.id}>
                        <CheckboxControl
                            label={(
                                <>
                                    {item.title}

                                    {item.status && (
                                        <span className="pe-settings-tab__backup-status">[{item.status}]</span>
                                    )}
                                </>
                            )}
                            checked={selectedItems.includes(item.id)}
                            onChange={(value) => {
                                if (value) {
                                    onSelect([...selectedItems, item.id]);
                                } else {
                                    onSelect(selectedItems.filter((id) => id !== item.id));
                                }
                            }}
                        />
                    </li>
                ))}
            </ul>
        </>
    );
};

export default SelectableList;
