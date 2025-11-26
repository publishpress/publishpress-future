import { PanelRow, Button } from "@wordpress/components";
import { __ } from "@publishpress/i18n";
import { useState } from "@wordpress/element";
import { useSelect, useDispatch } from "@wordpress/data";
import { store as workflowStore } from "../workflow-store";
import PersistentPanelBody from "../persistent-panel-body";
import { mapNodeInputs } from "../../utils";
import { InputBindingItem } from "./input-binding-item";
import { NewBindingModal } from "./new-binding-modal";

export const InputBindingsPanel = ({ node }) => {
    const [isNewBindingModalOpen, setIsNewBindingModalOpen] = useState(false);

    const {
        inputBindings,
        availableVariables,
    } = useSelect((select) => {
        const globalVariables = select(workflowStore).getGlobalVariables();
        const mappedInputs = mapNodeInputs(node);

        return {
            inputBindings: node?.data?.inputBindings || {},
            availableVariables: mappedInputs,
        };
    });

    const {
        updateNode
    } = useDispatch(workflowStore);

    const onAddBinding = (bindingName, bindingLabel, bindingType) => {
        const newInputBindings = {
            ...inputBindings,
            [bindingName]: {
                label: bindingLabel,
                type: bindingType,
                sources: [],
            }
        };

        updateNode({
            id: node.id,
            data: {
                inputBindings: newInputBindings,
            }
        });

        setIsNewBindingModalOpen(false);
    };

    const onUpdateBinding = (bindingName, updatedBinding) => {
        const newInputBindings = {
            ...inputBindings,
            [bindingName]: updatedBinding,
        };

        updateNode({
            id: node.id,
            data: {
                inputBindings: newInputBindings,
            }
        });
    };

    const onDeleteBinding = (bindingName) => {
        const newInputBindings = { ...inputBindings };
        delete newInputBindings[bindingName];

        updateNode({
            id: node.id,
            data: {
                inputBindings: newInputBindings,
            }
        });
    };

    const bindingKeys = Object.keys(inputBindings);
    const hasBindings = bindingKeys.length > 0;

    return (
        <>
            <PersistentPanelBody 
                title={__("Input Bindings", "post-expirator")}
                className="input-bindings-panel"
                initialOpen={false}
            >
                <PanelRow>
                    <div className="input-bindings-description">
                        {__("Input bindings allow you to map multiple source variables from different triggers to a single local variable name that can be used in this step.", "post-expirator")}
                    </div>
                </PanelRow>

                {hasBindings && (
                    <div className="input-bindings-list">
                        {bindingKeys.map((bindingName) => (
                            <InputBindingItem
                                key={bindingName}
                                bindingName={bindingName}
                                binding={inputBindings[bindingName]}
                                availableVariables={availableVariables}
                                onUpdate={(updatedBinding) => onUpdateBinding(bindingName, updatedBinding)}
                                onDelete={() => onDeleteBinding(bindingName)}
                                node={node}
                            />
                        ))}
                    </div>
                )}

                {!hasBindings && (
                    <PanelRow>
                        <div className="input-bindings-empty">
                            {__("No input bindings defined yet. Click 'New binding' to create one.", "post-expirator")}
                        </div>
                    </PanelRow>
                )}

                <PanelRow>
                    <Button
                        variant="secondary"
                        onClick={() => setIsNewBindingModalOpen(true)}
                    >
                        {__("+ New binding", "post-expirator")}
                    </Button>
                </PanelRow>
            </PersistentPanelBody>

            {isNewBindingModalOpen && (
                <NewBindingModal
                    onClose={() => setIsNewBindingModalOpen(false)}
                    onAdd={onAddBinding}
                    availableVariables={availableVariables}
                    existingBindings={inputBindings}
                />
            )}
        </>
    );
};

export default InputBindingsPanel;

