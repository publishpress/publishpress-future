import { select } from "@wordpress/data";
import { store as workflowStore } from "../../workflow-store";
import { CustomOptions } from "../custom-options";
import { __ } from "@wordpress/i18n";

export function InteractiveCustomOptions(props) {
    /**
     * Check if the option name can be changed.
     *
     * If the option is connected to a source handle, it cannot be changed because
     * it would break the connections in the workflow.
     *
     * @param {Object} option
     * @returns {boolean}
     */
    const canChangeNameCallback = (option) => {
        const connectedSourceHandlesOfSelectedNode = select(workflowStore).getConnectedSourceHandlesOfSelectedNode();

        if (connectedSourceHandlesOfSelectedNode.length === 0) {
            return true;
        }

        const isOptionConnected = connectedSourceHandlesOfSelectedNode.includes(option.name);

        return !isOptionConnected;
    }

    return <CustomOptions {...props}
        canChangeNameCallback={canChangeNameCallback}
        cantChangeNameDescription={__("This option is used to connect to another step and cannot have its name changed until the connection is removed.", "post-expirator")}
        maxOptions={5}
    />;
}
