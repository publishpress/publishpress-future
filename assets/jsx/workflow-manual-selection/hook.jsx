import { createRoot } from '@wordpress/element';

const { inlineEditPost } = window;

const delayToUnmountAfterSaving = 1000;

// We create a copy of the WP inline set bulk function
const wpInlineSetBulk = inlineEditPost.setBulk;
const wpInlineEditRevert = inlineEditPost.revert;

export const useInlineEditBulk = () => {
    const setupInlineEditBulk = (containerId, component) => {
        /**
         * We override the function with our own code so we can detect when
         * the inline edit row is displayed to recreate the React component.
         */
        inlineEditPost.setBulk = function (id) {
            // Call the original WP edit function.
            wpInlineSetBulk.apply(this, arguments);

            const container = document.getElementById(containerId);

            if (!container) {
                return;
            }

            const root = createRoot(container);

            const saveButton = document.querySelector('#bulk_edit');
            if (saveButton) {
                saveButton.onclick = function() {
                    setTimeout(() => {
                        root.unmount();
                    }, delayToUnmountAfterSaving);
                };
            }

            root.render(component);

            inlineEditPost.revert = function () {
                root.unmount();

                // Call the original WP revert function.
                wpInlineEditRevert.apply(this, arguments);
            };
        };
    };

    return {
        setupInlineEditBulk
    };
};

export default useInlineEditBulk;
