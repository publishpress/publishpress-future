<?php

defined('ABSPATH') || exit;

class_alias(
    'PublishPress\\Future\\Core\\DI\\Container',
    'PublishPressFuture\\Core\\DI\\Container'
);

class_alias(
    'PublishPress\\Future\\Core\\Paths',
    'PublishPressFuture\\Core\\Paths'
);

class_alias(
    'PublishPress\\Future\\Core\\DI\\ServiceNotFoundException',
    'PublishPressFuture\\Core\\DI\\ServiceNotFoundException'
);

class_alias(
    'PublishPress\\Future\\Core\\DI\\ServicesAbstract',
    'PublishPressFuture\\Core\\DI\\ServicesAbstract'
);

class_alias(
    'PublishPress\\Future\\Core\\DI\\ContainerInterface',
    'PublishPressFuture\\Core\\DI\\ContainerInterface'
);

class_alias(
    'PublishPress\\Future\\Core\\DI\\ContainerNotInitializedException',
    'PublishPressFuture\\Core\\DI\\ContainerNotInitializedException'
);

class_alias(
    'PublishPress\\Future\\Core\\DI\\ServiceProvider',
    'PublishPressFuture\\Core\\DI\\ServiceProvider'
);

class_alias(
    'PublishPress\\Future\\Core\\DI\\ServiceProviderInterface',
    'PublishPressFuture\\Core\\DI\\ServiceProviderInterface'
);

class_alias(
    'PublishPress\\Future\\Core\\HookableInterface',
    'PublishPressFuture\\Core\\HookableInterface'
);

class_alias(
    'PublishPress\\Future\\Core\\Plugin',
    'PublishPressFuture\\Core\\Plugin'
);

class_alias(
    'PublishPress\\Future\\Core\\HooksAbstract',
    'PublishPressFuture\\Core\\HooksAbstract'
);

class_alias(
    'PublishPress\\Future\\Core\\Autoloader',
    'PublishPressFuture\\Core\\Autoloader'
);

class_alias(
    'PublishPress\\Future\\Framework\\InitializableInterface',
    'PublishPressFuture\\Framework\\InitializableInterface'
);

class_alias(
    'PublishPress\\Future\\Framework\\Logger\\Logger',
    'PublishPressFuture\\Framework\\Logger\\Logger'
);

class_alias(
    'PublishPress\\Future\\Framework\\Logger\\LoggerInterface',
    'PublishPressFuture\\Framework\\Logger\\LoggerInterface'
);

class_alias(
    'PublishPress\\Future\\Framework\\Logger\\LogLevelAbstract',
    'PublishPressFuture\\Framework\\Logger\\LogLevelAbstract'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\ErrorFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\ErrorFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\DateTimeFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\DateTimeFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\DatabaseFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\DatabaseFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\RequestFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\RequestFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\OptionsFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\OptionsFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\SiteFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\SiteFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\CronFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\CronFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\HooksFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\HooksFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\SanitizationFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\SanitizationFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\UsersFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\UsersFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Facade\\EmailFacade',
    'PublishPressFuture\\Framework\\WordPress\\Facade\\EmailFacade'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Models\\TermsModel',
    'PublishPressFuture\\Framework\\WordPress\\Models\\TermsModel'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Models\\CurrentUserModel',
    'PublishPressFuture\\Framework\\WordPress\\Models\\CurrentUserModel'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Models\\PostModel',
    'PublishPressFuture\\Framework\\WordPress\\Models\\PostModel'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Models\\TermModel',
    'PublishPressFuture\\Framework\\WordPress\\Models\\TermModel'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Models\\UserModel',
    'PublishPressFuture\\Framework\\WordPress\\Models\\UserModel'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Exceptions\\NonexistentTermException',
    'PublishPressFuture\\Framework\\WordPress\\Exceptions\\NonexistentTermException'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Exceptions\\WordPressErrorException',
    'PublishPressFuture\\Framework\\WordPress\\Exceptions\\WordPressErrorException'
);

class_alias(
    'PublishPress\\Future\\Framework\\WordPress\\Exceptions\\NonexistentPostException',
    'PublishPressFuture\\Framework\\WordPress\\Exceptions\\NonexistentPostException'
);

class_alias(
    'PublishPress\\Future\\Framework\\BaseException',
    'PublishPressFuture\\Framework\\BaseException'
);

class_alias(
    'PublishPress\\Future\\Framework\\ModuleInterface',
    'PublishPressFuture\\Framework\\ModuleInterface'
);

class_alias(
    'PublishPress\\Future\\Modules\\VersionNotices\\Module',
    'PublishPressFuture\\Modules\\VersionNotices\\Module'
);

class_alias(
    'PublishPress\\Future\\Modules\\Settings\\Models\\SettingsPostTypesModel',
    'PublishPressFuture\\Modules\\Settings\\Models\\SettingsPostTypesModel'
);

class_alias(
    'PublishPress\\Future\\Modules\\Settings\\Models\\TaxonomiesModel',
    'PublishPressFuture\\Modules\\Settings\\Models\\TaxonomiesModel'
);

