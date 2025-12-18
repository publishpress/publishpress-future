<?php

namespace PublishPress\Future\Modules\Workflows\Controllers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\CronSchedulesModelInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepTypesModelInterface;
use PublishPress\Future\Modules\Workflows\Models\PostAuthorsModel;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;
use PublishPress\Future\Modules\Workflows\Models\PostStatusesModel;
use PublishPress\Future\Modules\Workflows\Models\PostTermModel;
use PublishPress\Future\Modules\Workflows\Models\PostTypesModel;
use PublishPress\Future\Modules\Workflows\Models\TaxonomiesModel;
use PublishPress\Future\Modules\Workflows\Models\UserRolesModel;
use PublishPress\Future\Modules\Workflows\Module;

class WorkflowEditor implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var StepTypesModelInterface
     */
    private $stepTypesModel;

    /**
     * @var CronSchedulesModelInterface
     */
    private $cronSchedulesModel;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    public function __construct(
        HookableInterface $hooks,
        StepTypesModelInterface $stepTypesModel,
        CronSchedulesModelInterface $cronSchedulesModel,
        SettingsFacade $settingsFacade
    ) {
        $this->hooks = $hooks;
        $this->stepTypesModel = $stepTypesModel;
        $this->cronSchedulesModel = $cronSchedulesModel;
        $this->settingsFacade = $settingsFacade;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            HooksAbstract::ACTION_RENDER_WORKFLOW_EDITOR_PAGE,
            [$this, "renderEditorPage"]
        );

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_ENQUEUE_SCRIPTS,
            [$this, "enqueueScriptsEditor"]
        );

        $this->hooks->addAction(CoreHooksAbstract::ACTION_LOAD_POST_PHP, [
            $this,
            "redirectToWorkflowEditor",
        ]);

        $this->hooks->addAction(CoreHooksAbstract::ACTION_LOAD_POST_NEW_PHP, [
            $this,
            "redirectToWorkflowEditor",
        ]);
    }

    public function renderEditorPage()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $workflowId = (int)($_GET["workflow"] ?? 0);

        require_once __DIR__ . "/../Views/editor.html.php";
    }

    public function redirectToWorkflowEditor()
    {
        global $typenow, $pagenow;

        if ($typenow !== Module::POST_TYPE_WORKFLOW) {
            return;
        }

        $url = admin_url("admin.php?page=future_workflow_editor");

        if ($pagenow === "post.php") {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $postId = (int) $_GET["post"] ?? 0;

            if (empty($postId)) {
                return;
            }

            $url = add_query_arg("workflow", $postId, $url);
        } elseif ($pagenow !== "post-new.php") {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $action = $_GET["action"] ?? "";
        $actionsToByPass = [
            "trash",
            "untrash",
            "delete",
        ];

        if (in_array($action, $actionsToByPass)) {
            return;
        }

        wp_redirect($url);
        exit();
    }

    public function enqueueScriptsEditor($hook)
    {
        if ("admin_page_future_workflow_editor" !== $hook) {
            return;
        }

        global $post_type;
        if (empty($post_type)) {
            $post_type = Module::POST_TYPE_WORKFLOW;
        }

        wp_enqueue_style("wp-components");
        wp_enqueue_style("wp-edit-post");
        wp_enqueue_style("wp-editor");
        wp_enqueue_style("wp-notices");

        wp_enqueue_style(
            "future_workflow_admin_style",
            Plugin::getAssetUrl('css/workflow-editor.css'),
            ["wp-components", "wp-edit-post", "wp-editor"],
            PUBLISHPRESS_FUTURE_VERSION
        );

        wp_enqueue_script("wp-url");
        wp_enqueue_script("wp-element");
        wp_enqueue_script("wp-components");
        wp_enqueue_script("wp-data");
        wp_enqueue_script("wp-plugins");
        wp_enqueue_script("wp-notices");
        wp_enqueue_script("wp-compose");
        wp_enqueue_script(
            "future_workflow_editor_script",
            Plugin::getScriptUrl('workflowEditor'),
            [
                "wp-element",
                "wp-components",
                "wp-compose",
                "wp-url",
                "wp-data",
                "wp-api-fetch",
                "wp-plugins",
                "wp-i18n",
                "wp-notices",
            ],
            PUBLISHPRESS_FUTURE_VERSION,
            true
        );

        wp_set_script_translations(
            'future_workflow_editor_script',
            'post-expirator',
            PUBLISHPRESS_FUTURE_BASE_PATH . '/languages'
        );

        $postTypesModel = new PostTypesModel();
        $postTypes = $postTypesModel->getPostTypesAsOptions();

        $postStatusesModel = new PostStatusesModel();
        $postStatuses = $postStatusesModel->getPostStatusesAsOptions();

        $taxonomiesModel = new TaxonomiesModel();
        $taxonomies = $taxonomiesModel->getTaxonomiesAsOptions();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $workflowId = (int)($_GET["workflow"] ?? 0);

        $isPro = $this->hooks->applyFilters(HooksAbstract::FILTER_IS_PRO, false);

        $userRolesModel = new UserRolesModel();
        $userRoles = $userRolesModel->getUserRolesAsOptions();

        $postAuthorsModel = new PostAuthorsModel();
        $postAuthors = $postAuthorsModel->getAuthorsAsOptions();

        $postTermsModel = new PostTermModel();
        $postTerms = $postTermsModel->getAllTermsAsOptions();

        wp_localize_script(
            "future_workflow_editor_script",
            "futureWorkflowEditor",
            [
                "isWP65OrLater" => version_compare(
                    get_bloginfo("version"),
                    "6.5",
                    ">="
                ),
                "apiUrl" => rest_url("publishpress-future/v1"),
                "pluginVersion" => PUBLISHPRESS_FUTURE_VERSION,
                "assetsUrl" => PUBLISHPRESS_FUTURE_ASSETS_URL,
                "workflowId" => $workflowId,
                "nonce" => wp_create_nonce("wp_rest"),
                "workflowNonce" => wp_create_nonce("pp_workflow_action"),
                "dateFormat" => get_option("date_format"),
                "timeFormat" => get_option("time_format"),
                "startOfWeek" => get_option("start_of_week"),
                "nodeTypeCategories" => $this->stepTypesModel->getCategories(),
                "currentUserId" => get_current_user_id(),
                "nodeTypes" => [
                    "triggers" => array_values(
                        $this->stepTypesModel->convertInstancesToArray(
                            $this->stepTypesModel->getTriggerSteps(),
                            StepTypesModel::STEP_TYPE_TRIGGER
                        )
                    ),
                    "actions" => array_values(
                        $this->stepTypesModel->convertInstancesToArray(
                            $this->stepTypesModel->getActionSteps(),
                            StepTypesModel::STEP_TYPE_ACTION
                        )
                    ),
                    "advanced" => array_values(
                        $this->stepTypesModel->convertInstancesToArray(
                            $this->stepTypesModel->getAdvancedSteps(),
                            StepTypesModel::STEP_TYPE_ADVANCED
                        )
                    ),
                ],
                "cronSchedules" => $this->cronSchedulesModel->getCronSchedulesAsOptions(),
                "postTypes" => $postTypes,
                "postStatuses" => $postStatuses,
                "taxonomies" => $taxonomies,
                "welcomeGuidePages" => $this->getWelcomeGuidePages(),
                "isExperimentalFeaturesEnabled" => $this->settingsFacade->getExperimentalFeaturesStatus(),
                "isPro" => $isPro,
                "userRoles" => $userRoles,
                "postAuthors" => $postAuthors,
                "postTerms" => $postTerms,
            ]
        );

        $this->hooks->doAction(HooksAbstract::ACTION_WORKFLOW_EDITOR_SCRIPTS);
    }

    // phpcs:disable Generic.Files.LineLength.TooLong
    private function getWelcomeGuidePages()
    {
        return [
            [
                "title" => __("Welcome to the workflow editor", 'post-expirator'),
                "content" => __("In the PublishPress Workflow Editor, each workflow step is presented as a distinct 'node' in the workflow.", 'post-expirator'),
                "image" => '1-welcome-to-editor',
            ],
            [
                "title" => __("Use your imagination", 'post-expirator'),
                "content" => __("You're free to create very distinct workflows in your site, according to your needs.", 'post-expirator'),
                "image" => '2-use-imagination',
            ],
            [
                "title" => __("A basic workflow", 'post-expirator'),
                "content" => __("Every workflow requires at least two steps connected to each other: one trigger and one action.", 'post-expirator'),
                "image" => '3-basic-workflow',
            ],
            [
                "title" => __("Add steps to your workflow", 'post-expirator'),
                "content" => __("Drag and drop steps to add them to your workflow. Connect the steps to create a workflow.", 'post-expirator'),
                "image" => '4-add-steps',
            ],
            [
                "title" => __("Output and input", 'post-expirator'),
                "content" => __("Linked steps can pass data forward as input to the next step.", 'post-expirator'),
                "image" => '5-output-input',
            ],
            [
                "title" => __("Customize the workflow", 'post-expirator'),
                "content" => __("Click on a step to customize it. You can change the step's settings in the right sidebar.", 'post-expirator'),
                "image" => '6-customize-workflow',
            ],
            [
                "title" => __("Workflow validation", 'post-expirator'),
                "content" => __("Error messages will appear for any unfilled required settings, missed connections, or invalid values. Select the step to view the corresponding error in the sidebar.", 'post-expirator'),
                "image" => '7-workflow-validation',
            ],
            [
                "title" => __("Publish your workflow", 'post-expirator'),
                "content" => __("When you're ready, click the publish button to make your workflow live.", 'post-expirator'),
                "image" => '8-publish-workflow',
            ],
            [
                "title" => __("Need help?", 'post-expirator'),
                "content" => __("If you have any questions or need help, click the help button in the top right corner to access the support resources.", 'post-expirator'),
                "image" => '9-need-help',
            ],
        ];
    }
    // phpcs:enable Generic.Files.LineLength.TooLong
}
