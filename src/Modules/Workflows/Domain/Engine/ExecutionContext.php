<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\ArrayResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\BooleanResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\DatetimeResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\EmailResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\FutureActionResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\NodeResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostTermsResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\SiteResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\UserResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\WorkflowResolver;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextProcessorRegistryInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;

use function wp_json_encode;

class ExecutionContext implements ExecutionContextInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var ExecutionContextProcessorRegistryInterface
     */
    private $processorRegistry;

    /**
     * @var array
     */
    private $runtimeVariables = [];

    /**
     * @var array
     */
    private $executionTrace = [];

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    public function __construct(
        HookableInterface $hooks,
        ExecutionContextProcessorRegistryInterface $processorRegistry,
        \Closure $expirablePostModelFactory
    ) {
        $this->hooks = $hooks;
        $this->processorRegistry = $processorRegistry;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
    }

    public function setAllVariables(array $runtimeVariables)
    {
        $this->runtimeVariables = $runtimeVariables;
    }

    public function getAllVariables(): array
    {
        return $this->runtimeVariables;
    }

    public function getVariable(string $variableName)
    {
        return $this->getVariableValueFromNestedVariable($variableName, $this->runtimeVariables);
    }

    public function setVariable(string $variableName, $variableValue)
    {
        if (strpos($variableName, '.') !== false) {
            $this->setVariableInNestedArray($variableName, $variableValue, $this->runtimeVariables);
        } else {
            $this->runtimeVariables[$variableName] = $variableValue;
        }
    }

    /**
     * @since 4.3.4
     */
    public function extractExpressionsFromText(string $text): array
    {
        $expressions = [];
        preg_match_all('/{{(.*?)}}/', $text, $expressions);

        return $expressions[1];
    }

    /**
     * @since 4.3.4
     */
    public function resolveExpressionsInText(string $text): string
    {
        $expressions = $this->extractExpressionsFromText($text);

        foreach ($expressions as $expression) {
            if ($expressionElements = $this->variableHasHelper($expression)) {
                $value = $this->getVariable($expressionElements['variable']);
                $value = $this->processorRegistry->process($expressionElements['helper'], $value, $expressionElements['args']);
            } else {
                $value = $this->getVariable($expression);
            }

            if (is_array($value) || is_object($value)) {
                $value = wp_json_encode($value);
            }

            $text = str_replace('{{' . $expression . '}}', $value, $text);
        }

        return $text;
    }

    /**
     * @since 4.3.4
     */
    public function resolveExpressionsInArray(array $array): array
    {
        if (empty($array)) {
            return $array;
        }

        return array_map(fn ($item) => $this->resolveExpressionsInText($item), $array);
    }

    /**
     * @deprecated 4.3.4 Use extractExpressionsFromText instead.
     */
    public function extractPlaceholdersFromText($text)
    {
        return $this->extractExpressionsFromText($text);
    }

    /**
     * @deprecated 4.3.4 Use extractExpressionsFromText instead.
     */
    public function replacePlaceholdersInText($text)
    {
        return $this->resolveExpressionsInText($text);
    }

    /**
     * Recursively replaces variables in JSON Logic expressions with their values.
     *
     * @since 4.3.4
     */
    public function resolveExpressionsInJsonLogic(array $jsonLogicExpression): array
    {
        $newExpression = [];

        foreach ($jsonLogicExpression as $key => $value) {
            // If it's a {"var": "..."} node, try to resolve it to a real value
            if (is_array($value) && array_key_exists('var', $value)) {
                $originalVar = $value['var'];

                $resolved = $this->getVariableValueFromNestedVariable($originalVar, $this->runtimeVariables);

                if ($resolved === $originalVar || (is_string($resolved) && strpos($resolved, '{{') !== false)) {
                    try {
                        $fallback = $this->getVariable($originalVar);
                        if ($fallback !== null) {
                            $resolved = $fallback;
                        }
                    } catch (\Throwable $e) {
                        // couldn't resolve
                    }
                }

                if ($resolved instanceof VariableResolverInterface) {
                    $resolved = $resolved->getValue();
                }

                $newExpression[$key] = $resolved;
                continue;
            }

            if (is_array($value)) {
                $newExpression[$key] = $this->resolveExpressionsInJsonLogic($value);
                continue;
            }

            if (is_string($value) && strpos($value, '{{') !== false) {
                $newExpression[$key] = $this->resolveExpressionsInText($value);
                continue;
            }

            $newExpression[$key] = $value;
        }

        return $newExpression;
    }

    private function variableHasHelper(string $variableName)
    {
        $helperRegex = '/^([a-zA-Z0-9_]+)\s+([a-zA-Z0-9_\.]+)\s*(.*)$/';

        $matches = [];
        preg_match($helperRegex, $variableName, $matches);

        if (empty($matches)) {
            return false;
        }

        $arguments = [];

        // Extract arguments if they exist in the third capture group
        if (!empty($matches[3])) {
            $argsString = trim($matches[3]);

            // Regex to match key-value pairs like key="value" or key='value' or key=value
            $argsRegex = '/([a-zA-Z0-9_\-]+)\s*=\s*(?:(?:"([^"]*)")|(?:\'([^\']*)\')|([^\s]+))/';

            $argMatches = [];
            preg_match_all($argsRegex, $argsString, $argMatches, PREG_SET_ORDER);

            foreach ($argMatches as $argMatch) {
                $key = $argMatch[1];
                // Get the value from whichever capturing group matched (double quotes, single quotes, or no quotes)
                $value = $argMatch[2] ?? $argMatch[3] ?? $argMatch[4] ?? '';
                $arguments[$key] = $value;
            }
        }

        return [
            'helper' => $matches[1],
            'variable' => $matches[2],
            'args' => $arguments,
        ];
    }

    private function getVariableValue(string $variableName, $dataSource)
    {
        if (is_array($dataSource) && isset($dataSource[$variableName])) {
            if (
                is_object($dataSource[$variableName]) &&
                $dataSource[$variableName] instanceof VariableResolverInterface
            ) {
                return $dataSource[$variableName]->getValue();
            }

            return $dataSource[$variableName];
        } elseif (is_object($dataSource) && $dataSource instanceof VariableResolverInterface) {
            return $dataSource->getValue($variableName);
        } elseif (is_object($dataSource) && isset($dataSource->{$variableName})) {
            return $dataSource->{$variableName};
        }

        return (string) $variableName;
    }

    private function getVariableValueFromNestedVariable(string $variableName, $dataSource)
    {
        $variableName = trim($variableName, '{}');

        /**
         * @param string $variableName
         * @param mixed $dataSource
         *
         * @since 4.3.4
         *
         * @return string
         */
        $variableName = $this->hooks->applyFilters(
            HooksAbstract::FILTER_WORKFLOW_ROUTE_VARIABLE,
            $variableName,
            $dataSource
        );

        $originalVariableName = $variableName;
        $variableName = explode('.', $variableName);

        if (count($variableName) === 1) {
            return $this->getVariableValue($variableName[0], $dataSource);
        } else {
            if (is_array($dataSource) && !isset($dataSource[$variableName[0]])) {
                return $originalVariableName;
            }

            if (is_object($dataSource)) {
                if (!isset($dataSource->{$variableName[0]})) {
                    return $originalVariableName;
                }
            }

            if (is_array($dataSource)) {
                $currentVariableSource = $dataSource[$variableName[0]];
            } else {
                $currentVariableSource = $dataSource->{$variableName[0]};
            }

            $variableName = array_slice($variableName, 1);

            return $this->getVariableValueFromNestedVariable(
                implode('.', $variableName),
                $currentVariableSource
            );
        }

        return $originalVariableName;
    }

    private function setVariableInNestedArray(string $variableName, $variableValue, &$dataSource)
    {
        $variableName = explode('.', $variableName);

        if (count($variableName) === 1) {
            if (is_array($dataSource)) {
                $dataSource[$variableName[0]] = $variableValue;
            } elseif (is_object($dataSource) && $dataSource instanceof VariableResolverInterface) {
                $dataSource->setValue($variableName[0], $variableValue);
            } else {
                $dataSource->{$variableName[0]} = $variableValue;
            }
        } else {
            if (!isset($dataSource[$variableName[0]])) {
                $dataSource[$variableName[0]] = [];
            }

            $this->setVariableInNestedArray(
                implode('.', array_slice($variableName, 1)),
                $variableValue,
                $dataSource[$variableName[0]]
            );
        }
    }

    public function getCompactedRuntimeVariables(): array
    {
        $runtimeVariables = $this->getAllVariables();

        foreach ($runtimeVariables as $context => &$variables) {
            if (is_array($variables)) {
                foreach ($variables as &$variableResolver) {
                    if (is_object($variableResolver)) {
                        $diff = null;
                        // TODO: Each variable resolver should have a method to compact itself and check diff, etc.
                        if ($variableResolver->getType() === 'post') {
                            $diff = $this->getPostDifferences(
                                $variableResolver->getVariable(),
                                get_post($variableResolver->getValue('ID'))
                            );
                        }

                        $variableResolver = $variableResolver->compact();

                        if ($diff) {
                            $variableResolver['diff'] = $diff;
                        }
                    }
                }
            }
        }

        return $runtimeVariables;
    }

    private function getPostDifferences($post1, $post2)
    {
        $differences = [];

        foreach ($post1 as $key => $value) {
            if (! isset($post2->$key)) {
                $differences[$key] = $value;
            } elseif ($post2->$key !== $value) {
                $differences[$key] = $value;
            }
        }

        return $differences;
    }

    /**
     * Example of the different types of compacted arguments, LEGACY is the format
     * before v3.4.1.
     *
     * ----------------------------------------
     * LEGACY compacted arguments:
     *
     *   ...
     *   "contextVariables": {
     *      "global": {
     *          "workflow": 1417,
     *          "user": 1,
     *          "site": "Future Pro Workflow Dev",
     *          "trigger": "onPostUpdated_ebtrjpm"
     *      },
     *      "onPostUpdated1": {
     *          "postId": 1402,
     *          "postBefore": {
     *              "class": "WP_Post",
     *              "id": 1402,
     *              "diff": {
     *                  "post_title": "Custom Development??",
     *                  "post_status": "draft",
     *                  "post_modified": "2024-06-27 15:10:06",
     *                  "post_modified_gmt": "2024-06-27 18:10:06"
     *              }
     *          },
     *          "postAfter": {
     *              "class": "WP_Post",
     *              "id": 1402,
     *              "diff": []
     *          }
     *      }
     *  }
     *  ...
     * ----------------------------------------
     * NEW compacted arguments:
     *
     *  "runtimeVariables": {
     *    "global": {
     *        "workflow": {
     *            "type": "workflow",
     *            "value": 1417,
     *            "execution_id": "0000-0129-af10-a001",
     *            "execution_trace": "onPostUpdated_ebtrjpm, savePost_1402"
     *        },
     *        "user": {
     *            "type": "user",
     *            "value": 1
     *        },
     *        "site": {
     *            "type": "site",
     *            "value": "Future Pro Workflow Dev"
     *        },
     *        "trigger": {
     *            "type": "node",
     *            "value": {
     *                "id": "onPostUpdated_ebtrjpm",
     *                "name": "trigger\/core.post-updated",
     *                "label": "Post is updated",
     *                "activation_timestamp": "2024-06-27 18:19:24"
     *            }
     *        }
     *    },
     *    "onPostUpdated1": {
     *        "postId": {
     *            "type": "integer",
     *            "value": 1402
     *        },
     *        "postBefore": {
     *            "type": "post",
     *            "value": 1402,
     *            "diff": {
     *                "post_title": "Custom Development??",
     *                "post_status": "draft",
     *                "post_modified": "2024-06-27 15:16:56",
     *                "post_modified_gmt": "2024-06-27 18:16:56"
     *            }
     *        },
     *        "postAfter": {
     *            "type": "post",
     *            "value": 1402
     *        }
     *    }
     *}
     */
    public function expandRuntimeVariables(array $compactedVariables, bool $isLegacyCompact = false): array
    {
        $runtimeVariables = [];

        foreach ($compactedVariables as $context => $variables) {
            foreach ($variables as $variableName => $value) {
                $type = 'unknown';

                if ($isLegacyCompact) {
                    if ($variableName === 'workflow') {
                        $type = 'workflow';
                    } elseif ($variableName === 'user') {
                        $type = 'user';
                    } elseif ($variableName === 'site') {
                        $type = 'site';
                    } elseif ($variableName === 'trigger') {
                        $type = 'node';
                    } elseif (is_array($value)) {
                        $type = 'array';

                        if (isset($value['class'])) {
                            if ($value['class'] === 'WP_Post') {
                                $type = 'post';
                            } elseif ($value['class'] === 'WP_User') {
                                $type = 'user';
                            }
                        }
                    } elseif (is_numeric($value)) {
                        $type = 'integer';
                    } elseif (is_string($value)) {
                        $type = 'string';
                    }
                } else {
                    $type = $value['type'] ?? 'unknown';
                }

                // FIXME: This should be moved to a variable resolver factory
                $resolversMap = [
                    'array' => ArrayResolver::class,
                    'boolean' => BooleanResolver::class,
                    'datetime' => DatetimeResolver::class,
                    'email' => EmailResolver::class,
                    'integer' => IntegerResolver::class,
                    'node' => NodeResolver::class,
                    'post' => PostResolver::class,
                    'terms' => TermsResolver::class,
                    'site' => SiteResolver::class,
                    'user' => UserResolver::class,
                    'workflow' => WorkflowResolver::class,
                    'future_action' => FutureActionResolver::class,
                ];

                if (! $isLegacyCompact) {
                    $resolverArgument = $value['value'] ?? null;
                } else {
                    $resolverArgument = $value;
                }

                switch ($type) {
                    case 'post':
                        if ($isLegacyCompact) {
                            $postId = (int)$value['id'];
                        } else {
                            $postId = (int)$value['value'];
                        }

                        $resolverArgument = get_post($postId);

                        break;
                    case 'user':
                        if ($isLegacyCompact) {
                            $userId = (int)$value;
                        } else {
                            $userId = (int)$value['value'];
                        }

                        $resolverArgument = get_user_by('id', $userId);
                        break;
                    case 'workflow':
                        if ($isLegacyCompact) {
                            $workflowId = (int)$value;
                        } else {
                            $workflowId = (int)$value['value'];
                        }

                        $workflowModel = new WorkflowModel();
                        $workflowModel->load($workflowId);
                        $resolverArgument = [
                            'id' => $workflowModel->getId(),
                            'title' => $workflowModel->getTitle(),
                            'description' => $workflowModel->getDescription(),
                            'modified_at' => $workflowModel->getModifiedAt(),
                            'execution_id' => $value['execution_id'] ?? '',
                            'execution_trace' => $value['execution_trace'] ?? '',
                        ];
                        break;
                }

                if (isset($resolversMap[$type])) {
                    $resolverClass = $resolversMap[$type];

                    // TODO: Replace this with a factory
                    if ($type === 'site') {
                        $runtimeVariables[$context][$variableName] = new $resolverClass();
                    } elseif ($type === 'post') {
                        $runtimeVariables[$context][$variableName] = new $resolverClass(
                            $resolverArgument,
                            $this->hooks,
                            '',
                            $this->expirablePostModelFactory
                        );
                    } else {
                        $runtimeVariables[$context][$variableName] = new $resolverClass(
                            $resolverArgument
                        );
                    }
                } else {
                    $runtimeVariables[$context][$variableName] = $value;
                }
            }
        }

        return $runtimeVariables;
    }
}
