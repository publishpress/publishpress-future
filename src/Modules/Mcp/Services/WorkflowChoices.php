<?php

/**
 * Current native editor choices for workflow field discovery.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp\Services;

use PublishPress\Future\Modules\Workflows\Models\CronSchedulesModel;
use PublishPress\Future\Modules\Workflows\Models\PostStatusesModel;
use PublishPress\Future\Modules\Workflows\Models\PostTypesModel;
use PublishPress\Future\Modules\Workflows\Models\TaxonomiesModel;
use PublishPress\Future\Modules\Workflows\Models\UserRolesModel;

\defined('ABSPATH') || exit;

class WorkflowChoices
{
    private PostTypesModel $types;
    private PostStatusesModel $statuses;
    private TaxonomiesModel $taxonomies;
    private UserRolesModel $roles;
    private CronSchedulesModel $schedules;

    /** @since 4.12.0 */
    public function __construct(PostTypesModel $types, PostStatusesModel $statuses, TaxonomiesModel $taxonomies, UserRolesModel $roles, CronSchedulesModel $schedules)
    {
        $this->types = $types;
        $this->statuses = $statuses;
        $this->taxonomies = $taxonomies;
        $this->roles = $roles;
        $this->schedules = $schedules;
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function get(): array
    {
        return ['post_types' => $this->types->getPostTypesAsOptions(), 'post_statuses' => $this->statuses->getPostStatusesAsOptions(),
            'taxonomies' => $this->taxonomies->getTaxonomiesAsOptions(), 'user_roles' => $this->roles->getUserRolesAsOptions(),
            'cron_schedules' => $this->schedules->getCronSchedulesAsOptions()];
    }
}
