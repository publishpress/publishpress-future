<?php

/**
 * PublishPress Future: Schedule Post Changes
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2025, PublishPress
 * @license     GPLv2 or later
 */

defined("ABSPATH") or die("Direct access not allowed.");

use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HooksAbstract;
use PublishPress\Future\Core\Paths;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\Cache\GenericCacheHandler;
use PublishPress\Future\Framework\Database\DBTableSchemaHandler;
use PublishPress\Future\Framework\Logger\DBTableSchemas\DebugLogSchema;
use PublishPress\Future\Framework\Logger\Logger;
use PublishPress\Future\Framework\System\DateTimeHandler;
use PublishPress\Future\Framework\WordPress\Facade\DatabaseFacade;
use PublishPress\Future\Framework\WordPress\Facade\DateTimeFacade;
use PublishPress\Future\Framework\WordPress\Facade\EmailFacade;
use PublishPress\Future\Framework\WordPress\Facade\ErrorFacade;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Framework\WordPress\Facade\NoticeFacade;
use PublishPress\Future\Framework\WordPress\Facade\OptionsFacade;
use PublishPress\Future\Framework\WordPress\Facade\RequestFacade;
use PublishPress\Future\Framework\WordPress\Facade\SanitizationFacade;
use PublishPress\Future\Framework\WordPress\Facade\SiteFacade;
use PublishPress\Future\Framework\WordPress\Facade\UsersFacade;
use PublishPress\Future\Framework\WordPress\Models\PostModel;
use PublishPress\Future\Framework\WordPress\Models\TermModel;
use PublishPress\Future\Framework\WordPress\Models\UserModel;
use PublishPress\Future\Modules\Debug\Module as ModuleDebug;
use PublishPress\Future\Modules\Expirator\Adapters\CronToWooActionSchedulerAdapter;
use PublishPress\Future\Modules\Expirator\DBTableSchemas\ActionArgsSchema;
use PublishPress\Future\Modules\Expirator\ExpirationActions\ChangePostStatus;
use PublishPress\Future\Modules\Expirator\ExpirationActions\DeletePost;
use PublishPress\Future\Modules\Expirator\ExpirationActions\PostCategoryAdd;
use PublishPress\Future\Modules\Expirator\ExpirationActions\PostCategoryRemove;
use PublishPress\Future\Modules\Expirator\ExpirationActions\PostCategoryRemoveAll;
use PublishPress\Future\Modules\Expirator\ExpirationActions\PostCategorySet;
use PublishPress\Future\Modules\Expirator\ExpirationActions\PostStatusToDraft;
use PublishPress\Future\Modules\Expirator\ExpirationActions\PostStatusToPrivate;
use PublishPress\Future\Modules\Expirator\ExpirationActions\PostStatusToTrash;
use PublishPress\Future\Modules\Expirator\ExpirationActions\StickPost;
use PublishPress\Future\Modules\Expirator\ExpirationActions\UnstickPost;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\ExpirationScheduler;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpirationHooksAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\ActionArgsModelInterface;
use PublishPress\Future\Modules\Expirator\Migrations\V30000ActionArgsSchema;
use PublishPress\Future\Modules\Expirator\Migrations\V30000ReplaceFooterPlaceholders;
use PublishPress\Future\Modules\Expirator\Migrations\V30000WPCronToActionsScheduler;
use PublishPress\Future\Modules\Expirator\Migrations\V30001RestorePostMeta;
use PublishPress\Future\Modules\Expirator\Migrations\V30104ArgsColumnLength;
use PublishPress\Future\Modules\Expirator\Models\ActionArgsModel;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;
use PublishPress\Future\Modules\Expirator\Models\ExpirationActionsModel;
use PublishPress\Future\Modules\Expirator\Models\PostTypeDefaultDataModelFactory;
use PublishPress\Future\Modules\Expirator\Module as ModuleExpirator;
use PublishPress\Future\Modules\Backup\Module as ModuleBackup;
use PublishPress\Future\Modules\Expirator\Tables\ScheduledActionsTable;
use PublishPress\Future\Modules\InstanceProtection\Module as ModuleInstanceProtection;
use PublishPress\Future\Modules\Settings\Models\SettingsPostTypesModel;
use PublishPress\Future\Modules\Settings\Models\TaxonomiesModel;
use PublishPress\Future\Modules\Settings\Module as ModuleSettings;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\VersionNotices\Module as ModuleVersionNotices;
use PublishPress\Future\Modules\WooCommerce\Module as ModuleWooCommerce;
use PublishPress\Future\Modules\Workflows\DBTableSchemas\WorkflowScheduledStepsSchema;
use PublishPress\Future\Modules\Workflows\Domain\Caches\PostCache;
use PublishPress\Future\Modules\Workflows\Domain\Engine\WorkflowEngine;
use PublishPress\Future\Modules\Workflows\Domain\Engine\InputValidators\PostQuery as PostQueryValidator;
use PublishPress\Future\Modules\Workflows\Domain\Engine\JsonLogicEngine;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Processors\Cron as CronStep;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Processors\General as GeneralStep;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Processors\Post as PostStep;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\ChangePostStatusRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\DeactivatePostWorkflowRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\DeletePostRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\StickPostRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\UnstickPostRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\AddPostMetaRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\AddPostTermRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\AppendDebugLogRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\ConditionalRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\DeletePostMetaRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\QueryPostsRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\RemovePostTermRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\ScheduleDelayRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\SendEmailRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\SendRayRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\SetPostTermRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\UpdatePostMetaRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\UpdatePostRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnAdminInitRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnInitRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnLegacyActionTriggerRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostAuthorChangeRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostMetaChangeRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostPublishRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostRowActionRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostSaveRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostScheduleRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostStatusChangeRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostUpdateRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostWorkflowEnableRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnScheduleRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnUserRoleChangeRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnTermsAddedRunner;
use PublishPress\Future\Modules\Workflows\HooksAbstract as WorkflowsHooksAbstract;
use PublishPress\Future\Modules\Workflows\Infrastructure\Safety\WorkflowExecutionSafeguard;
use PublishPress\Future\Modules\Workflows\Interfaces\AsyncStepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Migrations\V40000WorkflowScheduledStepsSchema;
use PublishPress\Future\Modules\Workflows\Models\CronSchedulesModel;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;
use PublishPress\Future\Modules\Workflows\Module as ModuleWorkflows;
use PublishPress\Future\Modules\Workflows\Rest\RestApiManager;
use PublishPress\Future\Core\DI\ContainerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\ContextProcessors\DateProcessor;
use PublishPress\Future\Modules\Workflows\Domain\Engine\ExecutionContextProcessorInitializer;
use PublishPress\Future\Modules\Workflows\Domain\Engine\ExecutionContextProcessorRegistry;
use PublishPress\Future\Modules\Workflows\Domain\Engine\ExecutionContextRegistry;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\DoActionRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\UserInteractionRunner;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnCustomActionRunner;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowEngineInterface;
use PublishPress\Future\Modules\Workflows\Migrations\V040500OnScheduledStepsSchema;

