import { SelectControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useEffect } from "@wordpress/element";
import { __experimentalVStack as VStack } from "@wordpress/components";
import InlineMultiSelect from "../inline-multi-select";
import { store as workflowStore } from "../workflow-store";
import { useSelect, useDispatch } from "@wordpress/data";
import { RadioControl } from "@wordpress/components";


export function PostStatus({ name, label, defaultValue, onChange, settings }) {
    const postStatuses = futureWorkflowEditor.postStatuses;

    const onChangeSetting = ({ settingName, value }) => {
        const newValue = { ...defaultValue };
        newValue[settingName] = value;

        if (onChange) {
            onChange(name, newValue);
        }
    }

    const defaultStatus = postStatuses[0]?.value;

    // Set default setting
    useEffect(() => {
        if (!defaultValue) {
            defaultValue = {
                newStatus: defaultStatus,
            };

            onChangeSetting({ settingName: "status", value: defaultStatus });
        }
    }, []);

    return (
        <>
            <VStack>
                <SelectControl
                    label={__("New Status", "post-expirator")}
                    value={defaultValue?.status}
                    options={postStatuses}
                    onChange={(value) => onChangeSetting({ settingName: "status", value })}
                />
            </VStack>
        </>
    );
}

export default PostStatus;
