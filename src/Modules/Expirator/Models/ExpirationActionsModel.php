<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Models;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\HookableInterface;
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
use PublishPress\Future\Modules\Expirator\HooksAbstract;

\defined('ABSPATH') || exit;

class ExpirationActionsModel
{
    public const ACTION_NAME_ATTRIBUTE = 'name';

    public const ACTION_LABEL_ATTRIBUTE = 'label';

    /**
     * @var \PublishPress\Future\Core\HookableInterface
     */
    private $hooks;

    /**
     * @var array
     */
    private $actions = [];

    /**
     * @var array
     */
    private $actionsAsOptions = [];

    private $statusesAsOptions = [];

    public function __construct(HookableInterface $hooks)
    {
        $this->hooks = $hooks;
    }

    /**
     * @return string[]
     */
    public function getActions($postType = '', $replaceTaxonomyNames = true)
    {
        if (! isset($this->actions[$postType])) {
            $actions = [
                ExpirationActionsAbstract::CHANGE_POST_STATUS => ChangePostStatus::getLabel(),
                ExpirationActionsAbstract::DELETE_POST => DeletePost::getLabel(),
                ExpirationActionsAbstract::STICK_POST => StickPost::getLabel(),
                ExpirationActionsAbstract::UNSTICK_POST => UnstickPost::getLabel(),
            ];

            if ($replaceTaxonomyNames) {
                // FIXME: Remove the $postType arguments, moving the text replacement to this class
                $actions = array_merge($actions, [
                    ExpirationActionsAbstract::POST_CATEGORY_SET => PostCategorySet::getLabel($postType),
                    ExpirationActionsAbstract::POST_CATEGORY_ADD => PostCategoryAdd::getLabel($postType),
                    ExpirationActionsAbstract::POST_CATEGORY_REMOVE => PostCategoryRemove::getLabel($postType),
                    ExpirationActionsAbstract::POST_CATEGORY_REMOVE_ALL => PostCategoryRemoveAll::getLabel($postType),
                ]);
            } else {
                $actions = array_merge($actions, [
                    ExpirationActionsAbstract::POST_CATEGORY_SET => PostCategorySet::getLabel(),
                    ExpirationActionsAbstract::POST_CATEGORY_ADD => PostCategoryAdd::getLabel(),
                    ExpirationActionsAbstract::POST_CATEGORY_REMOVE => PostCategoryRemove::getLabel(),
                    ExpirationActionsAbstract::POST_CATEGORY_REMOVE_ALL => PostCategoryRemoveAll::getLabel(),
                ]);
            }

            $this->actions[$postType] = $this->hooks->applyFilters(
                HooksAbstract::FILTER_EXPIRATION_ACTIONS,
                $actions,
                $postType
            );

            $this->actions[$postType] = $this->sortActions($this->actions[$postType]);
        }

        return $this->actions[$postType];
    }

    private function sortActions($actions)
    {
        $sortedActions = [];

        foreach ($actions as $name => $label) {
            $sortedActions[$name] = $label;
        }

        asort($sortedActions);

        return $sortedActions;
    }

    public function getActionsAsOptions($postType = '', $replaceTaxonomyNames = true)
    {
        if (! isset($this->actionsAsOptions[$postType])) {
            $options = [];

            $actions = $this->getActions($postType, $replaceTaxonomyNames);

            foreach ($actions as $name => $label) {
                $options[] = [
                    'value' => $name,
                    'label' => $label,
                ];
            }

            $this->actionsAsOptions[$postType] = $options;
        }

        return $this->actionsAsOptions[$postType];
    }

    public function getActionsAsOptionsForAllPostTypes($replaceTaxonomyNames = true)
    {
        $container = Container::getInstance();
        $postTypesModel = new PostTypesModel($container);

        $postTypes = array_values($postTypesModel->getPostTypes());

        $actions = [];

        foreach ($postTypes as $postType) {
            $actions[$postType] = $this->getActionsAsOptions($postType, $replaceTaxonomyNames);
        }

        return $actions;
    }

    public function getStatusesForPostType($postType)
    {
        $statuses = [
            'draft' => __('Draft'),
            'private' => __('Private'),
            'trash' => __('Trash'),
            'publish' => __('Published'),
        ];

        /**
         * Filter the expiration statuses for a specific post type.
         * @param array $statuses
         * @param string $postType
         * @return array
         */
        $statuses = $this->hooks->applyFilters(
            HooksAbstract::FILTER_EXPIRATION_STATUSES,
            $statuses,
            $postType
        );

        asort($statuses);

        return $statuses;
    }

    public function getStatusesAsOptionsForPostType($postType)
    {
        if (isset($this->statusesAsOptions[$postType]) && !empty($this->statusesAsOptions[$postType])) {
            return $this->statusesAsOptions[$postType];
        }

        $statuses = $this->getStatusesForPostType($postType);

        $this->statusesAsOptions[$postType] = array_map(
            function ($label, $value) {
                return [
                    'label' => $label,
                    'value' => $value,
                ];
            },
            $statuses,
            array_keys($statuses)
        );

        return $this->statusesAsOptions[$postType];
    }

    public function getStatusesAsOptionsForAllPostTypes()
    {
        if (!empty($this->statusesAsOptions)) {
            return $this->statusesAsOptions;
        }

        $container = Container::getInstance();
        $postTypesModel = new PostTypesModel($container);

        $postTypes = array_values($postTypesModel->getPostTypes());

        foreach ($postTypes as $postType) {
            $this->statusesAsOptions[$postType] = $this->getStatusesAsOptionsForPostType($postType);
        }

        return $this->statusesAsOptions;
    }

    public function getLabelForAction($actionName, $postType = "")
    {
        $actions = $this->getActions($postType);

        return $actions[$actionName] ?? '';
    }
}
