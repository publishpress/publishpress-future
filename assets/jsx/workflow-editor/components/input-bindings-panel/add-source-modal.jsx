import { Modal, Button, CheckboxControl } from "@wordpress/components";
import { __ } from "@publishpress/i18n";
import { useState } from "@wordpress/element";
import { useSelect } from "@wordpress/data";
import { store as workflowStore } from "../workflow-store";

export const AddSourceModal = ({ onClose, onAdd, availableSources, bindingType }) => {
    const [selectedSources, setSelectedSources] = useState([]);

    const {
        getDataTypeByName
    } = useSelect((select) => {
        return {
            getDataTypeByName: select(workflowStore).getDataTypeByName,
        };
    });

    const handleToggleSource = (sourceName, isChecked) => {
        if (isChecked) {
            setSelectedSources([...selectedSources, sourceName]);
        } else {
            setSelectedSources(selectedSources.filter(s => s !== sourceName));
        }
    };

    const handleAdd = () => {
        if (selectedSources.length > 0) {
            // Pass all selected sources at once to avoid race conditions
            onAdd(selectedSources);
            onClose();
        }
    };

    const dataType = getDataTypeByName(bindingType);
    const typeLabel = dataType?.label || bindingType;

    return (
        <Modal
            title={__("Add source", "post-expirator")}
            onRequestClose={onClose}
            className="input-binding-add-source-modal"
        >
            <div className="input-binding-modal-content">
                <p>
                    {__("Available (type: ", "post-expirator")}
                    <strong>{typeLabel}</strong>
                    {__(")","post-expirator")}
                </p>

                {availableSources.length > 0 ? (
                    <>
                        <div className="input-binding-sources-checkboxes">
                            {availableSources.map((variable) => (
                                <CheckboxControl
                                    key={variable.name}
                                    label={`${variable.nodeLabel ? variable.nodeLabel + ': ' : ''}${variable.label}`}
                                    checked={selectedSources.includes(variable.name)}
                                    onChange={(isChecked) => handleToggleSource(variable.name, isChecked)}
                                />
                            ))}
                        </div>

                        <div className="input-binding-modal-actions">
                            <Button variant="secondary" onClick={onClose}>
                                {__("Cancel", "post-expirator")}
                            </Button>
                            <Button
                                variant="primary"
                                onClick={handleAdd}
                                disabled={selectedSources.length === 0}
                            >
                                {selectedSources.length > 0
                                    ? __("Add (" + selectedSources.length + ")", "post-expirator")
                                    : __("Add", "post-expirator")
                                }
                            </Button>
                        </div>
                    </>
                ) : (
                    <div>
                        <p>{__("No compatible sources available.", "post-expirator")}</p>
                        <Button variant="secondary" onClick={onClose}>
                            {__("Close", "post-expirator")}
                        </Button>
                    </div>
                )}
            </div>
        </Modal>
    );
};

export default AddSourceModal;

