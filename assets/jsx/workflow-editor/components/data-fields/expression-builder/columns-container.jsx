import { useState, useCallback } from "@wordpress/element";
import { ColumnItem } from "./column-item";
import { processItemWithTypeHandler } from "./type-handlers";

const RenderColumns = ({
    currentItemPath,
    currentItems,
    onClick,
    onDoubleClick,
    onVariableHover,
    onVariableHoverEnd,
    path = [],
    columnIndex,
    renderLeafHint
}) => {
    if (!currentItems) return null;

    const currentColumnIndex = path.length;
    const selectedItemIndex = currentItemPath[currentColumnIndex];
    let currentItem = processItemWithTypeHandler(currentItems[selectedItemIndex]);
    const hasChildren = currentItem?.children && currentItem.children.length > 0;
    const isFormItem = currentItem?.type === 'meta-key-input';

    return (
        <>
            <div className="column" key={`column-${path.join('-')}`}>
                {currentItems.map((item, index) => {
                    item = processItemWithTypeHandler(item);

                    return <ColumnItem
                        key={`column-item-${path.join('-')}-${index}`}
                        item={item}
                        currentItemPath={currentItemPath}
                        onClick={onClick}
                        onDoubleClick={onDoubleClick}
                        onVariableHover={onVariableHover}
                        onVariableHoverEnd={onVariableHoverEnd}
                        path={[...path, index]}
                        index={index}
                        columnIndex={currentColumnIndex}
                    />;
                })}
            </div>

            {selectedItemIndex !== undefined && hasChildren && (
                <RenderColumns
                    currentItemPath={currentItemPath}
                    currentItems={currentItem.children}
                    path={[...path, selectedItemIndex]}
                    onClick={onClick}
                    onDoubleClick={onDoubleClick}
                    onVariableHover={onVariableHover}
                    onVariableHoverEnd={onVariableHoverEnd}
                    renderLeafHint={renderLeafHint}
                />
            )}

            {selectedItemIndex !== undefined && currentItem && !hasChildren && !isFormItem && renderLeafHint && (
                <div className="column column-leaf-hint">
                    {renderLeafHint(currentItem)}
                </div>
            )}
        </>
    );
};

export const ColumnsContainer = ({
    items,
    onDoubleClick,
    onVariableHover,
    onVariableHoverEnd,
    renderLeafHint
}) => {
    const [currentItemPath, setCurrentItemPath] = useState([]);

    const onClick = useCallback((path, currentColumnIndex, index) => {
        // Remove the items from the path that are after the current column index
        const newPath = path.slice(0, currentColumnIndex + 1);
        newPath.push(index);

        setCurrentItemPath(newPath);

        const container = document.querySelector('.columns-container');
        if (container) {
            setTimeout(() => {
                container.scrollLeft = container.scrollWidth;
            }, 0);
        }
    }, [setCurrentItemPath]);

    return (
        <div className="columns-container">
            <RenderColumns
                currentItems={items}
                currentItemPath={currentItemPath}
                onClick={onClick}
                onDoubleClick={onDoubleClick}
                onVariableHover={onVariableHover}
                onVariableHoverEnd={onVariableHoverEnd}
                renderLeafHint={renderLeafHint}
            />
        </div>
    );
};

export default ColumnsContainer;
