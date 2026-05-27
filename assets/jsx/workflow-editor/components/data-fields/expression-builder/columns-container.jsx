import { useState, useCallback } from "@wordpress/element";
import { ColumnItem } from "./column-item";
import { __, sprintf } from "@wordpress/i18n";
import { processItemWithTypeHandler } from "./type-handlers";

const RenderColumns = ({
    currentItemPath,
    currentItems,
    onClick,
    setCurrentDescription,
    onDoubleClick,
    path = [],
    setCurrentVariableId,
    columnIndex
}) => {
    if (!currentItems) return null;

    const currentColumnIndex = path.length;
    const selectedItemIndex = currentItemPath[currentColumnIndex];
    let currentItem = processItemWithTypeHandler(currentItems[selectedItemIndex]);

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
                        setCurrentDescription={setCurrentDescription}
                        setCurrentVariableId={setCurrentVariableId}
                        onDoubleClick={onDoubleClick}
                        path={[...path, index]}
                        index={index}
                        columnIndex={currentColumnIndex}
                    />;
                })}
            </div>

            {selectedItemIndex !== undefined && currentItem?.children && (
                <RenderColumns
                    currentItemPath={currentItemPath}
                    currentItems={currentItem.children}
                    path={[...path, selectedItemIndex]}
                    onClick={onClick}
                    setCurrentDescription={setCurrentDescription}
                    setCurrentVariableId={setCurrentVariableId}
                onDoubleClick={onDoubleClick}
                />
            )}
        </>
    );
};

export const ColumnsContainer = ({
    items,
    setCurrentDescription,
    onDoubleClick,
    setCurrentVariableId
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
                setCurrentDescription={setCurrentDescription}
                setCurrentVariableId={setCurrentVariableId}
            />
        </div>
    );
};

export default ColumnsContainer;
