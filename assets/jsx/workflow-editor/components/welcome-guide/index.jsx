import { Guide } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { store as editorStore } from "../editor-store";
import { useDispatch } from "@wordpress/data";
import { FEATURE_WELCOME_GUIDE } from "../../constants";

const getWelcomeGuideImageUrl = (imageName) => {
    return futureWorkflowEditor.assetsUrl + '/images/workflow-welcome-guide/' + imageName + '?v=' + futureWorkflowEditor.pluginVersion;
};

export function WelcomeGuide() {
    const {
        disableFeature,
    } = useDispatch(editorStore);

    const pagesContent = futureWorkflowEditor.welcomeGuidePages;

    return (
        <Guide
            className="workflow-editor-welcome-guide"
            contentLabel={__("Welcome to the workflow editor", 'post-expirator')}
            finishButtonText={__("Get started", 'post-expirator')}
            onFinish={() => {
                disableFeature(FEATURE_WELCOME_GUIDE);
            }}
            pages={
                pagesContent.map(({ title, content, image }) => ({
                    content: (
                        <>
                            <h1 className="edit-post-welcome-guide__heading">{ title }</h1>
                            <p className="edit-post-welcome-guide__text">{ content }</p>
                        </>
                    ),
                    image: (
                        <picture className="edit-post-welcome-guide__image">
                            <source
                                srcSet={ getWelcomeGuideImageUrl(image + '.png') }
                                media="(prefers-reduced-motion: reduce)"
                            />
                            <img src={ getWelcomeGuideImageUrl(image + '.gif') } width="312" height="240" alt="" />
                        </picture>
                    ),
                }))
            }
        />
    )
}

export default WelcomeGuide;
