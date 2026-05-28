import { HTML_ELEMENT_ID } from "./constants";
import { createRoot } from '@wordpress/element';
import { Suspense, lazy } from 'react';

const container = document.getElementById(HTML_ELEMENT_ID);

const WorkflowEditorApp = lazy(() => import('./components/app'));

if (container) {
    createRoot(container).render(
        <Suspense fallback={<div className="workflow-editor-loading">Loading...</div>}>
            <WorkflowEditorApp />
        </Suspense>
    );
}
