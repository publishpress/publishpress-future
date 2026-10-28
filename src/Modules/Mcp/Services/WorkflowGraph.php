<?php

/**
 * Discover and validate editor graphs against native step definitions.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp\Services;

use PublishPress\Future\Framework\WordPress\Facade\McpFacade;
use PublishPress\Future\Framework\WordPress\Utils\WorkflowSanitizationUtil;
use PublishPress\Future\Modules\Mcp\Registry;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DoAction;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class WorkflowGraph
{
    private McpFacade $wordpress;
    private StepTypesModel $types;
    private WorkflowSanitizationUtil $sanitization;
    private StepValidation $validation;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress, StepTypesModel $types, WorkflowSanitizationUtil $sanitization, StepValidation $validation)
    {
        $this->wordpress = $wordpress;
        $this->types = $types;
        $this->sanitization = $sanitization;
        $this->validation = $validation;
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function definitions(): array
    {
        $definitions = [];
        foreach ($this->types->getAllStepTypesByType() as $type => $instances) {
            foreach ($this->types->convertInstancesToArray($instances, $type) as $definition) {
                $definition['available'] = !$definition['isProFeature'] || $this->wordpress->pro();
                foreach ($definition['settingsSchema'] as &$panel) {
                    foreach ($panel['fields'] ?? [] as $index => $field) {
                        $panel['fields'][$index]['inputSchema'] = $this->fieldSchema($field);
                    }
                }
                unset($panel);
                $definitions[$definition['name']] = $definition;
            }
        }
        return $definitions;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $flow
     * @return mixed
     */
    public function prepare(array $flow, bool $publishing)
    {
        if (
            !isset($flow['nodes'], $flow['edges']) || !is_array($flow['nodes']) || !is_array($flow['edges'])
            || count($flow['nodes']) > 200 || count($flow['edges']) > 500
        ) {
            return Registry::error(__('Provide a native graph with at most 200 nodes and 500 edges.', 'post-expirator'));
        }
        $flow = $this->sanitization->sanitizeWorkflowData($flow);
        $definitions = $this->definitions();
        $ids = $slugs = [];
        $triggers = 0;
        foreach ($flow['nodes'] as $index => $node) {
            if (!is_array($node) || !is_array($node['data'] ?? null) || !is_string($node['data']['name'] ?? null)) {
                return Registry::error(__('Each graph node needs native data and a step name.', 'post-expirator'));
            }
            $id = $node['id'] ?? '';
            $name = $node['data']['name'];
            $slug = $node['data']['slug'] ?? '';
            if (
                !is_string($id) || !preg_match('/^[a-zA-Z0-9_-]{1,100}$/D', $id) || isset($ids[$id])
                || !is_string($slug) || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,99}$/D', $slug) || isset($slugs[$slug])
                || !isset($definitions[$name]) || !$definitions[$name]['available']
            ) {
                return Registry::error(__('Use unique node IDs and slugs, and an available discovered step type.', 'post-expirator'));
            }
            $definition = $definitions[$name];
            $settings = $node['data']['settings'] ?? [];
            if (!is_array($settings)) {
                return Registry::error(__('Step settings must be an object.', 'post-expirator'));
            }
            $fields = [];
            foreach ($definition['settingsSchema'] as $panel) {
                foreach ($panel['fields'] ?? [] as $field) {
                    $fields[$field['name']] = $field;
                }
            }
            foreach ($settings as $key => $value) {
                $schema = isset($fields[$key]) ? $this->fieldSchema($fields[$key]) : [];
                if (
                    !isset($fields[$key]) || !Registry::strict($value, $schema)
                    || $this->wordpress->validate($value, $schema) instanceof \WP_Error
                ) {
                    return Registry::error(__('Unknown step setting or incorrect native field type. Read the step definition.', 'post-expirator'));
                }
            }
            foreach ($fields as $key => $field) {
                if (($field['type'] ?? '') === 'interactiveCustomOptions' && isset($settings[$key])) {
                    $names = [];
                    foreach ($settings[$key] as $option) {
                        $optionName = $option['name'] ?? '';
                        if (!is_string($optionName) || !preg_match('/^[a-z0-9_-]{1,100}$/D', $optionName) || isset($names[$optionName])) {
                            return Registry::error(__('Interactive options need unique native option names.', 'post-expirator'));
                        }
                        $names[$optionName] = true;
                    }
                }
            }
            if ($publishing) {
                foreach ($fields as $key => $field) {
                    if (($field['type'] ?? '') === 'postStatus' && !isset($settings[$key]['status'])) {
                        return Registry::error(__('Choose the native new-status value before enabling this workflow.', 'post-expirator'));
                    }
                    if (
                        in_array($field['type'] ?? '', ['postInput', 'manualWorkflowInput'], true)
                        && !is_string($settings[$key]['variable'] ?? null)
                    ) {
                        return Registry::error(__('Choose a concrete input variable before enabling this workflow.', 'post-expirator'));
                    }
                }
            }
            if ($name === DoAction::getNodeTypeName() && !in_array($settings['hook'] ?? '', $this->wordpress->allowedActionHooks(), true)) {
                return Registry::error(__('This action hook is not allowed by site configuration.', 'post-expirator'), 403);
            }
            $node['type'] = $definition['type'];
            $node['data']['elementaryType'] = $definition['elementaryType'];
            $node['data']['version'] = $definition['version'];
            $node['data']['settings'] = $this->sanitization->sanitizeWorkflowData($settings);
            $node['position'] = $node['position'] ?? ['x' => 0, 'y' => $index * 150];
            $flow['nodes'][$index] = $node;
            $ids[$id] = $index;
            $slugs[$slug] = true;
            $triggers += $definition['elementaryType'] === 'trigger' ? 1 : 0;
        }
        $edges = [];
        $incoming = $outgoing = [];
        foreach ($flow['edges'] as $edge) {
            if (
                !is_array($edge) || !is_string($edge['id'] ?? null) || isset($edges[$edge['id']])
                || !isset($ids[$edge['source'] ?? ''], $ids[$edge['target'] ?? '']) || $edge['source'] === $edge['target']
            ) {
                return Registry::error(__('Connections need unique IDs and two different existing nodes.', 'post-expirator'));
            }
            foreach (['source', 'target'] as $end) {
                $node = $flow['nodes'][$ids[$edge[$end]]];
                $handles = $definitions[$node['data']['name']]['handleSchema'][$end] ?? [];
                $handleIds = [];
                foreach ($handles as $handle) {
                    if (strpos($handle['type'] ?? '', '__dynamic__:') === 0) {
                        $setting = substr($handle['type'], strlen('__dynamic__:'));
                        $handleIds = array_merge($handleIds, array_column($node['data']['settings'][$setting] ?? [], 'name'));
                    } else {
                        $handleIds[] = $handle['id'];
                    }
                }
                if (!in_array($edge[$end . 'Handle'] ?? '', $handleIds, true)) {
                    return Registry::error(__('Use the source and target handles returned by step discovery.', 'post-expirator'));
                }
            }
            $edges[$edge['id']] = true;
            $incoming[$edge['target']][] = $edge['source'];
            $outgoing[$edge['source']][] = $edge['target'];
        }
        foreach ($flow['nodes'] as $node) {
            $rules = $definitions[$node['data']['name']]['validationSchema'];
            if ($publishing) {
                foreach ($rules['connections']['rules'] ?? [] as $rule) {
                    if (
                        ($rule['rule'] === 'hasIncomingConnection' && empty($incoming[$node['id']]))
                        || ($rule['rule'] === 'hasOutgoingConnection' && empty($outgoing[$node['id']]))
                    ) {
                        return Registry::error(__('Connect every required workflow input and output before enabling it.', 'post-expirator'));
                    }
                }
                $valid = $this->validation->validate($node['data']['settings'], $rules['settings']['rules'] ?? [], $slugs);
                if ($valid instanceof \WP_Error) {
                    return $valid;
                }
            }
        }
        if ($publishing && !$triggers) {
            return Registry::error(__('An enabled workflow needs a trigger.', 'post-expirator'));
        }
        return $flow;
    }

    /**
     * Exact persisted shapes used by the native React field controls. @since 4.12.0
     *
     * @param array<array-key, mixed> $field
     * @return array<array-key, mixed>
     */
    private function fieldSchema(array $field): array
    {
        $type = $field['type'] ?? '';
        if (in_array($type, ['text', 'textarea', 'select', 'debugLevels', 'rayColor'], true)) {
            $schema = ['type' => 'string', 'maxLength' => 20000];
            if ($type === 'select' && !empty($field['settings']['options'])) {
                $schema['enum'] = array_column($field['settings']['options'], 'value');
            }
            if (array_key_exists('default', $field) && $field['default'] === []) {
                return ['anyOf' => [$schema, ['type' => 'array', 'maxItems' => 0, 'items' => ['type' => 'string']]]];
            }
            return $schema;
        }
        if ($type === 'toggle') {
            return ['type' => 'boolean'];
        }
        if ($type === 'askForConfirmation') {
            return Registry::object(['enabled' => ['type' => 'boolean'], 'message' => ['type' => ['string', 'object'], 'maxLength' => 20000, 'properties' => ['expression' => ['type' => 'string', 'maxLength' => 20000]], 'additionalProperties' => false]]);
        }
        if ($type === 'integer') {
            return ['type' => 'integer', 'minimum' => -1000000, 'maximum' => 1000000];
        }
        if ($type === 'postStatus') {
            return Registry::object(['status' => ['type' => 'string', 'enum' => array_keys($this->wordpress->statuses())],
                'newStatus' => ['type' => 'string']]);
        }
        if (in_array($type, ['postInput', 'manualWorkflowInput'], true)) {
            return Registry::object(['variable' => ['type' => ['string', 'object'], 'maxLength' => 10000]]);
        }
        if ($type === 'expression') {
            return Registry::object(['expression' => ['type' => 'string', 'maxLength' => 50000],
                'recipient' => ['type' => 'string', 'maxLength' => 10000]]);
        }
        if (in_array($type, ['actionArgs', 'interactiveCustomOptions'], true)) {
            return ['type' => 'array', 'maxItems' => $type === 'interactiveCustomOptions' ? 5 : 100, 'items' => ['type' => 'object']];
        }
        // Query builders, schedules and post data use nested native JSON structures.
        return ['type' => 'object', 'maxProperties' => 100];
    }
}
