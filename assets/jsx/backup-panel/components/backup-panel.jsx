import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

import ImportTab from './tabs-content/import';
import ExportTab from './tabs-content/export';
import { SnackbarList } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';

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

/**
 * Renders the export/import notices from the core/notices store. Snackbar
 * announces each notice through wp.a11y when it appears.
 */
const BackupNotices = () => {
    const notices = useSelect((select) => select('core/notices').getNotices(), []);
    const { removeNotice } = useDispatch('core/notices');

    const snackbarNotices = (notices || []).filter((notice) => notice.type === 'snackbar');

    return (
        <SnackbarList
            notices={snackbarNotices}
            className="pe-settings-tab__snackbar-list"
            onRemove={(noticeId) => removeNotice(noticeId)}
        />
    );
};

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

    let content = null;

    if (activeTab === 'import') {
        content = <ImportTab />;
    }

    if (activeTab === 'export') {
        content = <ExportTab />;
    }

    return (
        <>
            {content}
            <BackupNotices />
        </>
    );
};

export default BackupPanel;
