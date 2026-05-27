import { PanelRow } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import PersistentPanelBody from "../persistent-panel-body";
import { lazy, Suspense } from "@wordpress/element";

const ReactJson = lazy(() => import('@microlink/react-json-view'));

export function NodeDevInfoPanel({node, nodeType}) {
    const reactJSONParams = {
        collapsed: 1,
        collapseStringsAfterLength: 50,
        displayDataTypes: false,
        displayObjectSize: false,
        enableClipboard: false,
    };

    const lazyLoadLoading = (
        <PanelRow><div>{__('Loading...', 'post-expirator')}</div></PanelRow>
    );

    return (
        <PersistentPanelBody
            title={__('Developer Info', 'post-expirator')}
            icon={'admin-tools'}
            className="workflow-editor-dev-info-panel workflow-editor-dev-panel"
        >
            {node && (
                <>
                    <PanelRow>
                        <div>
                            <h3>{__('Node', 'post-expirator')}</h3>
                            <div className="workflow-editor-dev-info-wrapper">
                                <Suspense fallback={lazyLoadLoading}>
                                    <ReactJson src={node} {...reactJSONParams} collapsed={true} />
                                </Suspense>
                            </div>
                        </div>
                    </PanelRow>
                    <PanelRow>
                        <div>
                            <h3>{__('Node Data', 'post-expirator')}</h3>
                            <div className="workflow-editor-dev-info-wrapper">
                                <Suspense fallback={lazyLoadLoading}>
                                    <ReactJson src={node?.data} {...reactJSONParams} collapsed={true} />
                                </Suspense>
                            </div>
                        </div>
                    </PanelRow>
                    <PanelRow>
                        <div>
                            <h3>{__('Node Settings', 'post-expirator')}</h3>
                            <div className="workflow-editor-dev-info-wrapper">
                                <Suspense fallback={lazyLoadLoading}>
                                    <ReactJson src={node?.data?.settings} {...reactJSONParams} collapsed={true} />
                                </Suspense>
                            </div>
                        </div>
                    </PanelRow>
                </>
            )}

            {nodeType && (
                <PanelRow>
                    <div>
                        <h3>{__('Node Type', 'post-expirator')}</h3>
                        <div className="workflow-editor-dev-info-wrapper">
                            <Suspense fallback={lazyLoadLoading}>
                                <ReactJson src={nodeType} {...reactJSONParams} collapsed={true} />
                            </Suspense>
                        </div>
                    </div>
                </PanelRow>
            )}
        </PersistentPanelBody>
    );
}

export default NodeDevInfoPanel;
