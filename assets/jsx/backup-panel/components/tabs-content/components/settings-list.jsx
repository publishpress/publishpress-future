import { __ } from '@wordpress/i18n';
import { CheckboxControl, Spinner } from '@wordpress/components';
import { SelectableList } from '../../selectable-list';

export const SettingsList = ({
    items,
    label,
    isLoading,
    onCheckboxChange,
    checked,
    selectedItems,
    onSelectItems,
    className,
}) => {
    return <div className={className}>
        <CheckboxControl
            label={label}
            checked={checked && items.length > 0}
            onChange={onCheckboxChange}
            disabled={items.length === 0}
        />

        {items.length === 0 && !isLoading && (
            <p className="pe-settings-tab__export-no-items">{__('No items found.', 'post-expirator')}</p>
        )}

        {isLoading && (
            <p className="pe-settings-tab__export-loading">
                <Spinner />
                {__('Loading items, please wait...', 'post-expirator')}
            </p>
        )}

        {checked && items.length > 0 && (
            <div className="pe-settings-tab__backup-container">
                <SelectableList items={items} selectedItems={selectedItems} onSelect={onSelectItems} />
            </div>
        )}
    </div>;
};

export default SettingsList;
