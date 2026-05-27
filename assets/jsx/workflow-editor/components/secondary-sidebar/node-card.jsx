import NodeIcon from "../node-icon";
import { __ } from '@wordpress/i18n';
import { useIsPro } from '../../contexts/pro-context';

export function NodeCard({node}) {
    const isPro = useIsPro();

    return (
        <div className="block-editor-block-card">
            <NodeIcon icon={node.icon} showColors className={'block-editor-block-icon'} />
            <div className="block-editor-block-card__content">
                <h2 className="block-editor-block-card__title">
                    { node.label }
                    { node.isProFeature && !isPro && (
                        <span className="block-editor-block-card__pro-badge">
                            { __('Pro', 'post-expirator') }
                        </span>
                    ) }
                </h2>

                { node.description && (
                    <span className="block-editor-block-card__description">
                        { node.description }
                    </span>
                ) }

                { node.isProFeature && !isPro && (
                    <div className="block-editor-block-card__pro-instructions">
                        { __('Currently this step is being skipped. Upgrade to Pro to unlock this feature.', 'post-expirator') }
                    </div>
                ) }
            </div>
        </div>
    )
}
