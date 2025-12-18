<?php

namespace PublishPress\Future\Modules\Expirator\ExpirationActions;

use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\ExpirationActionInterface;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;

defined('ABSPATH') or die('Direct access not allowed.');

class ChangePostStatus implements ExpirationActionInterface
{
    public const SERVICE_NAME = 'expiration.actions.change_post_status';

    /**
     * @var ExpirablePostModel
     */
    private $postModel;

    /**
     * @var array
     */
    private $log = [];

    /**
     * @var string
     */
    private $oldPostStatus;

    /**
     * @var string
     */
    private $newPostStatus;

    /**
     * @param ExpirablePostModel $postModel
     */
    public function __construct($postModel)
    {
        $this->postModel = $postModel;
    }

    public function __toString()
    {
        return ExpirationActionsAbstract::CHANGE_POST_STATUS;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationText()
    {
        if (empty($this->log) || ! $this->log['success']) {
            return __('Status didn\'t change.', 'post-expirator');
        }

        $oldPostStatus = get_post_status_object($this->oldPostStatus);
        $newPostStatus = get_post_status_object($this->postModel->getExpirationNewStatus());

        return sprintf(
            // translators: 1: old post status, 2: new post status
            __('Status has been successfully changed from "%1$s" to "%2$s".', 'post-expirator'),
            $oldPostStatus->label,
            $newPostStatus->label
        );
    }

    /**
     * @inheritDoc
     * @throws \PublishPress\Future\Framework\WordPress\Exceptions\NonexistentPostException
     */
    public function execute()
    {
        $this->oldPostStatus = $this->postModel->getPostStatus();
        $this->newPostStatus = $this->postModel->getExpirationNewStatus();

        if ($this->newPostStatus === 'publish') {
            $result = $this->postModel->publish();
        } else {
            $result = $this->postModel->setPostStatus($this->newPostStatus);
        }

        $this->log['success'] = $result;

        return $result;
    }

    /**
     * @return string
     */
    public static function getLabel(string $postType = ''): string
    {
        return __('Change status', 'post-expirator');
    }

    public function getDynamicLabel($postType = '')
    {
        return self::getLabel($postType);
    }
}
