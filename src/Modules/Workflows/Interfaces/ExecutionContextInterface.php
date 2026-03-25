<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

interface ExecutionContextInterface
{
    public function setAllVariables(array $runtimeVariables);

    public function getAllVariables(): array;

    public function getVariable(string $variableName);

    public function setVariable(string $variableName, $variableValue);

    /**
     * @deprecated 4.3.4 Use extractExpressionsFromText instead.
     */
    public function extractPlaceholdersFromText($text);

    /**
     * @deprecated 4.3.4 Use resolveExpressionsInText instead.
     */
    public function replacePlaceholdersInText($text);

    /**
     * @since 4.3.4
     */
    public function extractExpressionsFromText(string $text): array;

    /**
     * @since 4.3.4
     */
    public function resolveExpressionsInText(string $text): string;

    /**
     * @since 4.3.4
     */
    public function resolveExpressionsInArray(array $array): array;

    /**
     * @since 4.3.4
     */
    public function resolveExpressionsInJsonLogic(array $jsonLogicExpression): array;

    /**
     * @since 4.6.0
     */
    public function getCompactedRuntimeVariables(): array;

    /**
     * @since 4.6.0
     */
    public function expandRuntimeVariables(array $compactedVariables, bool $isLegacyCompact = false): array;

    /**
     * @since 4.10.0
     */
    public function getWorkflowId(): int;

    /**
     * @since 4.10.0
     */
    public function setWorkflowId(int $workflowId): void;
}
