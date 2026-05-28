import { useEffect, useCallback, createRoot } from '@wordpress/element';
const CopyWorkflowHandler = () => {
    const handleCopyClick = useCallback((e) => {
        e.preventDefault();
        const form = e.target.closest('form');
        if (form) {
            form.submit();
        }
    }, []);

    useEffect(() => {
        const copyLinks = document.querySelectorAll('.pp-future-workflow-copy');
        copyLinks.forEach(link => {
            link.addEventListener('click', handleCopyClick);
        });
        return () => {
            copyLinks.forEach(link => {
                link.removeEventListener('click', handleCopyClick);
            });
        };
    }, [handleCopyClick]);

    return null;
};

document.addEventListener('DOMContentLoaded', () => {
    const root = createRoot(document.createElement('div'));
    root.render(<CopyWorkflowHandler />);
});
