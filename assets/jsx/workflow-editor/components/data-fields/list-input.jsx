import { __ } from "@wordpress/i18n";
import { VariablesTreeSelect } from "../variables-tree-select";
import { __experimentalVStack as VStack } from "@wordpress/components";


export function ListInput({ name, label, defaultValue, onChange, tree }) {
    const onChangeSetting = ({ settingName, value }) => {
        const newValue = { ...defaultValue };
        newValue[settingName] = value;

        if (onChange) {
            onChange(name, newValue);
        }
    }

    return (
        <>
            <VStack>
                <VariablesTreeSelect
                    tree={tree}
                    label={label}
                    selectedId={defaultValue?.variable}
                    onChange={(value) => onChangeSetting({ settingName: "variable", value })}
                />
            </VStack>
        </>
    );
}

export default ListInput;
