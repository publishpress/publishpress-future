<?php

namespace PublishPress\Future\Modules\Expirator;

\defined('ABSPATH') || exit;

abstract class ExpirationActionsAbstract
{
    public const POST_STATUS_TO_DRAFT = 'draft';
    public const POST_STATUS_TO_PRIVATE = 'private';
    public const POST_STATUS_TO_TRASH = 'trash';
    public const DELETE_POST = 'delete';
    public const STICK_POST = 'stick';
    public const UNSTICK_POST = 'unstick';
    public const POST_CATEGORY_SET = 'category';
    public const POST_CATEGORY_ADD = 'category-add';
    public const POST_CATEGORY_REMOVE = 'category-remove';
    public const POST_CATEGORY_REMOVE_ALL = 'category-remove-all';
    public const CHANGE_POST_STATUS = "change-status";
}
