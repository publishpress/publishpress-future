import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { SnackbarList } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';

const TabbedWindow = ({
    defaultTab = '',
    tabs = [
        {
            label: 'Tab 1',
            value: 'tab1',
        },
        {
            label: 'Tab 2',
            value: 'tab2',
        },
    ],
    onChange = () => {},
    children,
    noticeAutoDismissTimeout = 5000,
}) => {
    const getCurrentTabFromUrl = () => {
        const hash = window.location.hash.replace('#', '');
        const theTab = tabs.find((tab) => tab.value === hash) || defaultTab;

        if (typeof theTab === 'object') {
            return theTab.value;
        }

        return theTab;
    };

    const [activeTab, setActiveTab] = useState(getCurrentTabFromUrl());

    const notices = useSelect(select => select('core/notices').getNotices());

    const { removeNotice } = useDispatch('core/notices');

    // Listen for hash changes
    useEffect(() => {
        const handleHashChange = () => {
            const newTab = getCurrentTabFromUrl();
            setActiveTab(newTab);
        };

        window.addEventListener('hashchange', handleHashChange);
        return () => window.removeEventListener('hashchange', handleHashChange);
    }, []);

    //Autodismiss notices
    useEffect(() => {
        notices.forEach((notice) => {
            setTimeout(() => {
                removeNotice(notice.id);
            }, noticeAutoDismissTimeout);
        });
    }, [notices]);

    useEffect(() => {
        window.history.replaceState(null, '', `#${activeTab}`);

        onChange(activeTab);
    }, [activeTab]);

    return (
        <>
            <div id="pe-settings-tabs">
                <nav className="nav-tab-wrapper postexpirator-nav-tab-wrapper" id="postexpirator-nav">
                    {tabs.map((tab) => (
                        <a
                            key={tab.value}
                            href="#"
                            className={`pe-tab nav-tab ${activeTab === tab.value ? 'nav-tab-active' : ''}`}
                            data-tab={tab.value}
                            onClick={(e) => {
                                e.preventDefault();
                                setActiveTab(tab.value);
                            }}
                        >
                            {tab.label}
                        </a>
                    ))}
                </nav>
                {children}
            </div>
            <SnackbarList
                notices={notices}
                className="pe-settings-tab__snackbar-list"
                onRemove={(noticeId) => {
                    removeNotice(noticeId);
                }}
            />
        </>
    );
};

export default TabbedWindow;
