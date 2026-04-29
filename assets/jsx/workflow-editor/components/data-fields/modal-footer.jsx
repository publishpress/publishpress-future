import { __ } from "@wordpress/i18n";
import { Button } from "@wordpress/components";

export const ModalFooter = ({ onClose, children }) => {
    return (
        <div className="workflow-editor-modal-footer">
            {children ? (
                children
            ) : (
                <Button variant="primary" onClick={onClose}>
                    {__("OK", "post-expirator")}
                </Button>
            )}
        </div>
    );
};
