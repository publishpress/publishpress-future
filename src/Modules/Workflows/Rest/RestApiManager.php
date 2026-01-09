<?php

namespace PublishPress\Future\Modules\Workflows\Rest;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\RestApiManagerInterface;
use PublishPress\Future\Framework\WordPress\Utils\WorkflowSanitizationUtil;

// TODO: This is a temporary class to register the REST API routes. Move this to controllers on each module.
class RestApiManager implements RestApiManagerInterface
{
    public const API_BASE = 'publishpress-future';

    /**
     * @var HookableInterface
     */
    private HookableInterface $hooks;

    private WorkflowSanitizationUtil $workflowSanitization;

    public function __construct(
        HookableInterface $hooks,
        WorkflowSanitizationUtil $workflowSanitization
    ) {
        $this->hooks = $hooks;
        $this->workflowSanitization = $workflowSanitization;
    }

    public function register()
    {
        $apiManagers = [
            new RestApiV1(
                $this->hooks,
                $this->workflowSanitization
            )
        ];

        /**
         * @param RestApiManagerInterface[] $apiManagers
         */
        $apiManagers = $this->hooks->applyFilters(
            HooksAbstract::FILTER_REGISTER_REST_ROUTES,
            $apiManagers
        );

        foreach ($apiManagers as $apiManater) {
            $apiManater->register();
        }
    }
}
