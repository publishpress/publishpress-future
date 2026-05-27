import { SelectControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useEffect } from "@wordpress/element";
import { __experimentalVStack as VStack } from "@wordpress/components";
import InlineMultiSelect from "../inline-multi-select";
import { store as workflowStore } from "../workflow-store";
import { useSelect, useDispatch } from "@wordpress/data";
import { RadioControl } from "@wordpress/components";


export function TaxonomyTerms({ name, label, defaultValue, onChange, settings }) {
    const taxonomies = futureWorkflowEditor.taxonomies;

    const defaultTaxonomy = defaultValue?.taxonomy || taxonomies[0]?.value;

    const {
        taxonmoyTerms,
    } = useSelect((select) => {
        return {
            taxonmoyTerms: select(workflowStore).getTaxonomyTerms(defaultTaxonomy),
        };
    });

    const {
        fetchTaxonomyTerms,
    } = useDispatch(workflowStore)

    const optionToSelectAll = settings && settings?.optionToSelectAll === true;
    const labelOptionToSelectAll = settings && settings?.labelOptionToSelectAll;

    const onChangeSetting = ({ settingName, value }) => {
        const newValue = { ...defaultValue };
        newValue[settingName] = value;

        if (onChange) {
            onChange(name, newValue);
        }
    }

    // Set default setting
    useEffect(() => {
        if (!defaultValue) {
            defaultValue = {
                taxonomy: defaultTaxonomy,
                terms: [],
            };

            onChangeSetting({ settingName: "taxonomy", value: defaultTaxonomy });
        }
    }, []);

    useEffect(() => {
        fetchTaxonomyTerms(defaultTaxonomy);
    }, [defaultTaxonomy]);

    return (
        <>
            <VStack>
                <SelectControl
                    label={__("Taxonomy", "post-expirator")}
                    value={defaultValue?.taxonomy}
                    options={taxonomies}
                    onChange={(value) => onChangeSetting({ settingName: "taxonomy", value })}
                />

                {optionToSelectAll && (
                    <>
                        <RadioControl
                            label={__("Select the terms", "post-expirator")}
                            selected={defaultValue?.selectAll || '0'}
                            options={[
                                { label: labelOptionToSelectAll, value: '1' },
                                { label: __("Specific terms", "post-expirator"), value: '0' }
                            ]}
                            onChange={(value) => onChangeSetting({ settingName: "selectAll", value })}
                        />

                        {defaultValue?.selectAll !== '1' && taxonmoyTerms.length && (
                            <InlineMultiSelect
                                label={__('Terms', 'post-expirator')}
                                value={defaultValue?.terms || []}
                                suggestions={taxonmoyTerms}
                                expandOnFocus={true}
                                autoSelectFirstMatch={true}
                                onChange={(value) => onChangeSetting({ settingName: "terms", value })}
                            />
                        )}
                    </>
                )}

                {/* We are checking the taxonomyTerms.length to make sure the field renders only after labels are available */}
                {!optionToSelectAll && taxonmoyTerms.length && (
                    <InlineMultiSelect
                        label={__('Terms', 'post-expirator')}
                        value={defaultValue?.terms || []}
                        suggestions={taxonmoyTerms}
                        expandOnFocus={true}
                        autoSelectFirstMatch={true}
                        onChange={(value) => onChangeSetting({ settingName: "terms", value })}
                        className="future-taxonomy-terms"
                    />
                )}
            </VStack>
        </>
    );
}

export default TaxonomyTerms;
