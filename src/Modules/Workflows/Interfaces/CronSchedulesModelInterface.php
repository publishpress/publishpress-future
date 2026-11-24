<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface CronSchedulesModelInterface
{
    public function getCronSchedules(): array;

    public function getCronSchedulesAsOptions(): array;

    public function getCronScheduleValueByName(string $name): string;
}