return [
    ServicesAbstract::PLUGIN_VERSION => PUBLISHPRESS_FUTURE_VERSION,

    ServicesAbstract::PLUGIN_SLUG => 'post-expirator',

    ServicesAbstract::PLUGIN_NAME => "PublishPress Future",

    ServicesAbstract::DEFAULT_DATA => [
        ServicesAbstract::DEFAULT_DATE_FORMAT => __(
            "l F jS, Y",
            "post-expirator"
        ),
        ServicesAbstract::DEFAULT_TIME_FORMAT => __("g:ia", "post-expirator"),
        ServicesAbstract::DEFAULT_FOOTER_CONTENT => __(
            "Post expires at EXPIRATIONTIME on ACTIONDATE",
            "post-expirator"
        ),
        ServicesAbstract::DEFAULT_FOOTER_STYLE => "font-style: italic;",
        ServicesAbstract::DEFAULT_FOOTER_DISPLAY => "0",
        ServicesAbstract::DEFAULT_EMAIL_NOTIFICATION => "0",
        ServicesAbstract::DEFAULT_EMAIL_NOTIFICATION_ADMINS => "0",
        ServicesAbstract::DEFAULT_DEBUG => "0",
        ServicesAbstract::DEFAULT_EXPIRATION_DATE => "null",
    ],

    ServicesAbstract::BASE_PATH => __DIR__,

    /**
     * @return string
     */
    ServicesAbstract::BASE_URL => static function (ContainerInterface $container) {
        return plugins_url('/', __FILE__);
    },

    /**
     * @return ModuleInterface[]
     */
    ServicesAbstract::MODULES => static function (ContainerInterface $container) {
        $modulesServiceList = [
            ServicesAbstract::MODULE_DEBUG,
            ServicesAbstract::MODULE_INSTANCE_PROTECTION,
            ServicesAbstract::MODULE_VERSION_NOTICES,
            ServicesAbstract::MODULE_EXPIRATOR,
            ServicesAbstract::MODULE_SETTINGS,
            ServicesAbstract::MODULE_WOOCOMMERCE,
            ServicesAbstract::MODULE_WORKFLOWS,
            ServicesAbstract::MODULE_BACKUP,
        ];

        $modules = [];
        foreach ($modulesServiceList as $service) {
            $modules[] = $container->get($service);
        }

        return $container->get(ServicesAbstract::HOOKS)->applyFilters(
            HooksAbstract::FILTER_MODULES_LIST,
            $modules
        );
    },

    /**
     * @return Plugin
     */
    ServicesAbstract::PLUGIN => static function (ContainerInterface $container) {
        return new Plugin(
            $container->get(ServicesAbstract::MODULES),
            $container->get(ServicesAbstract::LEGACY_PLUGIN),
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::PLUGIN_SLUG),
            $container->get(ServicesAbstract::BASE_PATH),
            $container->get(ServicesAbstract::NOTICES),
            $container->get(ServicesAbstract::LOGGER)
        );
    },

    /**
     * @return PostExpirator_Facade
     */
    ServicesAbstract::LEGACY_PLUGIN => static function (ContainerInterface $container) {
        return PostExpirator_Facade::getInstance();
    },

    /**
     * @return Paths
     */
    ServicesAbstract::PATHS => static function (ContainerInterface $container) {
        return new Paths(__DIR__);
    },

    /**
     * @return LoggerInterface
     */
    ServicesAbstract::LOGGER => static function (ContainerInterface $container) {
        return new Logger(
            $container->get(ServicesAbstract::DB),
            $container->get(ServicesAbstract::SITE),
            $container->get(ServicesAbstract::SETTINGS)
        );
    },

    /**
     * @return \PublishPress\Future\Modules\Expirator\Adapters\CronToWooActionSchedulerAdapter
     */
    ServicesAbstract::WOO_CRON_ADAPTER => static function (ContainerInterface $container) {
        return new CronToWooActionSchedulerAdapter();
    },

    /**
     * @return \PublishPress\Future\Modules\Expirator\Interfaces\CronInterface
     */
    ServicesAbstract::CRON => static function (ContainerInterface $container) {
        return $container->get(ServicesAbstract::WOO_CRON_ADAPTER);
    },

    /**
     * @return HooksFacade
     */
    ServicesAbstract::HOOKS => static function (ContainerInterface $container) {
        return new HooksFacade();
    },

    /**
     * @return DatabaseFacade
     */
    ServicesAbstract::DB => static function (ContainerInterface $container) {
        return new DatabaseFacade(
            $container->get(ServicesAbstract::WPDB)
        );
    },

    /**
     * @return DateTimeFacade
     */
    ServicesAbstract::DATETIME => static function (ContainerInterface $container) {
        return new DateTimeFacade(
            $container->get(ServicesAbstract::OPTIONS)
        );
    },

    /**
     * @return ErrorFacade
     */
    ServicesAbstract::ERROR => static function (ContainerInterface $container) {
        return new ErrorFacade();
    },

    /**
     * @return OptionsFacade
     */
    ServicesAbstract::OPTIONS => static function (ContainerInterface $container) {
        return new OptionsFacade();
    },

    /**
     * @return SiteFacade
     */
    ServicesAbstract::SITE => static function (ContainerInterface $container) {
        return new SiteFacade();
    },

    /**
     * @return UsersFacade
     */
    ServicesAbstract::USERS => static function (ContainerInterface $container) {
        return new UsersFacade();
    },

    /**
     * @return \PublishPress\Future\Framework\WordPress\Facade\NoticeFacade
     */
    ServicesAbstract::NOTICES => static function (ContainerInterface $container) {
        return new NoticeFacade(
            $container->get(ServicesAbstract::HOOKS)
        );
    },

    /**
     * @return EmailFacade
     */
    ServicesAbstract::EMAIL => static function (ContainerInterface $container) {
        return new EmailFacade();
    },

    /**
     * @return \PublishPress\Future\Framework\WordPress\Facade\RequestFacade
     */
    ServicesAbstract::REQUEST => static function (ContainerInterface $container) {
        return new RequestFacade();
    },

    /**
     * @return \PublishPress\Future\Framework\WordPress\Facade\SanitizationFacade
     */
    ServicesAbstract::SANITIZATION => static function (ContainerInterface $container) {
        return new SanitizationFacade();
    },

    /**
     * @return PublishPress\Future\Modules\Debug\Debug|null
     */
    ServicesAbstract::DEBUG => static function (ContainerInterface $container) {
        return new PublishPress\Future\Modules\Debug\Debug(
            $container->get(ServicesAbstract::LOGGER),
            $container->get(ServicesAbstract::SETTINGS)
        );
    },

    /**
     * @return SettingsFacade
     */
    ServicesAbstract::SETTINGS => static function (ContainerInterface $container) {
        return new SettingsFacade(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::OPTIONS),
            $container->get(ServicesAbstract::DEFAULT_DATA)
        );
    },

    /**
     * @return SchedulerInterface
     */
    ServicesAbstract::EXPIRATION_SCHEDULER => static function (ContainerInterface $container) {
        return new ExpirationScheduler(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::CRON),
            $container->get(ServicesAbstract::ERROR),
            $container->get(ServicesAbstract::LOGGER),
            $container->get(ServicesAbstract::DATETIME),
            $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
            $container->get(ServicesAbstract::ACTION_ARGS_MODEL_FACTORY),
            $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL)
        );
    },

    /**
     * @return ModuleInterface
     */
    ServicesAbstract::MODULE_DEBUG => static function (ContainerInterface $container) {
        return new ModuleDebug(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::LOGGER)
        );
    },

    /**
     * @return ModuleInterface
     */
    ServicesAbstract::MODULE_WOOCOMMERCE => static function (ContainerInterface $container) {
        return new ModuleWooCommerce(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::BASE_URL),
            $container->get(ServicesAbstract::PLUGIN_VERSION)
        );
    },
    /**
     * @return ModuleInterface
     */
    ServicesAbstract::MODULE_INSTANCE_PROTECTION => static function (ContainerInterface $container) {
        return new ModuleInstanceProtection(
            $container->get(ServicesAbstract::PATHS),
            $container->get(ServicesAbstract::PLUGIN_SLUG),
            $container->get(ServicesAbstract::PLUGIN_NAME)
        );
    },

    /**
     * @return ModuleInterface
     */
    ServicesAbstract::MODULE_VERSION_NOTICES => static function (ContainerInterface $container) {
        return new ModuleVersionNotices(
            $container->get(ServicesAbstract::PATHS)
        );
    },

    /**
     * @return ModuleInterface
     */
    ServicesAbstract::MODULE_EXPIRATOR => static function (ContainerInterface $container) {
        return new ModuleExpirator(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::SITE),
            $container->get(ServicesAbstract::CRON),
            $container->get(ServicesAbstract::EXPIRATION_SCHEDULER),
            $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
            $container->get(ServicesAbstract::SANITIZATION),
            $container->get(ServicesAbstract::CURRENT_USER_MODEL_FACTORY),
            $container->get(ServicesAbstract::REQUEST),
            $container->get(ServicesAbstract::ACTION_ARGS_MODEL_FACTORY),
            $container->get(ServicesAbstract::SCHEDULED_ACTIONS_TABLE_FACTORY),
            $container->get(ServicesAbstract::NOTICES),
            $container->get(ServicesAbstract::DB_TABLE_ACTION_ARGS_SCHEMA),
            $container->get(ServicesAbstract::SETTINGS),
            $container->get(ServicesAbstract::LOGGER),
            $container->get(ServicesAbstract::DATE_TIME_HANDLER),
            $container->get(ServicesAbstract::POST_TYPE_DEFAULT_DATA_MODEL_FACTORY),
            $container->get(ServicesAbstract::TAXONOMIES_MODEL_FACTORY),
            $container->get(ServicesAbstract::DATETIME),
            $container->get(ServicesAbstract::POST_TYPE_SETTINGS_MODEL_FACTORY),
            $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL),
            $container->get(ServicesAbstract::MIGRATIONS_FACTORY)
        );
    },

    /**
     * @return ModuleInterface
     */
    ServicesAbstract::MODULE_BACKUP => static function (ContainerInterface $container) {
        return new ModuleBackup(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::PLUGIN_VERSION),
            $container->get(ServicesAbstract::SETTINGS),
            $container->get(ServicesAbstract::LOGGER)
        );
    },

    /**
     * @return ModuleInterface
     */
    ServicesAbstract::MODULE_SETTINGS => static function (ContainerInterface $container) {
        return new ModuleSettings(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::SETTINGS),
            $container->get(ServicesAbstract::POST_TYPE_SETTINGS_MODEL_FACTORY),
            $container->get(ServicesAbstract::TAXONOMIES_MODEL_FACTORY),
            $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL),
            $container->get(ServicesAbstract::MIGRATIONS_FACTORY),
            $container->get(ServicesAbstract::LOGGER)
        );
    },

    ServicesAbstract::POST_MODEL_FACTORY => static function (ContainerInterface $container) {
        return function ($postId) use ($container) {
            return new PostModel(
                $postId,
                $container->get(ServicesAbstract::TERM_MODEL_FACTORY),
                $container->get(ServicesAbstract::HOOKS),
                $container->get(ServicesAbstract::LOGGER)
            );
        };
    },

    ServicesAbstract::TERM_MODEL_FACTORY => static function (ContainerInterface $container) {
        return function ($termId) use ($container) {
            return new TermModel($termId);
        };
    },

    ServicesAbstract::USER_MODEL_FACTORY => static function (ContainerInterface $container) {
        return function ($user) use ($container) {
            return new UserModel($user);
        };
    },

    ServicesAbstract::CURRENT_USER_MODEL_FACTORY => static function (ContainerInterface $container) {
        return function () use ($container) {
            return new CurrentUserModel();
        };
    },

    ServicesAbstract::POST_TYPE_DEFAULT_DATA_MODEL_FACTORY => static function (ContainerInterface $container) {
        return new PostTypeDefaultDataModelFactory(
            $container->get(ServicesAbstract::SETTINGS),
            $container->get(ServicesAbstract::OPTIONS),
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::DATE_TIME_HANDLER)
        );
    },

    ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY => static function (ContainerInterface $container) {
        /**
         * @return ExpirablePostModel
         * @throws
         */
        return function ($postId) use ($container) {
            return new ExpirablePostModel(
                $postId,
                $container->get(ServicesAbstract::OPTIONS),
                $container->get(ServicesAbstract::HOOKS),
                $container->get(ServicesAbstract::USERS),
                $container->get(ServicesAbstract::EXPIRATION_SCHEDULER),
                $container->get(ServicesAbstract::SETTINGS),
                $container->get(ServicesAbstract::EMAIL),
                $container->get(ServicesAbstract::TERM_MODEL_FACTORY),
                $container->get(ServicesAbstract::EXPIRATION_ACTION_FACTORY),
                $container->get(ServicesAbstract::ACTION_ARGS_MODEL_FACTORY),
                $container->get(ServicesAbstract::POST_TYPE_DEFAULT_DATA_MODEL_FACTORY),
                $container->get(ServicesAbstract::LOGGER)
            );
        };
    },

    ServicesAbstract::POST_TYPE_SETTINGS_MODEL_FACTORY => static function (ContainerInterface $container) {
        /**
         * @return SettingsPostTypesModel
         * @throws
         */
        return static function () {
            return new SettingsPostTypesModel();
        };
    },

    ServicesAbstract::TAXONOMIES_MODEL_FACTORY => static function (ContainerInterface $container) {
        /**
         * @return TaxonomiesModel
         * @throws
         */
        return static function () use ($container) {
            return new TaxonomiesModel(
                $container->get(ServicesAbstract::LOGGER)
            );
        };
    },

    ServicesAbstract::EXPIRATION_ACTIONS_MODEL => static function (ContainerInterface $container) {
        return new ExpirationActionsModel(
            $container->get(ServicesAbstract::HOOKS)
        );
    },

    ServicesAbstract::EXPIRATION_ACTION_FACTORY => static function (ContainerInterface $container) {
        return function ($actionName, $postModel) use ($container) {
            switch ($actionName) {
                case ExpirationActionsAbstract::CHANGE_POST_STATUS:
                    return new ChangePostStatus($postModel);

                case ExpirationActionsAbstract::POST_STATUS_TO_DRAFT:
                    return new PostStatusToDraft($postModel);

                case ExpirationActionsAbstract::POST_STATUS_TO_TRASH:
                    return new PostStatusToTrash($postModel);

                case ExpirationActionsAbstract::POST_STATUS_TO_PRIVATE:
                    return new PostStatusToPrivate($postModel);

                case ExpirationActionsAbstract::DELETE_POST:
                    return new DeletePost($postModel);

                case ExpirationActionsAbstract::STICK_POST:
                    return new StickPost($postModel);

                case ExpirationActionsAbstract::UNSTICK_POST:
                    return new UnstickPost($postModel);

                case ExpirationActionsAbstract::POST_CATEGORY_ADD:
                    return new PostCategoryAdd(
                        $postModel,
                        $container->get(ServicesAbstract::ERROR),
                        $container->get(ServicesAbstract::POST_TYPE_DEFAULT_DATA_MODEL_FACTORY)
                    );

                case ExpirationActionsAbstract::POST_CATEGORY_REMOVE:
                    return new PostCategoryRemove(
                        $postModel,
                        $container->get(ServicesAbstract::ERROR)
                    );

                case ExpirationActionsAbstract::POST_CATEGORY_SET:
                    return new PostCategorySet(
                        $postModel,
                        $container->get(ServicesAbstract::ERROR)
                    );

                case ExpirationActionsAbstract::POST_CATEGORY_REMOVE_ALL:
                    return new PostCategoryRemoveAll(
                        $postModel,
                        $container->get(ServicesAbstract::ERROR)
                    );

                default:
                    $hook = $container->get(ServicesAbstract::HOOKS);

                    return $hook->applyFilters(
                        ExpirationHooksAbstract::FILTER_EXPIRATION_ACTION_FACTORY,
                        null,
                        $actionName,
                        $postModel,
                        $container
                    );
            }
        };
    },

    ServicesAbstract::SCHEDULED_ACTIONS_TABLE_FACTORY => static function (ContainerInterface $container) {
        return function () use ($container) {
            return new ScheduledActionsTable(
                $container->get(ServicesAbstract::ACTION_SCHEDULER_STORE),
                $container->get(ServicesAbstract::ACTION_SCHEDULER_LOGGER),
                $container->get(ServicesAbstract::ACTION_SCHEDULER_RUNNER),
                $container->get(ServicesAbstract::HOOKS)
            );
        };
    },

    ServicesAbstract::ACTION_ARGS_MODEL_FACTORY => static function (ContainerInterface $container): Closure {
        return function () use ($container): ActionArgsModelInterface {
            return new ActionArgsModel(
                $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL),
                $container->get(ServicesAbstract::DB_TABLE_ACTION_ARGS_SCHEMA)
            );
        };
    },

    ServicesAbstract::ACTION_SCHEDULER_STORE => static function (ContainerInterface $container) {
        return ActionScheduler::store();
    },

    ServicesAbstract::ACTION_SCHEDULER_RUNNER => static function (ContainerInterface $container) {
        return ActionScheduler::runner();
    },

    ServicesAbstract::ACTION_SCHEDULER_LOGGER => static function (ContainerInterface $container) {
        return ActionScheduler::logger();
    },

    ServicesAbstract::MIGRATIONS_FACTORY => static function (ContainerInterface $container) {
        return function () use ($container) {
            $migrations = [
                new V30000ActionArgsSchema(
                    $container->get(ServicesAbstract::HOOKS),
                    $container->get(ServicesAbstract::DB_TABLE_ACTION_ARGS_SCHEMA)
                ),
                new V30000WPCronToActionsScheduler(
                    $container->get(ServicesAbstract::CRON),
                    $container->get(ServicesAbstract::HOOKS),
                    $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY)
                ),
                new V30000ReplaceFooterPlaceholders(
                    $container->get(ServicesAbstract::HOOKS),
                    $container->get(ServicesAbstract::OPTIONS)
                ),
                new V30001RestorePostMeta(
                    $container->get(ServicesAbstract::CRON),
                    $container->get(ServicesAbstract::HOOKS),
                    $container->get(ServicesAbstract::OPTIONS),
                    $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                    $container->get(ServicesAbstract::ACTION_SCHEDULER_STORE)
                ),
                new V30104ArgsColumnLength(
                    $container->get(ServicesAbstract::HOOKS),
                    $container->get(ServicesAbstract::DB_TABLE_ACTION_ARGS_SCHEMA)
                ),
                new V40000WorkflowScheduledStepsSchema(
                    $container->get(ServicesAbstract::HOOKS),
                    $container->get(ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA)
                ),
                new V040500OnScheduledStepsSchema(
                    $container->get(ServicesAbstract::HOOKS),
                    $container->get(ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA)
                ),
            ];

            $migrations = $container->get(ServicesAbstract::HOOKS)->applyFilters(
                HooksAbstract::FILTER_MIGRATIONS,
                $migrations
            );

            return $migrations;
        };
    },

    ServicesAbstract::WPDB => static function (ContainerInterface $container) {
        global $wpdb;

        return $wpdb;
    },

    ServicesAbstract::DB_TABLE_SCHEMA_HANDLER_FACTORY => static function (ContainerInterface $container) {
        return function () use ($container) {
            $handler = new DBTableSchemaHandler(
                $container->get(ServicesAbstract::WPDB)
            );

            return $handler;
        };
    },

    ServicesAbstract::DB_TABLE_ACTION_ARGS_SCHEMA => static function (ContainerInterface $container) {
        $schemaHandler = $container->get(ServicesAbstract::DB_TABLE_SCHEMA_HANDLER_FACTORY);
        $schemaHandler = $schemaHandler();

        return new ActionArgsSchema(
            $schemaHandler,
            $container->get(ServicesAbstract::HOOKS)
        );
    },

    ServicesAbstract::DB_TABLE_DEBUG_LOG_SCHEMA => static function (ContainerInterface $container) {
        $schemaHandler = $container->get(ServicesAbstract::DB_TABLE_SCHEMA_HANDLER_FACTORY);
        $schemaHandler = $schemaHandler();

        return new DebugLogSchema(
            $schemaHandler,
            $container->get(ServicesAbstract::HOOKS)
        );
    },

    /**
     * @return ModuleInterface
     */
    ServicesAbstract::MODULE_WORKFLOWS => static function (ContainerInterface $container) {
        return new ModuleWorkflows(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::WORKFLOWS_REST_API_MANAGER),
            $container->get(ServicesAbstract::STEP_TYPES_MODEL),
            $container->get(ServicesAbstract::CRON_SCHEDULES_MODEL),
            $container->get(ServicesAbstract::WORKFLOW_ENGINE),
            $container->get(ServicesAbstract::SETTINGS),
            $container->get(ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA),
            $container->get(ServicesAbstract::MIGRATIONS_FACTORY),
            $container->get(ServicesAbstract::PLUGIN_VERSION),
            $container->get(ServicesAbstract::CRON),
            $container->get(ServicesAbstract::LOGGER),
            $container->get(ServicesAbstract::SANITIZATION),
            $container->get(ServicesAbstract::REQUEST),
            $container->get(ServicesAbstract::CURRENT_USER_MODEL_FACTORY),
            $container->get(ServicesAbstract::OPTIONS),
            $container->get(ServicesAbstract::EMAIL)
        );
    },

    ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA => static function (ContainerInterface $container) {
        $schemaHandler = $container->get(ServicesAbstract::DB_TABLE_SCHEMA_HANDLER_FACTORY);
        $schemaHandler = $schemaHandler();

        return new WorkflowScheduledStepsSchema(
            $schemaHandler,
            $container->get(ServicesAbstract::HOOKS)
        );
    },

    ServicesAbstract::WORKFLOWS_REST_API_MANAGER => static function (ContainerInterface $container) {
        return new RestApiManager(
            $container->get(ServicesAbstract::HOOKS)
        );
    },

    ServicesAbstract::STEP_TYPES_MODEL => static function (ContainerInterface $container) {
        return new StepTypesModel(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::SETTINGS)
        );
    },

    ServicesAbstract::CRON_SCHEDULES_MODEL => static function (ContainerInterface $container) {
        return new CronSchedulesModel();
    },

    ServicesAbstract::EXECUTION_CONTEXT_REGISTRY => static function (ContainerInterface $container) {
        return new ExecutionContextRegistry(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::EXECUTION_CONTEXT_PROCESSOR_REGISTRY),
            $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY)
        );
    },

    ServicesAbstract::EXECUTION_CONTEXT_PROCESSOR_REGISTRY => static function (ContainerInterface $container) {
        return new ExecutionContextProcessorRegistry();
    },

    ServicesAbstract::EXECUTION_CONTEXT_PROCESSOR_INITIALIZER => static function (ContainerInterface $container) {
        return new ExecutionContextProcessorInitializer(
            $container->get(ServicesAbstract::EXECUTION_CONTEXT_PROCESSOR_REGISTRY),
            [
                $container->get(ServicesAbstract::EXECUTION_CONTEXT_DATE_PROCESSOR)
            ]
        );
    },

    ServicesAbstract::EXECUTION_CONTEXT_DATE_PROCESSOR => static function (ContainerInterface $container) {
        return new DateProcessor(
            $container->get(ServicesAbstract::DATE_TIME_HANDLER)
        );
    },

    ServicesAbstract::WORKFLOW_ENGINE => static function (ContainerInterface $container): WorkflowEngineInterface {
        return new WorkflowEngine(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::STEP_TYPES_MODEL),
            $container->get(ServicesAbstract::STEP_RUNNER_FACTORY),
            $container->get(ServicesAbstract::EXECUTION_CONTEXT_REGISTRY),
            $container->get(ServicesAbstract::LOGGER),
            $container->get(ServicesAbstract::EXECUTION_CONTEXT_PROCESSOR_INITIALIZER)
        );
    },

    ServicesAbstract::GENERAL_STEP_PROCESSOR_FACTORY =>
    static function (ContainerInterface $container): \Closure {
        return static function (string $workflowExecutionId) use ($container): StepProcessorInterface {
            $executionContext = $container->get(ServicesAbstract::EXECUTION_CONTEXT_REGISTRY)
                ->getExecutionContext($workflowExecutionId);

            return new GeneralStep(
                $container->get(ServicesAbstract::HOOKS),
                $executionContext,
                $container->get(ServicesAbstract::LOGGER)
            );
        };
    },

    ServicesAbstract::POST_STEP_PROCESSOR_FACTORY =>
    static function (ContainerInterface $container): \Closure {
        return static function (
            StepProcessorInterface $generalProcessor,
            string $workflowExecutionId
        ) use ($container): StepProcessorInterface {
            $executionContext = $container->get(ServicesAbstract::EXECUTION_CONTEXT_REGISTRY)
                ->getExecutionContext($workflowExecutionId);

            return new PostStep(
                $container->get(ServicesAbstract::HOOKS),
                $generalProcessor,
                $container->get(ServicesAbstract::LOGGER),
                $executionContext
            );
        };
    },

    ServicesAbstract::CRON_STEP_PROCESSOR_FACTORY =>
    static function (ContainerInterface $container): \Closure {
        return static function (
            StepProcessorInterface $generalProcessor,
            string $workflowExecutionId
        ) use ($container): AsyncStepProcessorInterface {
            $executionContext = $container->get(ServicesAbstract::EXECUTION_CONTEXT_REGISTRY)
                ->getExecutionContext($workflowExecutionId);

            return new CronStep(
                $container->get(ServicesAbstract::HOOKS),
                $generalProcessor,
                $container->get(ServicesAbstract::CRON),
                $container->get(ServicesAbstract::CRON_SCHEDULES_MODEL),
                $container->get(ServicesAbstract::PLUGIN_VERSION),
                $container->get(ServicesAbstract::LOGGER),
                $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                $executionContext
            );
        };
    },

    ServicesAbstract::STEP_RUNNER_FACTORY => static function (ContainerInterface $container) {
        return function ($nodeName, $workflowExecutionId) use ($container) {
            $hooks = $container->get(ServicesAbstract::HOOKS);

            $executionContext = $container->get(ServicesAbstract::EXECUTION_CONTEXT_REGISTRY)
                ->getExecutionContext($workflowExecutionId);

            $stepRunner = $hooks->applyFilters(
                WorkflowsHooksAbstract::FILTER_WORKFLOW_ENGINE_MAP_STEP_RUNNER,
                null,
                $nodeName,
                $workflowExecutionId
            );

            if (is_object($stepRunner)) {
                return $stepRunner;
            }

            $logger = $container->get(ServicesAbstract::LOGGER);
            $settingsModel = $container->get(ServicesAbstract::SETTINGS);

            $generalStepProcessor = call_user_func(
                $container->get(ServicesAbstract::GENERAL_STEP_PROCESSOR_FACTORY),
                $workflowExecutionId
            );

            switch ($nodeName) {
                // Triggers
                case OnInitRunner::getNodeTypeName():
                    if ($settingsModel->getExperimentalFeaturesStatus()) {
                        $stepRunner = new OnInitRunner(
                            $generalStepProcessor,
                            $logger,
                            $container->get(ServicesAbstract::WORKFLOW_ENGINE)
                        );
                    }
                    break;

                case OnAdminInitRunner::getNodeTypeName():
                    if ($settingsModel->getExperimentalFeaturesStatus()) {
                        $stepRunner = new OnAdminInitRunner(
                            $generalStepProcessor,
                            $logger
                        );
                    }
                    break;

                case OnPostSaveRunner::getNodeTypeName():
                    $inputValidatorPostQuery = call_user_func(
                        $container->get(ServicesAbstract::INPUT_VALIDATOR_POST_QUERY_FACTORY),
                        $workflowExecutionId
                    );

                    $stepRunner = new OnPostSaveRunner(
                        $container->get(ServicesAbstract::HOOKS),
                        $generalStepProcessor,
                        $inputValidatorPostQuery,
                        $logger,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $container->get(ServicesAbstract::WORKFLOW_EXECUTION_SAFEGUARD),
                        $executionContext
                    );
                    break;

                case OnPostUpdateRunner::getNodeTypeName():
                    $inputValidatorPostQuery = call_user_func(
                        $container->get(ServicesAbstract::INPUT_VALIDATOR_POST_QUERY_FACTORY),
                        $workflowExecutionId
                    );

                    $stepRunner = new OnPostUpdateRunner(
                        $container->get(ServicesAbstract::HOOKS),
                        $generalStepProcessor,
                        $inputValidatorPostQuery,
                        $logger,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $container->get(ServicesAbstract::POST_CACHE),
                        $container->get(ServicesAbstract::WORKFLOW_EXECUTION_SAFEGUARD),
                        $executionContext
                    );
                    break;

                case OnPostPublishRunner::getNodeTypeName():
                    $inputValidatorPostQuery = call_user_func(
                        $container->get(ServicesAbstract::INPUT_VALIDATOR_POST_QUERY_FACTORY),
                        $workflowExecutionId
                    );

                    $stepRunner = new OnPostPublishRunner(
                        $hooks,
                        $generalStepProcessor,
                        $inputValidatorPostQuery,
                        $executionContext,
                        $logger,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $container->get(ServicesAbstract::POST_CACHE),
                        $container->get(ServicesAbstract::WORKFLOW_EXECUTION_SAFEGUARD)
                    );
                    break;

                case OnPostStatusChangeRunner::getNodeTypeName():
                    $stepRunner = new OnPostStatusChangeRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                case OnPostScheduleRunner::getNodeTypeName():
                    $stepRunner = new OnPostScheduleRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                case OnTermsAddedRunner::getNodeTypeName():
                        $inputValidatorPostQuery = call_user_func(
                            $container->get(ServicesAbstract::INPUT_VALIDATOR_POST_QUERY_FACTORY),
                            $workflowExecutionId
                        );

                        $stepRunner = new OnTermsAddedRunner(
                            $container->get(ServicesAbstract::HOOKS),
                            $generalStepProcessor,
                            $inputValidatorPostQuery,
                            $logger,
                            $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                            $container->get(ServicesAbstract::POST_CACHE),
                            $container->get(ServicesAbstract::WORKFLOW_EXECUTION_SAFEGUARD),
                            $executionContext
                        );
                    break;

                case OnPostWorkflowEnableRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $inputValidatorPostQuery = call_user_func(
                        $container->get(ServicesAbstract::INPUT_VALIDATOR_POST_QUERY_FACTORY),
                        $workflowExecutionId
                    );

                    $stepRunner = new OnPostWorkflowEnableRunner(
                        $container->get(ServicesAbstract::HOOKS),
                        $postStepProcessor,
                        $inputValidatorPostQuery,
                        $executionContext,
                        $logger,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $container->get(ServicesAbstract::WORKFLOW_EXECUTION_SAFEGUARD)
                    );
                    break;

                case OnLegacyActionTriggerRunner::getNodeTypeName():
                    $stepRunner = new OnLegacyActionTriggerRunner(
                        $container->get(ServicesAbstract::HOOKS),
                        $generalStepProcessor,
                        $executionContext,
                        $logger,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY)
                    );
                    break;

                case OnScheduleRunner::getNodeTypeName():
                    $stepRunner = new OnScheduleRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                case OnPostMetaChangeRunner::getNodeTypeName():
                    $stepRunner = new OnPostMetaChangeRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                case OnPostAuthorChangeRunner::getNodeTypeName():
                    $stepRunner = new OnPostAuthorChangeRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                case OnPostRowActionRunner::getNodeTypeName():
                    $stepRunner = new OnPostRowActionRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                case OnUserRoleChangeRunner::getNodeTypeName():
                    $stepRunner = new OnUserRoleChangeRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                case OnCustomActionRunner::getNodeTypeName():
                    $stepRunner = new OnCustomActionRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                // Actions
                case DeletePostRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new DeletePostRunner(
                        $postStepProcessor,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $logger
                    );
                    break;

                case StickPostRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new StickPostRunner(
                        $postStepProcessor,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $logger
                    );
                    break;

                case UnstickPostRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new UnstickPostRunner(
                        $postStepProcessor,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $logger
                    );
                    break;

                case AddPostTermRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new AddPostTermRunner(
                        $postStepProcessor,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $container->get(ServicesAbstract::ERROR),
                        $logger
                    );
                    break;

                case SetPostTermRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new SetPostTermRunner(
                        $postStepProcessor,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $container->get(ServicesAbstract::ERROR),
                        $logger
                    );
                    break;

                case RemovePostTermRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new RemovePostTermRunner(
                        $postStepProcessor,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $container->get(ServicesAbstract::ERROR),
                        $logger
                    );
                    break;

                case ChangePostStatusRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new ChangePostStatusRunner(
                        $hooks,
                        $postStepProcessor,
                        $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
                        $logger
                    );
                    break;

                case SendEmailRunner::getNodeTypeName():
                    $stepRunner = new SendEmailRunner(
                        $generalStepProcessor,
                        $container->get(ServicesAbstract::EMAIL),
                        $executionContext,
                        $logger
                    );
                    break;

                case DeactivatePostWorkflowRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new DeactivatePostWorkflowRunner(
                        $postStepProcessor,
                        $executionContext,
                        $logger
                    );
                    break;

                case AddPostMetaRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new AddPostMetaRunner(
                        $postStepProcessor,
                        $logger
                    );
                    break;

                case DeletePostMetaRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new DeletePostMetaRunner(
                        $postStepProcessor,
                        $logger
                    );
                    break;

                case UpdatePostMetaRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new UpdatePostMetaRunner(
                        $postStepProcessor,
                        $logger
                    );
                    break;

                case UpdatePostRunner::getNodeTypeName():
                    $postStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::POST_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new UpdatePostRunner(
                        $postStepProcessor,
                        $logger
                    );
                    break;

                // Advanced
                case ScheduleDelayRunner::getNodeTypeName():
                    $cronStepProcessor = call_user_func(
                        $container->get(ServicesAbstract::CRON_STEP_PROCESSOR_FACTORY),
                        $generalStepProcessor,
                        $workflowExecutionId
                    );

                    $stepRunner = new ScheduleDelayRunner(
                        $cronStepProcessor,
                        $executionContext
                    );
                    break;

                case ConditionalRunner::getNodeTypeName():
                    $stepRunner = new ConditionalRunner(
                        $generalStepProcessor,
                        $executionContext,
                        $logger
                    );
                    break;

                case QueryPostsRunner::getNodeTypeName():
                    $stepRunner = new QueryPostsRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                case SendRayRunner::getNodeTypeName():
                    $stepRunner = new SendRayRunner(
                        $generalStepProcessor,
                        $executionContext,
                        $logger
                    );
                    break;

                case AppendDebugLogRunner::getNodeTypeName():
                    $stepRunner = new AppendDebugLogRunner(
                        $generalStepProcessor,
                        $executionContext,
                        $logger
                    );
                    break;

                case DoActionRunner::getNodeTypeName():
                    $stepRunner = new DoActionRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;

                case UserInteractionRunner::getNodeTypeName():
                    $stepRunner = new UserInteractionRunner(
                        $generalStepProcessor,
                        $logger
                    );
                    break;
            }

            return $hooks->applyFilters(
                WorkflowsHooksAbstract::FILTER_WORKFLOW_ENGINE_MAP_TRIGGER,
                $stepRunner,
                $nodeName,
                $executionContext
            );
        };
    },

    ServicesAbstract::INPUT_VALIDATOR_POST_QUERY_FACTORY => static function (ContainerInterface $container) {
        return static function ($workflowExecutionId) use ($container) {
            $executionContext = $container->get(ServicesAbstract::EXECUTION_CONTEXT_REGISTRY)
                ->getExecutionContext($workflowExecutionId);

            $jsonLogicEngine = call_user_func(
                $container->get(ServicesAbstract::JSON_LOGIC_ENGINE_FACTORY),
                $executionContext
            );

            return new PostQueryValidator(
                $executionContext,
                $jsonLogicEngine
            );
        };
    },

    ServicesAbstract::DATE_TIME_HANDLER => static function (ContainerInterface $container) {
        return new DateTimeHandler();
    },


    ServicesAbstract::CACHE_POSTS_WITH_FUTURE_ACTION => static function (ContainerInterface $container) {
        return new GenericCacheHandler();
    },

    ServicesAbstract::JSON_LOGIC_ENGINE_FACTORY => static function (ContainerInterface $container) {
        return static function ($executionContext) use ($container) {
            return new JsonLogicEngine(
                $executionContext
            );
        };
    },

    ServicesAbstract::POST_CACHE => static function (ContainerInterface $container) {
        return new PostCache(
            $container->get(ServicesAbstract::HOOKS)
        );
    },
    ServicesAbstract::WORKFLOW_EXECUTION_SAFEGUARD => static function (ContainerInterface $container) {
        global $workflowExecutionSafeguard;

        if (! isset($workflowExecutionSafeguard)) {
            $workflowExecutionSafeguard = new WorkflowExecutionSafeguard(
                $container->get(ServicesAbstract::HOOKS)
            );
        }

        return $workflowExecutionSafeguard;
    },
];
