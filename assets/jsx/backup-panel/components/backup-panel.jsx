import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

import ImportTab from './tabs-content/import';
import ExportTab from './tabs-content/export';
import TabbedWindow from './tabbed-window';

const tabs = [
    {
        label: __('Export', 'post-expirator'),
        value: 'export',
    },
    {
        label: __('Import', 'post-expirator'),
        value: 'import',
    },
];

const BackupPanel = () => {
    const [activeTab, setActiveTab] = useState();

    const getTabFromUrl = () => {
        const url = new URL(window.location.href);
        const tab = url.searchParams.get('tab');

        if (tab && tabs.some((t) => t.value === tab)) {
            return tab;
        }

        return tabs[0].value;
    };

    useEffect(() => {
        const tab = getTabFromUrl();

        setActiveTab(tab);
    }, []);

    if (! activeTab) {
        return null;
    }

    if (activeTab === 'import') {
        return <ImportTab />;
    }

    if (activeTab === 'export') {
        return <ExportTab />;
    }
};

export default BackupPanel;
