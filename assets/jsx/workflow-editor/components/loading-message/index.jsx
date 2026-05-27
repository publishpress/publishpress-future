import { Modal } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export const LoadingMessage = () => {
    return (
        <Modal
            title={__('Loading...', 'post-expirator')}
            isDismissible={false}
        >
            <div>
                <p>
                    {__('Wait, we are loading the workflow...', 'post-expirator')}
                </p>
            </div>
        </Modal>
    );
};

export default LoadingMessage;
