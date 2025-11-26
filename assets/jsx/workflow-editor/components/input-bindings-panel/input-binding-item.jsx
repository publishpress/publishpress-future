import { Button, CheckboxControl } from "@wordpress/components";
import { __ } from "@publishpress/i18n";
import { useState } from "@wordpress/element";
import { AddSourceModal } from "./add-source-modal";
import { EditBindingModal } from "./edit-binding-modal";
import { useSelect } from "@wordpress/data";
import { store as workflowStore } from "../workflow-store";

export const InputBindingItem = ({ bindingName, binding, availableVariables, onUpdate, onDelete, node }) => {
    const [isAddSourceModalOpen, setIsAddSourceModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);

    const {
        getDataTypeByName
    } = useSelect((select) => {
        return {
            getDataTypeByName: select(workflowStore).getDataTypeByName,
        };
    });

    const onToggleSource = (sourceName, isChecked) => {
        const newSources = isChecked
            ? [...binding.sources, sourceName]
            : binding.sources.filter(s => s !== sourceName);

        onUpdate({
            ...binding,
            sources: newSources,
        });
    };

    const onAddSource = (sourceNames) => {
        // Accept either a single source name or an array of source names
        const namesToAdd = Array.isArray(sourceNames) ? sourceNames : [sourceNames];

        // Filter out sources that are already in the binding
        const newSourcesToAdd = namesToAdd.filter(name => !binding.sources.includes(name));

        if (newSourcesToAdd.length > 0) {
            onUpdate({
                ...binding,
                sources: [...binding.sources, ...newSourcesToAdd],
            });
        }
    };

    // Filter available variables by type compatibility
    const compatibleVariables = availableVariables.filter(variable => {
        return variable.type === binding.type;
    });

    // Get variables that are already added
    const addedSources = compatibleVariables.filter(variable =>
        binding.sources.includes(variable.name)
    );

    // Get variables that can still be added
    const availableSources = compatibleVariables.filter(variable =>
        !binding.sources.includes(variable.name)
    );

    const dataType = getDataTypeByName(binding.type);
    const typeLabel = dataType?.label || binding.type;

    return (
        <div className="input-binding-item">
            <div className="input-binding-header">
                <div className="input-binding-title">
                    <strong>{binding.label || bindingName}</strong>
                    <span className="input-binding-type">({typeLabel})</span>
                </div>
                <div className="input-binding-actions">
                    <Button
                        icon="edit"
                        label={__("Edit binding", "post-expirator")}
                        onClick={() => setIsEditModalOpen(true)}
                        isSmall
                    />
                    <Button
                        icon="trash"
                        label={__("Delete binding", "post-expirator")}
                        onClick={onDelete}
                        isSmall
                        isDestructive
                    />
                </div>
            </div>

            <div className="input-binding-sources">
                {addedSources.length === 0 && (
                    <div className="input-binding-no-sources">
                        {__("No sources selected. Click 'Add source' to select compatible variables.", "post-expirator")}
                    </div>
                )}

                {addedSources.map((variable) => (
                    <CheckboxControl
                        key={variable.name}
                        label={`${variable.nodeLabel ? variable.nodeLabel + ': ' : ''}${variable.label}`}
                        checked={true}
                        onChange={(isChecked) => onToggleSource(variable.name, isChecked)}
                    />
                ))}

                {availableSources.length > 0 && (
                    <Button
                        variant="link"
                        onClick={() => setIsAddSourceModalOpen(true)}
                        className="input-binding-add-source"
                    >
                        {__("+ Add source", "post-expirator")}
                    </Button>
                )}

                {availableSources.length === 0 && addedSources.length > 0 && (
                    <div className="input-binding-all-sources-added">
                        {__("All compatible sources have been added.", "post-expirator")}
                    </div>
                )}

                {compatibleVariables.length === 0 && (
                    <div className="input-binding-no-compatible">
                        {__("No compatible variables available from previous steps.", "post-expirator")}
                    </div>
                )}
            </div>

            {isAddSourceModalOpen && (
                <AddSourceModal
                    onClose={() => {
                        setIsAddSourceModalOpen(false);
                    }}
                    onAdd={onAddSource}
                    availableSources={availableSources}
                    bindingType={binding.type}
                />
            )}

            {isEditModalOpen && (
                <EditBindingModal
                    onClose={() => setIsEditModalOpen(false)}
                    onSave={(updatedBinding) => {
                        onUpdate(updatedBinding);
                        setIsEditModalOpen(false);
                    }}
                    bindingName={bindingName}
                    binding={binding}
                    availableVariables={availableVariables}
                />
            )}
        </div>
    );
};

export default InputBindingItem;

