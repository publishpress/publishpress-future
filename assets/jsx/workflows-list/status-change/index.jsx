import { useEffect, useCallback } from '@wordpress/element';
import { __ } from '@publishpress/i18n';

const StatusChangeHandler = () => {
    const handleStatusChangeClick = useCallback((e) => {
        e.preventDefault();
        const form = e.target.closest('form');
        if (form) {
            form.submit();
        }
    }, []);

    useEffect(() => {
        const statusLinks = document.querySelectorAll('.pp-future-workflow-status-change');
        statusLinks.forEach(link => {
            link.addEventListener('click', handleStatusChangeClick);
        });
        return () => {
            statusLinks.forEach(link => {
                link.removeEventListener('click', handleStatusChangeClick);
            });
        };
    }, [handleStatusChangeClick]);

    return null;
};

document.addEventListener('DOMContentLoaded', () => {
    const root = createRoot(document.createElement('div'));
    root.render(<StatusChangeHandler />);
});