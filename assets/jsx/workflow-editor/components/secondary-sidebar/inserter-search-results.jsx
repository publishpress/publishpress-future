/**
 * External dependencies
 */
import orderBy from 'lodash/orderBy';
import isEmpty from 'lodash/isEmpty';

/**
 * WordPress dependencies
 */
import { useMemo, useEffect } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { VisuallyHidden } from '@wordpress/components';
import { useDebounce } from '@wordpress/compose';
import { speak } from '@wordpress/a11y';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import NodeTypesList from './node-types-list';
import InserterPanel from './panel';
import { searchNodeItems } from './search-items';
import { InserterNoResults } from './inserter-no-results';
import { store as editorStore } from '../editor-store';
import InserterListbox from './inserter-listbox';

export const InserterSearchResults = ({
    filterValue,
    onSelect,
    onHover,
    maxNodeTypes,
    isDraggable = true,
    filterTypes,
}) => {
    const debouncedSpeak = useDebounce(speak, 500);

    const {
        nodeTypes,
        nodeTypeCategories,
    } = useSelect((select) => {
        const actionNodes = select(editorStore).getActionNodes();
        const actionCategories = select(editorStore).getActionCategories();
        const triggerNodes = select(editorStore).getTriggerNodes();
        const triggerCategories = select(editorStore).getTriggerCategories();
        const advancedNodes = select(editorStore).getAdvancedNodes();
        const advancedCategories = select(editorStore).getAdvancedCategories();

        let nodeTypes = [];

        if (filterTypes && filterTypes.length) {
            nodeTypes = [
                ...actionNodes.filter((node) => filterTypes.includes(node.elementaryType)),
                ...triggerNodes.filter((node) => filterTypes.includes(node.elementaryType)),
                ...advancedNodes.filter((node) => filterTypes.includes(node.elementaryType)),
            ];
        } else {
            nodeTypes = [
                ...triggerNodes,
                ...actionNodes,
                ...advancedNodes,
            ];
        }

        const nodeTypeCategories = [
            ...actionCategories,
            ...triggerCategories,
            ...advancedCategories,
        ];

        return {
            nodeTypes: nodeTypes,
            nodeTypeCategories: nodeTypeCategories,
        };
    });

    const filteredNodeTypes = useMemo(() => {
        const results = searchNodeItems(
            orderBy(nodeTypes, ['frecency'], ['desc']),
            nodeTypeCategories,
            filterValue
        );

        return maxNodeTypes !== undefined
            ? results.slice(0, maxNodeTypes)
            : results;
    }, [
        filterValue,
        nodeTypes,
        nodeTypeCategories,
        maxNodeTypes,
    ]);

    // Announce search results on change
    useEffect(() => {
        if (!filterValue) {
            return;
        }
        const count = filteredNodeTypes.length;
        const resultsFoundMessage = sprintf(
            /* translators: %d: number of results. */
            _n('%d result found.', '%d results found.', count, 'post-expirator'),
            count
        );
        debouncedSpeak(resultsFoundMessage);
    }, [filterValue, debouncedSpeak, filteredNodeTypes]);

    const hasItems = !isEmpty(filteredNodeTypes);

    return (
        <InserterListbox>
            {!hasItems && <InserterNoResults />}

            {!!hasItems && (
                <InserterPanel
                    title={
                        <VisuallyHidden>{__('Nodes', 'post-expirator')}</VisuallyHidden>
                    }
                >
                    <NodeTypesList
                        items={filteredNodeTypes}
                        onSelect={onSelect}
                        onHover={onHover}
                        label={__('Nodes', 'post-expirator')}
                        isDraggable={isDraggable}
                    />
                </InserterPanel>
            )}
        </InserterListbox>
    );
}

export default InserterSearchResults;
