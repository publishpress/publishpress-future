<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface StepTypesModelInterface
{
    /** @since 4.3.1 */
    public function getTriggerSteps(): array;

    /** @since 4.3.1 */
    public function getActionSteps(): array;

    /** @since 4.3.1 */
    public function getAdvancedSteps(): array;

    public function convertInstancesToArray($instances, $type): array;

    public function getCategories(): array;

    /** @since 4.3.1 */
    public function getAllStepTypesIndexedByName(): array;

    /** @since 4.3.1 */
    public function getStepType(string $name): ?StepTypeInterface;

    public function getStrings(): array;

    /** @since 4.3.1 */
    public function getAllStepTypes(): array;

    /** @since 4.3.1 */
    public function getAllStepTypesByType(): array;
}
