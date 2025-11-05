<?php

namespace PublishPress\Future\Modules\Workflows;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\EmailFacade;
use PublishPress\Future\Framework\WordPress\Facade\OptionsFacade;
use PublishPress\Future\Framework\WordPress\Facade\RequestFacade;
use PublishPress\Future\Framework\WordPress\Facade\SanitizationFacade;
use PublishPress\Future\Modules\Expirator\Interfaces\CronInterface;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\Interfaces\CronSchedulesModelInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepTypesModelInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\RestApiManagerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowEngineInterface;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use PublishPress\Future\Modules\Workflows\Domain\Engine\Cache;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowsModel;

final class Module implements InitializableInterface
{
    public const POST_TYPE_WORKFLOW = "ppfuture_workflow";

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var RestApiManagerInterface
     */
    private $restApiManager;

    private $stepTypesModel;

    /**
     * @var CronSchedulesModelInterface
     */
    private $cronSchedulesModel;

    /**
     * @var WorkflowEngineInterface
     */
    private $workflowEngine;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    /**
     * @var DBTableSchemaInterface
     */
    private $workflowScheduledStepsSchema;

    /**
     * @var \Closure
     */
    private $migrationsFactory;

    /**
     * @var string
     */
    private $pluginVersion;

    /**
     * @var CronInterface
     */
    private $cron;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SanitizationFacade
     */
    private $sanitization;

    /**
     * @var RequestFacade
     */
    private $request;

    /**
     * @var CurrentUserModel
     */
    private $currentUserModel;

    /**
     * @var OptionsFacade
     */
    private $options;

    /**
     * @var EmailFacade
     */
    private $email;

    public function __construct(
        HookableInterface $hooksFacade,
        RestApiManagerInterface $restApiManager,
        StepTypesModelInterface $stepTypesModel,
        CronSchedulesModelInterface $cronSchedulesModel,
        WorkflowEngineInterface $workflowEngine,
        SettingsFacade $settingsFacade,
        DBTableSchemaInterface $workflowScheduledStepsSchema,
        \Closure $migrationsFactory,
        string $pluginVersion,
        CronInterface $cron,
        LoggerInterface $logger,
        SanitizationFacade $sanitization,
        RequestFacade $request,
        \Closure $currentUserModelFactory,
        OptionsFacade $options,
        EmailFacade $email
    ) {
        $this->hooks = $hooksFacade;
        $this->restApiManager = $restApiManager;
        $this->stepTypesModel = $stepTypesModel;
        $this->cronSchedulesModel = $cronSchedulesModel;
        $this->workflowEngine = $workflowEngine;
        $this->settingsFacade = $settingsFacade;
        $this->workflowScheduledStepsSchema = $workflowScheduledStepsSchema;
        $this->migrationsFactory = $migrationsFactory;
        $this->pluginVersion = $pluginVersion;
        $this->cron = $cron;
        $this->logger = $logger;
        $this->sanitization = $sanitization;
        $this->request = $request;
        $this->currentUserModel = $currentUserModelFactory();
        $this->options = $options;
        $this->email = $email;

        $this->registerHooks();

        /*
         * We initialize the engine in the constructor because it requires
         * the init hook has not been fired yet. The initialize method runs in the init hook.
         * We don't initialize the engine if the module is loaded by the Pro version. The Pro version
         * will initialize the engine in its own Module class.
         */
        if (! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO') || ! constant('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')) {
            $this->initializeEngine();
        }
    }

    public function initialize()
    {
        $this->initializeControllers();
    }

