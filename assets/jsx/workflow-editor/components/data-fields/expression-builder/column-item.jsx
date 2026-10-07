import { __, sprintf } from "@wordpress/i18n";
import { useState } from "@wordpress/element";
import { TextControl, Button } from "@wordpress/components";

const ColumnItemMeta = ({ item, onClick }) => {
    const [metaKey, setMetaKey] = useState('');

    let metaDescription = sprintf(
        /* translators: %s is the database table name */
        __('Type the %s key and click on the button to insert it.', 'post-expirator'),
        item.context?.table || 'meta'
    );

    const metaItem = {
        id: `{{${item.name}.${metaKey}}}`,
        name: item.name + '.' + metaKey,
        label: __('Metadata key', 'post-expirator'),
        description: metaDescription,
        context: item.context
    }

    return (
        <div className="column-item-form">
            <TextControl
                label={item.label}
                value={metaKey}
                onChange={(value) => setMetaKey(value)}
                help={item.description}
            />
            <Button variant="secondary" onClick={() => {onClick(metaItem)}}>
                {__('Insert', 'post-expirator')}
            </Button>
        </div>
    );
}

const ColumnItemVariable = ({
    item,
    currentItemPath,
    onClick,
    onDoubleClick,
    onVariableHover,
    onVariableHoverEnd,
    path = [],
    index,
    columnIndex
}) => {
    const hasChildren = item.children && item.children.length > 0;
    const currentColumnIndex = path.length - 1;
    const selectedItemIndex = currentItemPath[currentColumnIndex];

    const stepSlug = item.name.split('.')[0];
    const stepSlugLabel = stepSlug ? `(${stepSlug})` : '';
    const showStepSlugLabel = columnIndex === 0 && stepSlug !== 'global';

    return (
        <div
            className={`column-item ${selectedItemIndex === index ? 'selected' : ''} ${hasChildren ? 'has-children' : ''}`}
            onClick={() => onClick(path, currentColumnIndex, index)}
            onDoubleClick={() => onDoubleClick(item)}
            onMouseEnter={(e) => onVariableHover?.(item, e)}
            onMouseLeave={() => onVariableHoverEnd?.()}
        >
            {item.label} {showStepSlugLabel ? <span className="column-item-step-slug">{stepSlugLabel}</span> : ''}
        </div>
    );
};

export const ColumnItem = ({
    item,
    currentItemPath,
    onClick,
    onDoubleClick,
    onVariableHover,
    onVariableHoverEnd,
    path = [],
    index,
    columnIndex
}) => {
    if (item?.type === 'meta-key-input') {
        return <ColumnItemMeta item={item} onClick={onDoubleClick} />;
    }

    return <ColumnItemVariable
        item={item}
        currentItemPath={currentItemPath}
        onClick={onClick}
        onDoubleClick={onDoubleClick}
        onVariableHover={onVariableHover}
        onVariableHoverEnd={onVariableHoverEnd}
        path={path}
        index={index}
        columnIndex={columnIndex}
    />;
};

export default ColumnItem;
