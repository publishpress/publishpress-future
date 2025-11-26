import { Modal, Button, TextControl, SelectControl } from "@wordpress/components";
import { __ } from "@publishpress/i18n";
import { useState, useMemo } from "@wordpress/element";
import { useSelect } from "@wordpress/data";
import { store as workflowStore } from "../workflow-store";

export const EditBindingModal = ({ onClose, onSave, bindingName, binding, availableVariables }) => {
    const [bindingLabel, setBindingLabel] = useState(binding.label || bindingName);

    const {
        dataTypes
    } = useSelect((select) => {
        return {
            dataTypes: select(workflowStore).getDataTypes(),
        };
    });

    const handleSave = () => {
        if (!bindingLabel) {
            alert(__("Please fill in all fields.", "post-expirator"));
            return;
        }

        onSave({
            ...binding,
            label: bindingLabel,
        });
    };

    // Get the data type label for display
    const dataType = dataTypes.find(dt => dt.name === binding.type);
    const typeLabel = dataType?.label || binding.type;

    return (
        <Modal
            title={__("Edit Input Binding", "post-expirator")}
            onRequestClose={onClose}
            className="input-binding-edit-modal"
        >
            <div className="input-binding-modal-content">
                <p>
                    {__("Edit the binding configuration.", "post-expirator")}
                </p>

                <TextControl
                    label={__("Binding Name", "post-expirator")}
                    help={__("Technical name cannot be changed to avoid breaking references.", "post-expirator")}
                    value={bindingName}
                    disabled={true}
                />

                <TextControl
                    label={__("Binding Label", "post-expirator")}
                    help={__("Human-readable label.", "post-expirator")}
                    value={bindingLabel}
                    onChange={setBindingLabel}
                    placeholder="The Post"
                />

                <TextControl
                    label={__("Data Type", "post-expirator")}
                    help={__("Type cannot be changed to maintain compatibility with sources.", "post-expirator")}
                    value={typeLabel}
                    disabled={true}
                />

                <div className="input-binding-modal-actions">
                    <Button variant="secondary" onClick={onClose}>
                        {__("Cancel", "post-expirator")}
                    </Button>
                    <Button
                        variant="primary"
                        onClick={handleSave}
                        disabled={!bindingLabel}
                    >
                        {__("Save Changes", "post-expirator")}
                    </Button>
                </div>
            </div>
        </Modal>
    );
};

export default EditBindingModal;

