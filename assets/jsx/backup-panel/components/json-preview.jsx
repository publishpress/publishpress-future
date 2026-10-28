import { __ } from '@wordpress/i18n';
import { lazy, Suspense } from '@wordpress/element';
const ReactJson = lazy(() => import('@microlink/react-json-view'));

export const JsonPreview = ({ content }) => {
    const reactJSONParams = {
        collapsed: 1,
        collapseStringsAfterLength: 50,
        displayDataTypes: false,
        displayObjectSize: false,
        enableClipboard: false,
    };

    const lazyLoadLoading = (
        <div>{__('Loading...', 'post-expirator')}</div>
    );

    let formattedJson = '';

    try {
        formattedJson = JSON.stringify(content, null, 2);
    } catch (error) {
        formattedJson = String(content);
    }

    // The tree viewer's expand/collapse toggles can't be reached by keyboard,
    // so the full formatted JSON is also available in a native <details>.
    return (
        <div className="pe-settings-tab__import-file-upload-preview">
            <Suspense fallback={lazyLoadLoading}>
                <ReactJson src={content} {...reactJSONParams} />
            </Suspense>

            <details className="pe-settings-tab__import-file-upload-preview-text" style={{ marginTop: '12px' }}>
                <summary>{__('Show the full data as text', 'post-expirator')}</summary>
                <pre
                    tabIndex={0}
                    role="region"
                    aria-label={__('Full data as JSON', 'post-expirator')}
                    style={{ maxHeight: '400px', overflow: 'auto', whiteSpace: 'pre', margin: '8px 0 0' }}
                >
                    {formattedJson}
                </pre>
            </details>
        </div>
    );
};
