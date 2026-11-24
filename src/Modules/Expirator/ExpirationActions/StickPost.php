<?php

namespace PublishPress\Future\Modules\Expirator\ExpirationActions;

use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\ExpirationActionInterface;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;

\defined('ABSPATH') || exit;

class StickPost implements ExpirationActionInterface
{
    public const SERVICE_NAME = 'expiration.actions.stick_post';

    /**
     * @var ExpirablePostModel
     */
    private $postModel;

    /**
     * @var array
     */
    private $log = [];

    /**
     * @param ExpirablePostModel $postModel
     */
    public function __construct($postModel)
    {
        $this->postModel = $postModel;
    }

    public function __toString()
    {
        return ExpirationActionsAbstract::STICK_POST;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationText()
    {
        if (empty($this->log) || ! $this->log['success']) {
            return sprintf(
                // translators: %s: post type singular label
                __('%s didn\'t change.', 'post-expirator'),
                $this->postModel->getPostTypeSingularLabel()
            );
        }

        return sprintf(
            // translators: %s: post type singular label
            __('%s has been added to stickies list.', 'post-expirator'),
            $this->postModel->getPostTypeSingularLabel()
        );
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = $this->postModel->stick();

        $this->log['success'] = $result;

        return $result;
    }

    /**
     * @return string
     */
    public static function getLabel(string $postType = ''): string
    {
        return __('Stick', 'post-expirator');
    }

    public function getDynamicLabel($postType = '')
    {
        return self::getLabel($postType);
    }
}
