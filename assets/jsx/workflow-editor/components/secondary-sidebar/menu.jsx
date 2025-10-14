import { useState, useCallback, useMemo } from '@wordpress/element';
import { __ } from "@publishpress/i18n";
import { VisuallyHidden, SearchControl } from '@wordpress/components';
import { useSelect, select } from '@wordpress/data';
import { Tips } from './tips';
import { NodesTab } from './nodes-tab';
import InserterTabs from './tabs';
import {
    INSERTER_TAB_ACTIONS,
    INSERTER_TAB_TRIGGERS
} from '../../constants';
import { store as editorStore } from '../editor-store';
import { useDispatch } from '@wordpress/data';
import InserterSearchResults from './inserter-search-results';
import { Popover } from '@wordpress/components';
import InserterPreviewPanel from './inserter-preview-panel';

export function InserterMenu({
    onSelect,
    showInserterHelpPanel,
    showMostUsedNodes,
    __experimentalFilterValue = '',
    shouldFocusNode = true,
}) {
    const [filterValue, setFilterValue] = useState(
        __experimentalFilterValue
    );

    const {
        currentInserterTab,
        hoveredItem,
    } = useSelect((select) => {
        return {
            currentInserterTab: select(editorStore).getCurrentInserterTab(),
            hoveredItem: select(editorStore).getHoveredItem(),
        };
    });

    const {
        setCurrentInserterTab,
        setHoveredItem,
    } = useDispatch(editorStore);

    const onInsert = useCallback((blocks, meta, shouldForceFocusBlock) => {
        onSelect();
    }, [onSelect]);

    const onHover = useCallback((item) => {
        setHoveredItem(item);
    }, [setHoveredItem]);

    const triggersTab = useMemo(
        () => {
            const items = select(editorStore).getTriggerNodes();
            const categories = select(editorStore).getTriggerCategories();

            return (
                <>
                    <div className="block-editor-inserter__block-list">
                        <NodesTab
                            type={INSERTER_TAB_TRIGGERS}
                            onInsert={onInsert}
                            onHover={onHover}
                            showMostUsedNodes={showMostUsedNodes}
                            items={items}
                            categories={categories}
                        />
                    </div>
                    {showInserterHelpPanel && (
                        <div className="block-editor-inserter__tips">
                            <VisuallyHidden as="h2">
                                {__('A tip for using the workflow editor', 'post-expirator')}
                            </VisuallyHidden>
                            <Tips />
                        </div>
                    )}
                </>
            );
        },
        [
            onInsert,
            onHover,
            filterValue,
            showMostUsedNodes,
            showInserterHelpPanel,
        ]
    );

    const actionsTab = useMemo(
        () => {
            const actionItems = select(editorStore).getActionNodes();
            const advancedItems = select(editorStore).getAdvancedNodes();
            const items = [...actionItems, ...advancedItems];
            const categories = select(editorStore).getActionCategories();

            return (
                <>
                    <div className="block-editor-inserter__block-list">
                        <NodesTab
                            type={INSERTER_TAB_ACTIONS}
                            onInsert={onInsert}
                            onHover={onHover}
                            showMostUsedNodes={showMostUsedNodes}
                            items={items}
                            categories={categories}
                        />
                    </div>
                    {showInserterHelpPanel && (
                        <div className="block-editor-inserter__tips">
                            <VisuallyHidden as="h2">
                                {__('A tip for using the workflow editor', 'post-expirator')}
                            </VisuallyHidden>
                            <Tips />
                        </div>
                    )}
                </>
            );
        },
        [
            onInsert,
            onHover,
            filterValue,
            showMostUsedNodes,
            showInserterHelpPanel,
        ]
    );


    const getCurrentTab = useCallback(
        (tab) => {
            if (tab.name === INSERTER_TAB_TRIGGERS) {
                return triggersTab;
            }

            if (tab.name === INSERTER_TAB_ACTIONS) {
                return actionsTab;
            }
        },
        [triggersTab, actionsTab]
    );

    const onSelectTab = useCallback(
        (tab) => {
            setCurrentInserterTab(tab);
        },
        [setCurrentInserterTab]
    );

    return (
        <div className="block-editor-inserter__menu">
            <div className="block-editor-inserter__main-area show-as-tabs">
                { /* the following div is necessary to fix the sticky position of the search form */}
                <div className="block-editor-tabbed-sidebar">
                    <SearchControl
                        className="block-editor-inserter__search"
                        onChange={(value) => {
                            if (hoveredItem) setHoveredItem(null);
                            setFilterValue(value);
                        }}
                        value={filterValue}
                        label={__('Search for triggers and steps', 'post-expirator')}
                        placeholder={__('Search')}
                    />
                    <div className="block-editor-tabbed-sidebar__tabpanel">
                        {!!filterValue && (
                            <InserterSearchResults
                                filterValue={filterValue}
                                onSelect={onSelect}
                                onHover={onHover}
                            />
                        )}

                        {!filterValue && (
                            <InserterTabs
                                onSelect={onSelectTab}
                                initialTabName={currentInserterTab}
                            >
                                {getCurrentTab}
                            </InserterTabs>
                        )}
                    </div>
                </div>
            </div>
            {showInserterHelpPanel && hoveredItem && (
                <Popover
                    className='block-editor-inserter__preview-container__popover'
                    placement='right-start'
                    offset={16}
                    focusOnMount={false}
                    animate={false}
                >
                    <InserterPreviewPanel item={hoveredItem} />
                </Popover>
            )}
        </div>
    );
}
