<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

/**
 * @deprecated 4.3.1 Use StepTypesModelInterface instead.
 */
interface NodeTypesModelInterface
{
    public function convertInstancesToArray($instances, $type): array;

    public function getCategories(): array;

    public function getStrings(): array;

    public function getAllNodeTypesIndexedByName(): array;

    public function getNodeType(string $name);

    public function getAllNodeTypes(): array;

    public function getAllNodeTypesByType(): array;

    public function getTriggerNodes(): array;

    public function getActionNodes(): array;

    public function getAdvancedNodes(): array;
}
