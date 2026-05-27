import { useSelect } from "@wordpress/data";
import {
    Button,
    Icon,
    __unstableMotion as motion
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { addQueryArgs } from '@wordpress/url';
import { wordpress } from '@wordpress/icons';
import { store as workflowStore } from './workflow-store';
import { store as editorStore } from './editor-store';
import { POST_TYPE } from '../constants';

export function FullscreenModeClose({ showTooltip }) {
    const { isActive, postType } = useSelect(
        (select) => {
            const { getPostType } = select(workflowStore);
            const { isFeatureActive } = select(editorStore);

            return {
                isActive: isFeatureActive('fullscreenMode'),
                postType: getPostType(),
            };
        },
        []
    );

    if (!isActive || !postType) {
        return null;
    }

    let buttonIcon = <Icon size="36px" icon={wordpress} />;

    return (
        <motion.div whileHover="expand" className="editor-header__back-button">
            <Button
                className="edit-post-fullscreen-mode-close"
                href={
                    addQueryArgs('edit.php', {
                        post_type: POST_TYPE,
                    })
                }
                label={__('Back', 'post-expirator')}
                showTooltip={showTooltip}
            >
                {buttonIcon}
            </Button>
        </motion.div>
    );
}
