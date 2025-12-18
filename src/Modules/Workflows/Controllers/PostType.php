<?php

namespace PublishPress\Future\Modules\Workflows\Controllers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Module;
use PublishPress\Future\Modules\Workflows\CapabilitiesAbstract as WorkflowCapabilities;

class PostType implements InitializableInterface
{
    public const MENU_CAPABILITY = 'manage_options';

    /**
     * @var HookableInterface
     */
    private $hooks;

    public function __construct(HookableInterface $hooks)
    {
        $this->hooks = $hooks;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_AFTER_INIT_PLUGIN,
            [$this, "registerPostType"]
        );

        $this->hooks->addFilter(
            HooksAbstract::FILTER_SUPPORTED_POST_TYPES,
            [$this, 'hideActionWorkflowsFromSettings']
        );
    }

    public function registerPostType()
    {
        $showInMenu = "publishpress-future";

        if (!current_user_can(self::MENU_CAPABILITY)) {
            $showInMenu = false;
        }

        register_post_type(Module::POST_TYPE_WORKFLOW, [
            "labels" => [
                "name" => __("Action Workflows", "post-expirator"),
                "singular_name" => __("Future Workflow", "post-expirator"),
                "add_new" => __("Add New", "post-expirator"),
                "add_new_item" => __("Add New Workflow", "post-expirator"),
                "edit_item" => __("Edit Workflow", "post-expirator"),
                "new_item" => __("New Workflow", "post-expirator"),
                "view_item" => __("View Workflow", "post-expirator"),
                "search_items" => __("Search Workflows", "post-expirator"),
                "not_found" => __("No Workflows found", "post-expirator"),
                "not_found_in_trash" => __("No Workflows found in Trash", "post-expirator"),
                "parent_item_colon" => __("Parent Workflow:", "post-expirator"),
                "all_items" => __("All Workflows", "post-expirator"),
                "archives" => __("Workflow Archives", "post-expirator"),
                "insert_into_item" => __("Insert into workflow", "post-expirator"),
                "uploaded_to_this_item" => __("Uploaded to this workflow", "post-expirator"),
                "filter_items_list" => __("Filter workflows list", "post-expirator"),
                "items_list_navigation" => __("Workflows list navigation", "post-expirator"),
                "items_list" => __("Action Workflows list", "post-expirator"),
                "item_published" => __("Workflow published.", "post-expirator"),
                "item_published_privately" => __("Workflow published privately.", "post-expirator"),
                "item_reverted_to_draft" => __("Workflow reverted to draft.", "post-expirator"),
                "item_scheduled" => __("Workflow scheduled.", "post-expirator"),
                "item_updated" => __("Workflow updated.", "post-expirator"),
            ],
            'capabilities' => [
                'read_post' => WorkflowCapabilities::EDIT_WORKFLOWS,
                'read_private_posts' => WorkflowCapabilities::EDIT_WORKFLOWS,
                'edit_post' => WorkflowCapabilities::EDIT_WORKFLOWS,
                'edit_posts' => WorkflowCapabilities::EDIT_WORKFLOWS,
                'edit_others_posts' => WorkflowCapabilities::EDIT_WORKFLOWS,
                'publish_posts' => WorkflowCapabilities::PUBLISH_WORKFLOWS,
                'delete_post' => WorkflowCapabilities::UNPUBLISH_WORKFLOWS,
                'delete_posts' => WorkflowCapabilities::UNPUBLISH_WORKFLOWS,
            ],
            "public" => false,
            "show_ui" => true,
            "show_in_menu" => $showInMenu,
            "show_in_nav_menus" => false,
            "show_in_admin_bar" => true,
            "menu_position" => 20,
            "menu_icon" => "dashicons-randomize",
            "hierarchical" => false,
            "supports" => ["title"],
            "has_archive" => false,
            "rewrite" => false,
            "query_var" => false,
            "can_export" => true,
            "delete_with_user" => false,
            "show_in_rest" => false,
            "rest_base" => "workflows",
        ]);
    }

    public function hideActionWorkflowsFromSettings($postTypes)
    {
        if (in_array(Module::POST_TYPE_WORKFLOW, $postTypes, true)) {
            $postTypes = array_diff($postTypes, [Module::POST_TYPE_WORKFLOW]);
        }

        return $postTypes;
    }
}
