import { useSelect } from "@wordpress/data";
import {
    Button,
    Icon,
    __unstableMotion as motion
} from "@wordpress/components";
import { isRTL } from "@wordpress/i18n";
import { addQueryArgs } from '@wordpress/url';
import { wordpress, chevronLeft, chevronRight } from '@wordpress/icons';
import { store as workflowStore } from './workflow-store';
import { store as editorStore } from './editor-store';
import { POST_TYPE } from '../constants';

const { backButtonLabel } = window.futureWorkflowEditor;

function isWP71BackButton() {
    if (window.futureWorkflowEditor?.isWP71OrLater) {
        return true;
    }

    const bodyClass = document.body.className;

    // branch-7-1, branch-7-2, version-7-1, etc. — NOT branch-7 alone (that's 7.0.x)
    return /\bbranch-7-([1-9]|\d{2,})\b/.test(bodyClass)
        || /\bversion-7-([1-9]|\d{2,})\b/.test(bodyClass);
}

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

    const backHref = addQueryArgs('edit.php', {
        post_type: POST_TYPE,
    });

    if (isWP71BackButton()) {
        return (
            <div className="editor-header__back-button">
                <Button
                    href={backHref}
                    label={backButtonLabel}
                    showTooltip={showTooltip ?? true}
                    tooltipPosition="bottom"
                    icon={isRTL() ? chevronRight : chevronLeft}
                    size="compact"
                />
            </div>
        );
    }

    const buttonIcon = <Icon size="36px" icon={wordpress} />;

    return (
        <motion.div whileHover="expand" className="editor-header__back-button">
            <Button
                className="edit-post-fullscreen-mode-close"
                href={backHref}
                label={backButtonLabel}
                showTooltip={showTooltip}
            >
                {buttonIcon}
            </Button>
        </motion.div>
    );
}
