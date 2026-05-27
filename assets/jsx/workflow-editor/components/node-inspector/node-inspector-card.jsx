import { __ } from "@wordpress/i18n";
import { useSelect } from "@wordpress/data";
import { store as editorStore } from "../editor-store";

import InspectorCard from "../inspector-card";
import NodeIcon from "../node-icon";

export const NodeInspectorCard = ({ node }) => {
    const {
        getNodeTypeByName,
    } = useSelect((select) => {
        return {
            getNodeTypeByName: select(editorStore).getNodeTypeByName,
        };
    });

    const nodeType = getNodeTypeByName(node?.data?.name);
    const nodeLabel = nodeType.label || node?.data.label || __("Node", "post-expirator");
    const nodeDescription = nodeType.description || node?.data.description || __("No description", "post-expirator")

    const nodeIcon = nodeType.icon?.src || "media-document";
    const nodeId = node?.id;
    const nodeSlug = node?.data?.slug;

    const Icon = <NodeIcon icon={nodeIcon} />;

    return (
        <InspectorCard
            title={nodeLabel}
            description={nodeDescription}
            id={nodeId}
            icon={Icon}
            slug={nodeSlug}
            isProFeature={nodeType.isProFeature}
            node={node}
        />
    );
};

export default NodeInspectorCard;
