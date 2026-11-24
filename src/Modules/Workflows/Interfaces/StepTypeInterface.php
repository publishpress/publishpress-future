<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

/**
 * @since 4.3.1
 */
interface StepTypeInterface
{
    /**
     * The name of the node type. This must be unique and must be
     * lower case with no spaces. It must be a compound name
     * initiating with the node elementary type, separated by a slash "/".
     */
    public static function getNodeTypeName(): string;

    /**
     * There are three elementary types: trigger, action, and advanced.
     */
    public function getElementaryType(): string;

    /**
     * There are two types: generic and trigger. This is used by
     * the workflow editor to differentiate between the two for
     * rendering purposes.
     */
    public function getReactFlowNodeType(): string;

    /**
     * The label displayed in the workflow editor.
     */
    public function getLabel(): string;

    /**
     * A brief description of the node type.
     */
    public function getDescription(): string;

    /**
     * The icon displayed in the workflow editor. It accepts dashicons or
     * custom icons. Custom icons must be registered in the plugin.
     */
    public function getIcon(): string;

    /**
     * The frecency is a number that determines the order in which the
     * node type is displayed in the workflow editor in the recent nodes
     * panel. The lower the number, the higher the node type will be displayed.
     */
    public function getFrecency(): int;

    /**
     * The version of the node type. This is used to determine if the
     * node type has been updated and the flow needs to be updated
     * accordingly.
     */
    public function getVersion(): int;

    /**
     * The category of the node type. This is used to group the node types
     * in the workflow editor.
     *
     * The available categories are:
     *  - post
     *  - future
     *  - site
     *  - conditional
     *  - async
     *  - debug
     *  - db-query
     *  - messages
     */
    public function getCategory(): string;

    /**
     * The schema of the settings of the node type. This is used to
     * render the settings panel when the node is selected in the
     * workflow editor.
     */
    public function getSettingsSchema(): array;

    /**
     * The schema of the validation of the node type. This is used to
     * validate the settings and connections of the node in the
     * workflow editor.
     */
    public function getValidationSchema(): array;

    /**
     * The schema of the step scoped variables of the node type. This is used to
     * specify the step scoped variables of the node in the workflow editor. Those
     * variables are available to the node and can be used in the settings. Output
     * variables are not necessarily available in the step scoped variables, and it
     * doesn't have to be on both schemas.
     */
    public function getStepScopedVariablesSchema(): array;

    /**
     * The schema of the output of the node type. This is used to
     * specify the output of the node in the workflow editor.
     */
    public function getOutputSchema(): array;

    /**
     * The CSS class of the node type. This is used to apply custom
     * styles to the node in the workflow editor.
     */
    public function getCSSClass(): string;

    /**
     * The base slug of the node type, the prefix. This is used to generate the
     * unique slug of the node type.
     */
    public function getBaseSlug(): string;

    /**
     * The schema of the handles of the node type. This is used to
     * specify the handles of the node in the workflow editor, for
     * source or target handles. The handles are used to connect
     * the nodes in the workflow editor.
     */
    public function getHandleSchema(): array;

    /**
     * Whether the node type is a pro feature.
     *
     * @return boolean
     */
    public function isProFeature(): bool;
}
