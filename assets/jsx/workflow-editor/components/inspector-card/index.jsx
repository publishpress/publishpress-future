import { useDispatch } from "@wordpress/data";
import { store as workflowStore } from "../workflow-store";
import { __ } from "@wordpress/i18n";
import {
    Button,
    TextareaControl,
    __experimentalVStack as VStack,
    __experimentalHStack as HStack,
    ExternalLink
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import SettingPopover from "../setting-popover";
import { useIsPro } from "../../contexts/pro-context";

const StepDescription = ({ node }) => {
    const [isPopoverOpen, setIsPopoverOpen] = useState(false);
    const [description, setDescription] = useState(node.data?.label);

    const {
        updateNode
    } = useDispatch(workflowStore);

    useEffect(() => {
        setIsPopoverOpen(false);
        setDescription(node.data?.label);
    }, [node]);

    const closePopover = () => {
        setIsPopoverOpen(false);

        const newNode = {
            id: node.id,
            data: {
                label: description,
            },
        };

        updateNode(newNode);
    };


    return (
        <>
            {! node.data?.label && (
                <Button
                    variant="link"
                    onClick={() => {
                        setIsPopoverOpen(true);
                    }}
                >
                    {__("Add a description to this step...", "post-expirator")}
                </Button>
            )}

            {node.data?.label && (
                <>
                    <VStack>
                        <div className="workflow-editor-inspector-card__description">
                            {node.data.label}
                        </div>

                        <Button
                            variant="link"
                            onClick={() => {
                                setIsPopoverOpen(true);
                            }}
                        >
                            {__("Edit description", "post-expirator")}
                        </Button>
                    </VStack>
                </>
            )}

            {isPopoverOpen && (
                <SettingPopover
                    onClose={closePopover}
                    className="workflow-editor-inspector-card__description-popover"
                    title={__("Edit description", "post-expirator")}
                >
                    <VStack>
                        <TextareaControl
                            value={description}
                            onChange={(value) => {
                                setDescription(value);
                            }}
                            onKeyDown={(event) => {
                                if (event.key === 'Escape') {
                                    closePopover();
                                }
                            }}
                        />
                    </VStack>
                </SettingPopover>
            )}
        </>
    );
}

export const InspectorCard = ({ title, description, icon, id, slug, isProFeature, node }) => {
    const isPro = useIsPro();

    const nodeAttributes = [];

    return (
        <div className="workflow-editor-inspector-card">
            <div className="workflow-editor-inspector-card__content">
                <VStack>
                    <HStack className="workflow-editor-inspector-card__header">
                        <span className="workflow-editor-inspector-icon has-colors">
                            {icon}
                        </span>
                        <h2 className="workflow-editor-inspector-card__title">
                            {title}
                            {isProFeature && !isPro && (
                                <span className="workflow-editor-inspector-card__pro-badge">
                                    {__("Pro", "post-expirator")}
                                </span>
                            )}
                        </h2>
                    </HStack>
                </VStack>
                <VStack>
                    <div className="workflow-editor-inspector-card__description">
                        {description}
                    </div>
                </VStack>

                {isProFeature && !isPro && (
                    <VStack>
                        <div className="workflow-editor-inspector-card__pro-instructions">
                            <ExternalLink href="https://publishpress.com/links/future-workflow-inspector" target="_blank">
                                {__("Currently this step is being skipped. Upgrade to Pro to unlock this feature.", "post-expirator")}
                            </ExternalLink>
                        </div>
                    </VStack>
                )}

                {node && node?.data && (
                    <VStack>
                        <StepDescription node={node} />
                    </VStack>
                )}

                {nodeAttributes.length > 0 && (
                    <VStack>
                        <table>
                            <tbody>
                                {nodeAttributes.map((attribute) => {
                                    return (
                                        <tr key={"attribute_" + attribute.id}>
                                            <th>{attribute.label}</th>
                                            <td>{attribute.value}</td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </VStack>
                )}
            </div>
        </div>
    );
};

export default InspectorCard;
