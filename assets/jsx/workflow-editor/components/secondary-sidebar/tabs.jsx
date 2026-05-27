/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';
import { TabPanel } from '../tab-panel';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
    INSERTER_TAB_ACTIONS,
    INSERTER_TAB_TRIGGERS
} from '../../constants';

const triggersTab = {
    name: INSERTER_TAB_TRIGGERS,
    /* translators: Blocks tab title in the block inserter. */
    title: __('Triggers', 'post-expirator'),
};
const actionsTabs = {
    name: INSERTER_TAB_ACTIONS,
    /* translators: Patterns tab title in the block inserter. */
    title: __('Actions', 'post-expirator'),
};

function InserterTabs({
    children,
    onSelect,
    initialTabName = INSERTER_TAB_TRIGGERS,
}) {
    const tabs = useMemo(() => {
        const tempTabs = [triggersTab, actionsTabs];

        return tempTabs;
    }, [
        triggersTab,
        actionsTabs,
    ]);

    return (
        <TabPanel
            className="block-editor-inserter__tabs"
            tabs={tabs}
            onSelect={onSelect}
            initialTabName={initialTabName}
        >
            {children}
        </TabPanel>
    );
}

export default InserterTabs;
