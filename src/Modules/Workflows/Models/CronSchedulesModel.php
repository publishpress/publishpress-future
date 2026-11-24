<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Interfaces\CronSchedulesModelInterface;

\defined('ABSPATH') || exit;

class CronSchedulesModel implements CronSchedulesModelInterface
{
    public function getCronSchedules(): array
    {
        return wp_get_schedules();
    }

    public function getCronSchedulesAsOptions(): array
    {
        $schedules = $this->getCronSchedules();

        $options = [];

        foreach ($schedules as $key => $schedule) {
            $options[] = [
                'value' => $key,
                'label' => $schedule['display'],
            ];
        }

        return $options;
    }

    public function getCronScheduleValueByName(string $name): string
    {
        $schedules = $this->getCronSchedules();

        foreach ($schedules as $key => $schedule) {
            if ($key === $name) {
                return (int) $schedule['interval'];
            }
        }

        return 0;
    }
}
