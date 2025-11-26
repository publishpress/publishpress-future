import { Modal, Button, TextControl, SelectControl } from "@wordpress/components";
import { __ } from "@publishpress/i18n";
import { useState, useMemo } from "@wordpress/element";
import { useSelect } from "@wordpress/data";
import { store as workflowStore } from "../workflow-store";

export const NewBindingModal = ({ onClose, onAdd, availableVariables, existingBindings }) => {
    const [bindingName, setBindingName] = useState("");
    const [bindingLabel, setBindingLabel] = useState("");
    const [bindingType, setBindingType] = useState("");

    const {
        dataTypes
    } = useSelect((select) => {
        return {
            dataTypes: select(workflowStore).getDataTypes(),
        };
    });

    // Get unique types from available variables
    const availableTypes = useMemo(() => {
        const types = new Set();
        availableVariables.forEach(variable => {
            if (variable.type) {
                types.add(variable.type);
            }
        });
        return Array.from(types);
    }, [availableVariables]);

    // Build select options from data types
    const typeOptions = useMemo(() => {
        const options = [
            { label: __("-- Select a type --", "post-expirator"), value: "" }
        ];

        availableTypes.forEach(typeName => {
            const dataType = dataTypes.find(dt => dt.name === typeName);
            if (dataType) {
                options.push({
                    label: dataType.label || typeName,
                    value: typeName,
                });
            }
        });

        return options;
    }, [availableTypes, dataTypes]);

    const handleAdd = () => {
        if (!bindingName || !bindingLabel || !bindingType) {
            alert(__("Please fill in all fields.", "post-expirator"));
            return;
        }

        // Validate binding name (alphanumeric and underscores only)
        if (!/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(bindingName)) {
            alert(__("Binding name must start with a letter or underscore and contain only letters, numbers, and underscores.", "post-expirator"));
            return;
        }

        // Check if binding name already exists
        if (existingBindings[bindingName]) {
            alert(__("A binding with this name already exists.", "post-expirator"));
            return;
        }

        onAdd(bindingName, bindingLabel, bindingType);
    };

    return (
        <Modal
            title={__("New Input Binding", "post-expirator")}
            onRequestClose={onClose}
            className="input-binding-new-modal"
        >
            <div className="input-binding-modal-content">
                <p>
                    {__("Create a new input binding to reference multiple source variables with a single name.", "post-expirator")}
                </p>

                <TextControl
                    label={__("Binding Name", "post-expirator")}
                    help={__("Technical name used in configuration (e.g., 'thePost'). Use only letters, numbers, and underscores.", "post-expirator")}
                    value={bindingName}
                    onChange={setBindingName}
                    placeholder="thePost"
                />

                <TextControl
                    label={__("Binding Label", "post-expirator")}
                    help={__("Human-readable label (e.g., 'The Post').", "post-expirator")}
                    value={bindingLabel}
                    onChange={setBindingLabel}
                    placeholder="The Post"
                />

                <SelectControl
                    label={__("Data Type", "post-expirator")}
                    help={__("Select the data type for this binding. Only variables of this type can be added as sources.", "post-expirator")}
                    value={bindingType}
                    options={typeOptions}
                    onChange={setBindingType}
                />

                {availableTypes.length === 0 && (
                    <div className="input-binding-no-types">
                        {__("No variables are available from previous steps. Connect this step to other steps first.", "post-expirator")}
                    </div>
                )}

                <div className="input-binding-modal-actions">
                    <Button variant="secondary" onClick={onClose}>
                        {__("Cancel", "post-expirator")}
                    </Button>
                    <Button
                        variant="primary"
                        onClick={handleAdd}
                        disabled={!bindingName || !bindingLabel || !bindingType}
                    >
                        {__("Create Binding", "post-expirator")}
                    </Button>
                </div>
            </div>
        </Modal>
    );
};

export default NewBindingModal;

