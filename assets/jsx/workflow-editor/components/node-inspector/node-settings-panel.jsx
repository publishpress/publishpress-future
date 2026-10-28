import { PanelRow, VisuallyHidden } from "@wordpress/components";
import { store as workflowStore } from "../workflow-store";
import { store as editorStore } from "../editor-store";
import { useDispatch, useSelect } from "@wordpress/data";
import { useMemo, useRef, useEffect } from "@wordpress/element";
import { getNodeVariablesTree, getExpandedStepScopedVariables } from "../../utils";
import MappedField from "./mapped-field";
import PersistentPanelBody from "../persistent-panel-body";

const FIELD_CONTROLS_SELECTOR = 'input:not([type="hidden"]), select, textarea, .ace_text-input';

/**
 * Wraps a settings field and flags its controls with aria-invalid when
 * the validation found errors for that field. Works with any field component.
 */
const FieldErrorState = ({ fieldId, errorMessages = [], children }) => {
    const wrapperRef = useRef(null);
    const hasErrors = errorMessages.length > 0;
    const errorId = `workflow-editor-field-error-${fieldId}`;
    const errorsKey = errorMessages.join('|');

    useEffect(() => {
        const wrapper = wrapperRef.current;

        if (! wrapper) {
            return;
        }

        const applyState = () => {
            wrapper.querySelectorAll(FIELD_CONTROLS_SELECTOR).forEach((control) => {
                const describedBy = (control.getAttribute('aria-describedby') || '')
                    .split(' ')
                    .filter((id) => id && id !== errorId);

                if (hasErrors) {
                    control.setAttribute('aria-invalid', 'true');
                    describedBy.push(errorId);
                } else {
                    control.removeAttribute('aria-invalid');
                }

                if (describedBy.length > 0) {
                    control.setAttribute('aria-describedby', describedBy.join(' '));
                } else {
                    control.removeAttribute('aria-describedby');
                }
            });
        };

        applyState();

        // Some fields render their controls later (e.g. the Ace editor).
        const observer = new MutationObserver(applyState);
        observer.observe(wrapper, { childList: true, subtree: true });

        return () => observer.disconnect();
    }, [hasErrors, errorsKey, errorId]);

    return (
        <div ref={wrapperRef} className="workflow-editor-field-error-state" style={{ display: 'contents' }}>
            {children}
            {hasErrors && (
                <VisuallyHidden id={errorId}>{errorMessages.join(' ')}</VisuallyHidden>
            )}
        </div>
    );
};

export const NodeSettingsPanel = ({ node }) => {
    const {
        globalVariables,
        nodeType,
        nodeErrors,
    } = useSelect((select) => {
        return {
            globalVariables: select(workflowStore).getGlobalVariables(),
            nodeType: select(editorStore).getNodeTypeByName(node?.data?.name),
            nodeErrors: select(workflowStore).getNodeErrors(node?.id) || {},
        }
    });

    const getFieldErrorMessages = (fieldName) => {
        return Object.keys(nodeErrors || {})
            .filter((errorKey) => errorKey === fieldName || errorKey.startsWith(`${fieldName}-`))
            .map((errorKey) => nodeErrors[errorKey]?.message)
            .filter(Boolean);
    };

    const {
        updateNode
    } = useDispatch(workflowStore);

    const onChangeSetting = (fieldName, value) => {
        const newNode = {
            id: node.id,
            data: {
                settings: {
                    [fieldName]: value,
                },
            },
        };

        updateNode(newNode);
    };

    let nodeSettings = node.data?.settings;

    if (!nodeSettings) {
        nodeSettings = {};
    }
    const settingsSchema = nodeType?.settingsSchema || {};

    const variableListOptions = getNodeVariablesTree(node, globalVariables);
    const stepScopedVariables = getExpandedStepScopedVariables(node);

    const settingsPanels = useMemo(() => {
        return settingsSchema.map((settingPanel) => {
            return (
                <PersistentPanelBody title={settingPanel.label} key={`settings-panel-${node.id}-${settingPanel.label}`}>
                    {settingPanel?.description && (
                        <PanelRow>
                            <div className="settings-field-description">{settingPanel?.description}</div>
                        </PanelRow>
                    )}

                    {settingPanel.fields.map((field) => {
                        return (
                            <PanelRow key={settingPanel.label + '-' + node.id + '-' + field.name}>
                                <FieldErrorState
                                    fieldId={`${node.id}-${field.name}`.replace(/[^a-zA-Z0-9_-]/g, '-')}
                                    errorMessages={getFieldErrorMessages(field.name)}
                                >
                                <MappedField
                                    type={field.type}
                                    name={field.name}
                                    description={field?.description}
                                    helpUrl={field?.helpUrl}
                                    label={field.label}
                                    defaultValue={nodeSettings?.[field.name]}
                                    onChange={onChangeSetting}
                                    variables={variableListOptions}
                                    settings={field?.settings}
                                    settingsSchema={settingsSchema}
                                    node={node}
                                    stepScopedVariables={stepScopedVariables}
                                />
                                </FieldErrorState>
                            </PanelRow>
                        );
                    })}
                </PersistentPanelBody>
            );
        });
    }, [settingsSchema, nodeSettings, nodeErrors]);

    return (
        <>
            {settingsPanels}
        </>
    );
};

export default NodeSettingsPanel;