    private function initializeControllers()
    {
        $controllers = [
            new Controllers\PostType($this->hooks),
            new Controllers\WorkflowsList(
                $this->hooks,
                $this->stepTypesModel,
                $this->logger,
                $this->settingsFacade
            ),
            new Controllers\WorkflowEditor(
                $this->hooks,
                $this->stepTypesModel,
                $this->cronSchedulesModel,
                $this->settingsFacade
            ),
            new Controllers\RestApi($this->hooks, $this->restApiManager),
            new Controllers\FutureLegacyAction($this->hooks, $this->logger),
            new Controllers\ManualPostTrigger(
                $this->hooks,
                $this->logger,
                $this->sanitization,
                $this->request,
                $this->currentUserModel
            ),
            new Controllers\ScheduledActions(
                $this->hooks,
                $this->stepTypesModel,
                $this->cron,
                $this->settingsFacade,
                $this->logger
            ),
            new Controllers\SampleWorkflows(),
            new Controllers\PostsList($this->hooks),
            new Controllers\Settings(
                $this->hooks,
                $this->workflowScheduledStepsSchema
            ),
            new Controllers\PastDueActions(
                $this->hooks,
                $this->cron,
                $this->options,
                $this->logger,
                $this->email,
                $this->settingsFacade
            ),
        ];

        foreach ($controllers as $controller) {
            $controller->initialize();
        }
    }

    private function initializeEngine()
    {
        if ($this->logger->isDownloadLogRequested()) {
            return;
        }

        // TMP
        if ($_GET['cache'] === 'clear') {
            $cache = new Cache();
            $cache->clearCache();
            delete_transient('publishpress_future_benchmark_raw');
            delete_transient('publishpress_future_benchmark_cached');
            die('Cache cleared');
        }

        if ($_GET['cache'] === 'set') {
            $cache = new Cache();
            $workflowsModel = new WorkflowsModel();
            $workflows = $workflowsModel->getPublishedWorkflowsIds();

            foreach ($workflows as $workflowId) {
                $workflow = new WorkflowModel();
                $workflow->load($workflowId);
                $cache->setCache($workflowId, $workflow->getPostObject());
            }
            die('Cache set');
        }

        if ($_GET['cache'] === 'get') {
            $startTime = microtime(true);

            $this->workflowEngine->start();
            $this->workflowEngine->runWorkflowsFromCache();

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $benchmarks = (array) get_transient('publishpress_future_benchmark_cached');
            $benchmarks[] = $executionTime;

            // Keep only the last 100 benchmarks to prevent unlimited growth
            if (count($benchmarks) > 100) {
                $benchmarks = array_slice($benchmarks, -100);
            }

            set_transient('publishpress_future_benchmark_cached', $benchmarks, HOUR_IN_SECONDS);

            $average = !empty($benchmarks) ? array_sum($benchmarks) / count($benchmarks) : 0;
            ray([
                'execution_time' => $executionTime,
                'benchmarks_count' => count($benchmarks),
                'benchmarks_average' => round($average, 2),
            ])->label('cached benchmark');

            return;
        }

        // Run without cache
        $startTime = microtime(true);

        $this->workflowEngine->start();
        $this->workflowEngine->runWorkflows();

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $benchmarks = (array) get_transient('publishpress_future_benchmark_raw');
        $benchmarks[] = $executionTime;

        // Keep only the last 100 benchmarks to prevent unlimited growth
        if (count($benchmarks) > 100) {
            $benchmarks = array_slice($benchmarks, -100);
        }

        set_transient('publishpress_future_benchmark_raw', $benchmarks, HOUR_IN_SECONDS);

        $average = !empty($benchmarks) ? array_sum($benchmarks) / count($benchmarks) : 0;
        ray([
            'execution_time' => $executionTime,
            'benchmarks_count' => count($benchmarks),
            'benchmarks_average' => round($average, 2),
        ])->label('raw benchmark');
    }

    private function registerHooks()
    {
        $this->hooks->addFilter(
            HooksAbstract::FILTER_WORKFLOW_ROUTE_VARIABLE,
            [$this, 'filterWorkflowRouteVariable'],
            10,
            2
        );
    }

    public function filterWorkflowRouteVariable($variableName, $dataSource)
    {
        // The execution_id variable is deprecated.
        if ($variableName === 'global.execution_id') {
            return 'global.workflow.execution_id';
        }

        // The run_id variable is deprecated.
        if ($variableName === 'global.run_id') {
            return 'global.workflow.execution_id';
        }

        return $variableName;
    }
}
