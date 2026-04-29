import { PanelRow, Animate } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import PersistentPanelBody from "../persistent-panel-body";
import { useSelect } from "@wordpress/data";
import { store as workflowStore } from '../workflow-store';
import NodeIcon from "../node-icon";
import { useState } from "@wordpress/element";

export const NodeDataFlowPanel = ({ inputSchema = [], outputSchema = [], stepScopedVariables = []}) => {
    const {
        isLoadingWorkflow,
        getDataTypeByName,
        globalVariables,
    } = useSelect((select) => {
        return {
            isLoadingWorkflow: select(workflowStore).isLoadingWorkflow(),
            getDataTypeByName: select(workflowStore).getDataTypeByName,
            globalVariables: select(workflowStore).getGlobalVariables(),
        };
    });

    const globalVariableNames = Object.keys(globalVariables);

    if (!inputSchema) {
        inputSchema = [];
    }

    const Variable = ({schemaItem, prefix}) => {
        const [isOpen, setIsOpen] = useState(false);

        const togglePopover = () => {
            setIsOpen(!isOpen);
        }

        const dataType = getDataTypeByName(schemaItem.type);
        let properties = null;

        if (dataType && dataType.propertiesSchema) {
            properties = dataType.propertiesSchema.map((property) => {
                const propertyDataType = getDataTypeByName(property.type);

                if (! propertyDataType || ! propertyDataType.propertiesSchema) {
                    return (
                        <li key={`${schemaItem.name}${property.name}-li`}>
                            <code>{property.name}</code>
                        </li>
                    );
                }

                return (
                    <li key={`${schemaItem.name}${property.name}-li`}>
                        <Variable schemaItem={property} />
                    </li>
                );
            });
        }

        return (
            <>
                <div className="workflow-editor-data-flow-variable">
                    <NodeIcon
                        icon={isOpen ? 'arrow-down' : 'arrow-right'}
                        onClick={togglePopover}
                        style={{cursor: 'pointer'}}
                    />
                    <code>{prefix}{schemaItem.name}</code>

                    {isOpen && properties && (
                        <Animate type="slide-in" options={{origin: 'top'}}>
                            {({ className }) => (
                                <ul>
                                    {properties}
                                </ul>
                            )}
                        </Animate>
                    )}
                </div>
            </>
        );
    };

    return (
        <PersistentPanelBody title={__("Step Data Flow", "post-expirator")} className="workflow-editor-dev-panel">
            <PanelRow className="workflow-editor-inspector-card__handles-schema">
                <h3>{__('Inputs', 'post-expirator')}</h3>
                <div>
                    {inputSchema.length > 0 && (
                        <>
                            <div>{__("This step receives the following input from previous step:", "post-expirator")}</div>
                            <ul>
                                {inputSchema.map((schemaItem, index) => (
                                    <li key={`input-${schemaItem.name}-${index}`}>
                                        <Variable schemaItem={schemaItem} />
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}

                    {inputSchema.length === 0 && __("This step does not receive any input from previous step.", "post-expirator")}
                </div>
            </PanelRow>

            <PanelRow className="workflow-editor-inspector-card__handles-schema">
                <h3>{__('Outputs', 'post-expirator')}</h3>
                <div>
                    {outputSchema.length > 0 && (
                        <>
                            <div>{__("This step outputs the following data:", "post-expirator")}</div>
                            <ul>
                                {outputSchema.map((schemaItem, index) => (
                                    <li key={`output-${schemaItem.name}-${index}`}>
                                        <Variable schemaItem={schemaItem} />
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}

                    {outputSchema.length === 0 && __("This step does not output any data.", "post-expirator")}
                </div>
            </PanelRow>

            <PanelRow className="workflow-editor-inspector-card__handles-schema">
                <h3>{__('Step Scoped Variables', 'post-expirator')}</h3>
                <div>
                    {stepScopedVariables.length > 0 && (
                        <>
                            <div>{__("This step receives the following step scoped variables:", "post-expirator")}</div>
                            <ul>
                                {stepScopedVariables.map((schemaItem, index) => (
                                    <li key={`input-${schemaItem.name}-${index}`}>
                                        <Variable schemaItem={schemaItem} />
                                    </li>
                                ))}
                            </ul>
                        </>
                    )}

                    {stepScopedVariables.length === 0 && __("This step does not have any step scoped variables.", "post-expirator")}
                </div>
            </PanelRow>

            <PanelRow className="workflow-editor-inspector-card__handles-schema">
                <h3>{__('Global Variables', 'post-expirator')}</h3>
                <div>
                    {!isLoadingWorkflow && globalVariableNames.length === 0 &&
                        <div>
                            {__('No global variables are declared', 'post-expirator')}
                        </div>
                    }

                    {!isLoadingWorkflow && globalVariableNames.length > 0 && (
                        <ul>
                            {globalVariableNames.map((variableName) => {
                                return (
                                    <li key={`global-${variableName}`}>
                                        <Variable schemaItem={globalVariables[variableName]} prefix="global." />
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </div>
            </PanelRow>
        </PersistentPanelBody>
    );
};

export default NodeDataFlowPanel;
