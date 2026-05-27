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

    return (
        <div className="pe-settings-tab__import-file-upload-preview">
            <Suspense fallback={lazyLoadLoading}>
                <ReactJson src={content} {...reactJSONParams} />
            </Suspense>
        </div>
    );
};
