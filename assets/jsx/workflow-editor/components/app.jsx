import { useEffect, StrictMode } from "@wordpress/element";
import { WorkflowEditorLayout } from "./layout/layout";
import { useDispatch } from "@wordpress/data";
import { store as workflowStore } from "./workflow-store";
import { store as editorStore } from "./editor-store";
import {
    PostData,
    BooleanData,
    DatetimeData,
    IntegerData,
    StringData,
    EmailData,
    InputData,
    WorkflowData,
    UserData,
    SiteData,
    NodeData,
    ArrayData,
    FutureActionData,
    TermsArrayData,
    MetaData,
    PostStatusData,
    PostTypeData,
    UrlData,
    UserRolesData,
    PostTermsData,
} from "./data-types";

const { workflowId, nodeTypeCategories, nodeTypes } = window.futureWorkflowEditor;

import "../css/index.css";
import 'reactflow/dist/style.css';

export default function WorkflowEditorApp() {
    const {
        setupEditor,
        setDataTypes,
    } = useDispatch(workflowStore);

    const {
        setTriggerNodes,
        setTriggerCategories,
        setActionCategories,
        setActionNodes,
        setAdvancedCategories,
        setAdvancedNodes,
    } = useDispatch(editorStore);

    useEffect(() => {
        setupEditor(workflowId);

        setTriggerNodes(nodeTypes.triggers);
        setActionNodes(nodeTypes.actions);
        setAdvancedNodes(nodeTypes.advanced);

        setTriggerCategories(nodeTypeCategories);
        setActionCategories(nodeTypeCategories);
        setAdvancedCategories(nodeTypeCategories);

        const dataTypes = [
            PostData(),
            BooleanData(),
            DatetimeData(),
            IntegerData(),
            StringData(),
            EmailData(),
            InputData(),
            WorkflowData(),
            UserData(),
            SiteData(),
            NodeData(),
            ArrayData(),
            FutureActionData(),
            TermsArrayData(),
            MetaData(),
            PostStatusData(),
            PostTypeData(),
            UrlData(),
            UserRolesData(),
            PostTermsData()
        ]
        setDataTypes(dataTypes);
    }, [workflowId]);

    return (
        <StrictMode>
            <WorkflowEditorLayout />
        </StrictMode>
    );
};
