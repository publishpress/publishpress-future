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

const focusColumnItem = (element) => {
    if (element) {
        element.focus();
    }
};

const getActiveItemInColumn = (column) => {
    if (! column) {
        return null;
    }

    return column.querySelector('.column-item[tabindex="0"]') || column.querySelector('.column-item');
};

const ColumnItemVariable = ({
    item,
    currentItemPath,
    onClick,
    onDoubleClick,
    onVariableHover,
    onVariableHoverEnd,
    path = [],
    index,
    columnIndex,
    itemsCount = 0,
}) => {
    const hasChildren = item.children && item.children.length > 0;
    const currentColumnIndex = path.length - 1;
    const selectedItemIndex = currentItemPath[currentColumnIndex];
    const isSelected = selectedItemIndex === index;

    // Roving tabindex: only one item per column is in the tab order.
    const columnHasValidSelection = selectedItemIndex !== undefined && selectedItemIndex < itemsCount;
    const isTabStop = columnHasValidSelection ? isSelected : index === 0;

    const stepSlug = item.name.split('.')[0];
    const stepSlugLabel = stepSlug ? `(${stepSlug})` : '';
    const showStepSlugLabel = columnIndex === 0 && stepSlug !== 'global';

    const selectItemAt = (targetIndex, element) => {
        const targetPath = [...path.slice(0, -1), targetIndex];
        onClick(targetPath, currentColumnIndex, targetIndex);
        focusColumnItem(element);
    };

    const onKeyDown = (event) => {
        const column = event.currentTarget.closest('.column');
        const items = column ? Array.from(column.querySelectorAll('.column-item')) : [];

        switch (event.key) {
            case 'Enter':
            case ' ':
            case 'Spacebar':
                event.preventDefault();
                onDoubleClick(item);
                break;

            case 'ArrowDown':
                event.preventDefault();
                if (index < items.length - 1) {
                    selectItemAt(index + 1, items[index + 1]);
                }
                break;

            case 'ArrowUp':
                event.preventDefault();
                if (index > 0) {
                    selectItemAt(index - 1, items[index - 1]);
                }
                break;

            case 'Home':
                event.preventDefault();
                selectItemAt(0, items[0]);
                break;

            case 'End':
                event.preventDefault();
                selectItemAt(items.length - 1, items[items.length - 1]);
                break;

            case 'ArrowRight':
                if (! hasChildren) {
                    break;
                }

                event.preventDefault();
                onClick(path, currentColumnIndex, index);

                // Wait for the next column to render.
                setTimeout(() => {
                    let nextColumn = column?.nextElementSibling;

                    while (nextColumn && ! nextColumn.classList.contains('column')) {
                        nextColumn = nextColumn.nextElementSibling;
                    }

                    focusColumnItem(getActiveItemInColumn(nextColumn));
                }, 0);
                break;

            case 'ArrowLeft': {
                let previousColumn = column?.previousElementSibling;

                while (previousColumn && ! previousColumn.classList.contains('column')) {
                    previousColumn = previousColumn.previousElementSibling;
                }

                if (previousColumn) {
                    event.preventDefault();
                    focusColumnItem(getActiveItemInColumn(previousColumn));
                }
                break;
            }
        }
    };

    return (
        <div
            role="option"
            aria-selected={isSelected}
            tabIndex={isTabStop ? 0 : -1}
            className={`column-item ${isSelected ? 'selected' : ''} ${hasChildren ? 'has-children' : ''}`}
            onClick={() => onClick(path, currentColumnIndex, index)}
            onDoubleClick={() => onDoubleClick(item)}
            onKeyDown={onKeyDown}
            onMouseEnter={(e) => onVariableHover?.(item, e)}
            onMouseLeave={() => onVariableHoverEnd?.()}
            onFocus={(e) => onVariableHover?.(item, e)}
            onBlur={() => onVariableHoverEnd?.()}
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
    columnIndex,
    itemsCount,
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
        itemsCount={itemsCount}
    />;
};

export default ColumnItem;
