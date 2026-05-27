import { __ } from "@wordpress/i18n";
import { TreeSelect } from "@wordpress/components";
import { __experimentalVStack as VStack } from "@wordpress/components";

export function DebugLevels({ name, label, defaultValue, onChange, variables = [] }) {
    let levelsOptions = [
        { name: __("Debug", "post-expirator"), id: "debug" },
        { name: __("Info", "post-expirator"), id: "info" },
        { name: __("Notice", "post-expirator"), id: "notice" },
        { name: __("Warning", "post-expirator"), id: "warning" },
        { name: __("Error", "post-expirator"), id: "error" },
        { name: __("Critical", "post-expirator"), id: "critical" },
        { name: __("Alert", "post-expirator"), id: "alert" },
    ];

    const defaultOption = 'debug';

    const onChangeSetting = ({ value }) => {
        if (onChange) {
            onChange(name, value);
        }
    }

    return (
        <>
            <VStack>
                <TreeSelect
                    label={__("Level", "post-expirator")}
                    tree={levelsOptions}
                    selectedId={defaultValue || defaultOption}
                    onChange={(value) => onChangeSetting({ settingName: "level", value })}
                />
            </VStack>
        </>
    );
}

export default DebugLevels;