class_alias(
    'PublishPress\\Future\\Modules\\Settings\\HooksAbstract',
    'PublishPressFuture\\Modules\\Settings\\HooksAbstract'
);

class_alias(
    'PublishPress\\Future\\Modules\\Settings\\Module',
    'PublishPressFuture\\Modules\\Settings\\Module'
);

class_alias(
    'PublishPress\\Future\\Modules\\Settings\\Controllers\\Controller',
    'PublishPressFuture\\Modules\\Settings\\Controllers\\Controller'
);

class_alias(
    'PublishPress\\Future\\Modules\\Settings\\SettingsFacade',
    'PublishPressFuture\\Modules\\Settings\\SettingsFacade'
);

class_alias(
    'PublishPress\\Future\\Modules\\WooCommerce\\Module',
    'PublishPressFuture\\Modules\\WooCommerce\\Module'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Tables\\ScheduledActionsTable',
    'PublishPressFuture\\Modules\\Expirator\\Tables\\ScheduledActionsTable'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Migrations\\V30000ActionArgsSchema',
    'PublishPressFuture\\Modules\\Expirator\\Migrations\\V30000ActionArgsSchema'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Migrations\\V30000WPCronToActionsScheduler',
    'PublishPressFuture\\Modules\\Expirator\\Migrations\\V30000WPCronToActionsScheduler'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Migrations\\V30000ReplaceFooterPlaceholders',
    'PublishPressFuture\\Modules\\Expirator\\Migrations\\V30000ReplaceFooterPlaceholders'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActions\\PostStatusToTrash',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActions\\PostStatusToTrash'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActions\\UnstickPost',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActions\\UnstickPost'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActions\\PostStatusToDraft',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActions\\PostStatusToDraft'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActions\\PostCategorySet',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActions\\PostCategorySet'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActions\\PostCategoryRemove',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActions\\PostCategoryRemove'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActions\\DeletePost',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActions\\DeletePost'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActions\\PostStatusToPrivate',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActions\\PostStatusToPrivate'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActions\\PostCategoryAdd',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActions\\PostCategoryAdd'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActions\\StickPost',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActions\\StickPost'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationActionsAbstract',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationActionsAbstract'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\CapabilitiesAbstract',
    'PublishPressFuture\\Modules\\Expirator\\CapabilitiesAbstract'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Models\\CurrentUserModel',
    'PublishPressFuture\\Modules\\Expirator\\Models\\CurrentUserModel'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Models\\ExpirationActionsModel',
    'PublishPressFuture\\Modules\\Expirator\\Models\\ExpirationActionsModel'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Models\\ActionArgsModel',
    'PublishPressFuture\\Modules\\Expirator\\Models\\ActionArgsModel'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Models\\ExpirablePostModel',
    'PublishPressFuture\\Modules\\Expirator\\Models\\ExpirablePostModel'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Exceptions\\UndefinedActionException',
    'PublishPressFuture\\Modules\\Expirator\\Exceptions\\UndefinedActionException'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\HooksAbstract',
    'PublishPressFuture\\Modules\\Expirator\\HooksAbstract'
);

class_alias(
    \PublishPress\Future\Modules\Expirator\Deprecated\Schemas\ActionArgsSchema::class,
    'PublishPress\Future\Modules\Expirator\Schemas\ActionArgsSchema'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Schemas\\ActionArgsSchema',
    'PublishPressFuture\\Modules\\Expirator\\Schemas\\ActionArgsSchema'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Adapters\\CronToWooActionSchedulerAdapter',
    'PublishPressFuture\\Modules\\Expirator\\Adapters\\CronToWooActionSchedulerAdapter'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Module',
    'PublishPressFuture\\Modules\\Expirator\\Module'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\ExpirationScheduler',
    'PublishPressFuture\\Modules\\Expirator\\ExpirationScheduler'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Controllers\\BulkEditController',
    'PublishPressFuture\\Modules\\Expirator\\Controllers\\BulkEditController'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Controllers\\ScheduledActionsController',
    'PublishPressFuture\\Modules\\Expirator\\Controllers\\ScheduledActionsController'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Controllers\\ExpirationController',
    'PublishPressFuture\\Modules\\Expirator\\Controllers\\ExpirationController'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\PostMetaAbstract',
    'PublishPressFuture\\Modules\\Expirator\\PostMetaAbstract'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Interfaces\\ActionableInterface',
    'PublishPressFuture\\Modules\\Expirator\\Interfaces\\ActionableInterface'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Interfaces\\SchedulerInterface',
    'PublishPressFuture\\Modules\\Expirator\\Interfaces\\SchedulerInterface'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Interfaces\\CronInterface',
    'PublishPressFuture\\Modules\\Expirator\\Interfaces\\CronInterface'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Interfaces\\MigrationInterface',
    'PublishPressFuture\\Modules\\Expirator\\Interfaces\\MigrationInterface'
);

