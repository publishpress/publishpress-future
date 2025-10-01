<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use PostExpirator_Display;
use PostExpirator_Facade;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use Throwable;

defined('ABSPATH') or die('Direct access not allowed.');

class BulkEditController implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var \PublishPress\Future\Framework\WordPress\Facade\SanitizationFacade
     */
    private $sanitization;

    /**
     * @var CurrentUserModel
     */
    private $currentUserModel;

    /**
     * @var \PublishPress\Future\Framework\WordPress\Facade\RequestFacade
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param HookableInterface $hooksFacade
     * @param callable $expirablePostModelFactory
     * @param \PublishPress\Future\Framework\WordPress\Facade\SanitizationFacade $sanitization
     * @param \Closure $currentUserModelFactory
     * @param \PublishPress\Future\Framework\WordPress\Facade\RequestFacade $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        HookableInterface $hooksFacade,
        $expirablePostModelFactory,
        $sanitization,
        $currentUserModelFactory,
        $request,
        LoggerInterface $logger
    ) {
        $this->hooks = $hooksFacade;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->sanitization = $sanitization;
        $this->currentUserModel = $currentUserModelFactory();
        $this->request = $request;
        $this->logger = $logger;
    }

    public function initialize()
    {
        if (! $this->currentUserModel->userCanExpirePosts()) {
            return;
        }

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_BULK_EDIT_CUSTOM_BOX,
            [$this, 'registerBulkEditCustomBox'],
            10,
            2
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_ADMIN_INIT,
            [$this, 'processBulkEditUpdate']
        );

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_PRINT_SCRIPTS_EDIT,
            [$this, 'enqueueScripts']
        );
    }

    public function enqueueScripts()
    {
        wp_enqueue_script("wp-components");
        wp_enqueue_script("wp-plugins");
        wp_enqueue_script("wp-element");
        wp_enqueue_script("wp-data");

        wp_enqueue_script(
            'postexpirator-bulk-edit',
            Plugin::getScriptUrl('bulkEdit'),
            [
                'wp-i18n',
                'wp-components',
                'wp-url',
                'wp-data',
                'wp-api-fetch',
                'wp-element',
                'inline-edit-post',
                'wp-html-entities',
                'wp-plugins',
            ],
            PUBLISHPRESS_FUTURE_VERSION,
            true
        );

        wp_enqueue_style('wp-components');

        $currentScreen = get_current_screen();
        $container = Container::getInstance();
        $settingsFacade = $container->get(ServicesAbstract::SETTINGS);
        $actionsModel = $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL);
        $postType = $currentScreen->post_type;

        $postTypeDefaultConfig = $settingsFacade->getPostTypeDefaults($postType);


        $defaultDataModelFactory = $container->get(ServicesAbstract::POST_TYPE_DEFAULT_DATA_MODEL_FACTORY);
        $defaultDataModel = $defaultDataModelFactory->create($postType);

        $debug = $container->get(ServicesAbstract::DEBUG);

        $taxonomyPluralName = '';
        if (! empty($postTypeDefaultConfig['taxonomy'])) {
            $taxonomy = get_taxonomy($postTypeDefaultConfig['taxonomy']);

            if (! is_wp_error($taxonomy) && ! empty($taxonomy)) {
                $taxonomyPluralName = $taxonomy->label;
            }
        }

        if (empty($taxonomyPluralName)) {
            $taxonomyPluralName = __('Taxonomy', 'post-expirator');
        }

        $taxonomyTerms = [];
        if (! empty($postTypeDefaultConfig['taxonomy'])) {
            $taxonomyTerms = get_terms([
                'taxonomy' => $postTypeDefaultConfig['taxonomy'],
                'hide_empty' => false,
            ]);
        }

        try {
            $defaultExpirationDate = $defaultDataModel->getActionDateParts();
        } catch (Throwable $e) {
            $now = time();
            $gmDate = gmdate('Y-m-d H:i:s', $now);
            $calculatedDate = $now;

            $defaultExpirationDate = [
                'year' => date('Y', $now),
                'month' => date('m', $now),
                'day' => date('d', $now),
                'hour' => date('H', $now),
                'minute' => date('i', $now),
                'ts' => $calculatedDate,
                'iso' => $gmDate
            ];
        }

        $nonce = wp_create_nonce('__future_action');

        $metaboxTitle = $settingsFacade->getMetaboxTitle() ?? __('Future Actions', 'post-expirator');
        $metaboxCheckboxLabel = $settingsFacade->getMetaboxCheckboxLabel() ?? __('Enable Future Action', 'post-expirator');

        $hiddenFields = (array) $this->hooks->applyFilters(HooksAbstract::FILTER_HIDDEN_METABOX_FIELDS, [], $postType);

        wp_localize_script(
            'postexpirator-bulk-edit',
            'publishpressFutureBulkEditConfig',
            [
                'postTypeDefaultConfig' => $postTypeDefaultConfig,
                'defaultDate' => $defaultExpirationDate['iso'],
                'is12Hour' => get_option('time_format') !== 'H:i',
                'timeFormat' => $settingsFacade->getTimeFormatForDatePicker(),
                'startOfWeek' => get_option('start_of_week', 0),
                'actionsSelectOptions' => $actionsModel->getActionsAsOptions($postType),
                'statusesSelectOptions' => $actionsModel->getStatusesAsOptionsForPostType($postType),
                'isDebugEnabled' => $debug->isEnabled(),
                'taxonomyName' => $taxonomyPluralName,
                'taxonomyTerms' => $taxonomyTerms,
                'postType' => $currentScreen->post_type,
                'isNewPost' => false,
                'nonce' => $nonce,
                'hideCalendarByDefault' => $settingsFacade->getHideCalendarByDefault(),
                'hiddenFields' => $hiddenFields,
                'wpTimezone' => wp_timezone_string(),
                'strings' => [
                    'category' => __('Categories', 'post-expirator'),
                    'panelTitle' => $metaboxTitle,
                    'enablePostExpiration' => $metaboxCheckboxLabel,
                    'action' => __('Action', 'post-expirator'),
                    'showCalendar' => __('Show Calendar', 'post-expirator'),
                    'hideCalendar' => __('Hide Calendar', 'post-expirator'),
                    'loading' => __('Loading', 'post-expirator'),
                    // translators: the text between {{}} is the link to the settings page.
                    'timezoneSettingsHelp' => __(
                        'Timezone is controlled by the {WordPress Settings}.',
                        'post-expirator'
                    ),
                    // translators: %s is the name of the taxonomy in plural form.
                    'noTermsFound' => sprintf(
                        // translators: %s is the name of the taxonomy in plural form.
                        __('No %s found.', 'post-expirator'),
                        strtolower($taxonomyPluralName)
                    ),
                    'futureActionUpdate' => __('Future Action Update', 'post-expirator'),
                    'noTaxonomyFound' => __(
                        'You must assign a taxonomy to this post type to use this feature.',
                        'post-expirator'
                    ),
                    'noChange' => __('— No Change —', 'post-expirator'),
                    'changeAdd' => __('Add or update action for posts', 'post-expirator'),
                    'addOnly' => __('Add action if none exists for posts', 'post-expirator'),
                    'changeOnly' => __('Update the existing actions for posts', 'post-expirator'),
                    'removeOnly' => __('Remove action from posts', 'post-expirator'),
                    // translators: %s is the name of the taxonomy in plural form.
                    'newTerms' => __('New %s', 'post-expirator'),
                    // translators: %s is the name of the taxonomy in plural form.
                    'removeTerms' => __('%s to remove', 'post-expirator'),
                    // translators: %s is the name of the taxonomy in plural form.
                    'addTerms' => __('%s to add', 'post-expirator'),
                    // translators: %s is the name of the taxonomy in singular form.
                    'addTermsPlaceholder' => sprintf(
                        __('Search for %s', 'post-expirator'),
                        strtolower($taxonomyPluralName)
                    ),
                    'errorActionRequired' => __('Select an action', 'post-expirator'),
                    'errorDateRequired' => __('Select a date', 'post-expirator'),
                    'errorDateInPast' => __('Date cannot be in the past', 'post-expirator'),
                    'errorTermsRequired' => sprintf(
                        // translators: %s is the name of the taxonomy in singular form.
                        __('Please select one or more %s', 'post-expirator'),
                        strtolower($taxonomyPluralName)
                    ),
                    'newStatus' => __('New status', 'post-expirator'),
                ]
            ]
        );
    }

    public function registerBulkEditCustomBox($columnName, $postType)
    {
        try {
            $facade = PostExpirator_Facade::getInstance();

            if (
                ($columnName !== 'expirationdate')
                || (! $facade->current_user_can_expire_posts())
            ) {
                return;
            }

            // TODO: Use DI here.
            $container = Container::getInstance();
            $settingsFacade = $container->get(ServicesAbstract::SETTINGS);

            $defaults = $settingsFacade->getPostTypeDefaults($postType);

            $taxonomy = isset($defaults['taxonomy']) ? $defaults['taxonomy'] : '';
            $label = '';

            // if settings have not been configured and this is the default post type
            if (empty($taxonomy) && 'post' === $postType) {
                $taxonomy = 'category';
            }

            if (! empty($taxonomy)) {
                $tax_object = get_taxonomy($taxonomy);
                $label = $tax_object ? $tax_object->label : '';
            }

            PostExpirator_Display::getInstance()->render_template('bulk-edit', array(
                'post_type' => $postType,
                'taxonomy' => $taxonomy,
                'tax_label' => $label
            ));
        } catch (Throwable $th) {
            $this->logger->error('Error registering bulk edit custom box: ' . $th->getMessage());
        }
    }

    public function processBulkEditUpdate()
    {
        try {
            // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $doAction = isset($_GET['action']) ? $this->sanitization->sanitizeKey($_GET['action']) : '';

            if (
                ('edit' !== $doAction)
                || (! isset($_REQUEST['future_action_bulk_view']))
                || ($_REQUEST['future_action_bulk_view'] !== 'bulk-edit')
                || (! isset($_REQUEST['future_action_bulk_change_action']))
                || ($this->sanitization->sanitizeKey($_REQUEST['future_action_bulk_change_action']) === 'no-change')
            ) {
                return;
            }

            if (! $this->currentUserModel->userCanExpirePosts()) {
                return;
            }

            $this->request->checkAdminReferer('bulk-posts');

            $this->saveBulkEditData();
            // phpcs:enable
        } catch (Throwable $th) {
            $this->logger->error('Error processing bulk edit update: ' . $th->getMessage());
        }
    }

    private function updateScheduleForPostFromBulkEditData(ExpirablePostModel $postModel)
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $expireType = isset($_REQUEST['future_action_bulk_action']) ?
            $this->sanitization->sanitizeTextField($_REQUEST['future_action_bulk_action']) : '';
        $newStatus = isset($_REQUEST['future_action_bulk_new_status']) ?
            $this->sanitization->sanitizeTextField($_REQUEST['future_action_bulk_new_status']) : 'draft';

        if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_DRAFT) {
            $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
            $newStatus = 'draft';
        }

        if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_PRIVATE) {
            $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
            $newStatus = 'private';
        }

        if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_TRASH) {
            $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
            $newStatus = 'trash';
        }

        $opts = [
            'expireType' => $expireType,
            'newStatus' => $newStatus,
            'category' => isset($_REQUEST['future_action_bulk_terms']) ?
                $this->sanitization->sanitizeTextField($_REQUEST['future_action_bulk_terms']) : '',
            'categoryTaxonomy' => isset($_REQUEST['future_action_bulk_taxonomy']) ?
                $this->sanitization->sanitizeTextField($_REQUEST['future_action_bulk_taxonomy']) : '',
        ];

        if (! empty($opts['category'])) {
            // TODO: Use DI here.
            $taxonomiesModelFactory = Container::getInstance()->get(ServicesAbstract::TAXONOMIES_MODEL_FACTORY);
            $taxonomiesModel = $taxonomiesModelFactory();

            $opts['category'] = $taxonomiesModel->normalizeTermsCreatingIfNecessary(
                $opts['categoryTaxonomy'],
                explode(',', $opts['category'])
            );
        }

        if (empty($opts['categoryTaxonomy'])) {
            $opts['category'] = [];
        }

        $date = isset($_REQUEST['future_action_bulk_date']) ?
            sanitize_text_field($_REQUEST['future_action_bulk_date']) : '0';
        $date = strtotime($date);

        $this->hooks->doAction(
            HooksAbstract::ACTION_SCHEDULE_POST_EXPIRATION,
            $postModel->getPostId(),
            $date,
            $opts
        );
        // phpcs:enable
    }

    private function changeStrategyChangeOnly(ExpirablePostModel $postModel)
    {
        if ($postModel->isExpirationEnabled()) {
            $this->updateScheduleForPostFromBulkEditData($postModel);
        }
    }

    private function changeStrategyAddOnly(ExpirablePostModel $postModel)
    {
        if (! $postModel->isExpirationEnabled()) {
            $this->updateScheduleForPostFromBulkEditData($postModel);
        }
    }

    private function changeStrategyChangeAdd(ExpirablePostModel $postModel)
    {
        $this->updateScheduleForPostFromBulkEditData($postModel);
    }

    private function changeStrategyRemoveOnly(ExpirablePostModel $postModel)
    {
        if ($postModel->isExpirationEnabled()) {
            $this->hooks->doAction(HooksAbstract::ACTION_UNSCHEDULE_POST_EXPIRATION, $postModel->getPostId());
        }
    }

    private function saveBulkEditData()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $changeStrategy = $this->sanitization->sanitizeKey($_REQUEST['future_action_bulk_change_action']);
        $validStrategies = ['change-only', 'add-only', 'change-add', 'remove-only'];

        if (! in_array($changeStrategy, $validStrategies)) {
            return;
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.NonceVerification.Recommended
        $postIds = array_map('intval', (array)$_REQUEST['post']);

        if (empty($postIds)) {
            return;
        }

        $postModelFactory = $this->expirablePostModelFactory;

        foreach ($postIds as $postId) {
            $postId = (int)$postId;

            $postModel = $postModelFactory($postId);

            if (empty($postModel)) {
                continue;
            }

            switch ($changeStrategy) {
                case 'change-only':
                    $this->changeStrategyChangeOnly($postModel);
                    break;
                case 'add-only':
                    $this->changeStrategyAddOnly($postModel);
                    break;
                case 'change-add':
                    $this->changeStrategyChangeAdd($postModel);
                    break;
                case 'remove-only':
                    $this->changeStrategyRemoveOnly($postModel);
                    break;
            }
        }
    }
}