class_alias(
    'PublishPress\\Future\\Modules\\Expirator\\Interfaces\\ExpirationActionInterface',
    'PublishPressFuture\\Modules\\Expirator\\Interfaces\\ExpirationActionInterface'
);

class_alias(
    'PublishPress\\Future\\Modules\\InstanceProtection\\Module',
    'PublishPressFuture\\Modules\\InstanceProtection\\Module'
);

class_alias(
    'PublishPress\\Future\\Modules\\Debug\\Debug',
    'PublishPressFuture\\Modules\\Debug\\Debug'
);

class_alias(
    'PublishPress\\Future\\Modules\\Debug\\HooksAbstract',
    'PublishPressFuture\\Modules\\Debug\\HooksAbstract'
);

class_alias(
    'PublishPress\\Future\\Modules\\Debug\\DebugInterface',
    'PublishPressFuture\\Modules\\Debug\\DebugInterface'
);

class_alias(
    'PublishPress\\Future\\Modules\\Debug\\Module',
    'PublishPressFuture\\Modules\\Debug\\Module'
);

class_alias(
    'PublishPress\\Future\\Modules\\Debug\\Controllers\\Controller',
    'PublishPressFuture\\Modules\\Debug\\Controllers\\Controller'
);

class_alias(
    'PublishPress\Future\Modules\Expirator\Models\PostTypesModel',
    'PublishPress\Future\Modules\Expirator\Models\PostTypes'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\AppendDebugLog',
    'PublishPress\Future\Modules\Workflows\Interfaces\NodeRunnerInterface'
);

// Step Processors

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Processors\Cron',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunnerProcessors\CronStep'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Processors\General',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunnerProcessors\GeneralStep'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Processors\Post',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunnerProcessors\PostStep'
);

// Step Runners - Actions

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\AddPostMetaRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostMetaAdd'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\AddPostTermRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostTermsAdd'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\ChangePostStatusRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostChangeStatus'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\ConditionalRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\ConditionalSplit'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\DeactivatePostWorkflowRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostDeactivateWorkflow'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\AppendDebugLogRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\LogAdd'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\DeletePostRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostDelete'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\DeletePostMetaRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostMetaDelete'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\QueryPostsRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostQuery'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\RemovePostTermRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostTermsRemove'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\ScheduleDelayRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostSchedule'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\SendEmailRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CoreSendEmail'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\SetPostTermRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostTermsSet'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\SendRayRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\RayDebug'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\StickPostRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostStick'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\UnstickPostRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostUnstick'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\UpdatePostMetaRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Actions\CorePostMetaUpdate'
);

// Step Definitions - Actions

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\AddPostMeta',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostMetaAdd'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\AddPostTerm',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostTermsAdd'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\ChangePostStatus',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostChangeStatus'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\Conditional',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Advanced\ConditionalSplit'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DeactivatePostWorkflow',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostDeactivateWorkflow'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\AppendDebugLog',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Advanced\LogAdd'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DeletePost',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostDelete'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DeletePostMeta',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostMetaDelete'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\QueryPosts',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Advanced\CorePostQuery'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\RemovePostTerm',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostTermsRemove'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\ScheduleDelay',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Advanced\CoreSchedule'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SendEmail',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CoreSendEmail'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SetPostTerm',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostTermsSet'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SendRay',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Advanced\RayDebug'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\StickPost',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostStick'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\UnstickPost',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostUnstick'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\UpdatePostMeta',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Actions\CorePostMetaUpdate'
);

// // Step Definitions - Triggers

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnAdminInit',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnAdminInit'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnInit',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnInit'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnLegacyActionTrigger',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\FutureLegacyAction'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostMetaChange',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnPostMetaChanged'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostPublish',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnPostPublished'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostSave',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnSavePost'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostSchedule',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnPostScheduled'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostStatusChange',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnPostStatusChanged'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostUpdate',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnPostUpdated'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostWorkflowEnable',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnManuallyEnabledForPost'
);


/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnSchedule',
    'PublishPress\Future\Modules\Workflows\Domain\NodeTypes\Triggers\CoreOnCronSchedule'
);


// Step Runners - Triggers

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnAdminInitRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnAdminInit'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnInitRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnInit'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnLegacyActionTriggerRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\FutureLegacyAction'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostMetaChangeRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnPostMetaChanged'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostPublishRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnPostPublished'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostSaveRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnSavePost'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostScheduleRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnPostScheduled'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostStatusChangeRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnPostStatusChanged'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostUpdateRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnPostUpdated'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnPostWorkflowEnableRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnManuallyEnabledForPost'
);

/** @since 4.3.1 */
class_alias(
    'PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\OnScheduleRunner',
    'PublishPress\Future\Modules\Workflows\Domain\Engine\NodeRunners\Triggers\CoreOnCronSchedule'
);


require_once __DIR__ . '/deprecated-functions.php';
