<?php

namespace PublishPress\Future\Modules\Workflows\Domain\LegacyAction;

use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Expirator\Interfaces\ExpirationActionInterface;
use PublishPress\Future\Modules\Workflows\HooksAbstract;

\defined('ABSPATH') || exit;

class TriggerWorkflow implements ExpirationActionInterface
{
    public const ACTION_NAME = 'trigger-workflow';

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var array
     */
    private $log = [];

    private $postModel;

    private $container;

    public function __construct(HookableInterface $hooks, $postModel, $container)
    {
        $this->hooks = $hooks;
        $this->postModel = $postModel;
        $this->container = $container;
    }

    public function execute()
    {
        $postId = $this->postModel->getPostId();
        $post = get_post($postId);

        $actionsArgsModelFactory = $this->container->get(ServicesAbstract::ACTION_ARGS_MODEL_FACTORY);
        $actionsArgsModel = $actionsArgsModelFactory();
        $actionsArgsModel->loadByPostId($postId, true);

        $args = $actionsArgsModel->getArgs();
        $args['postId'] = $postId;

        if (isset($args['extraData']['workflowId'])) {
            $args['workflowId'] = $args['extraData']['workflowId'];
        }

        if (isset($args['expireType']) && $args['expireType'] === self::ACTION_NAME) {
            $this->hooks->doAction(HooksAbstract::ACTION_LEGACY_ACTION, $postId, $post, $args);
        }
    }

    /**
     * @return string
     */
    public function getNotificationText()
    {
        if (empty($this->log)) {
            return __('The workflow was not triggered.', 'post-expirator');
        } elseif (isset($this->log['error'])) {
            return $this->log['error'];
        }

        return __('The workflow was triggered by the legacy action', 'post-expirator');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::getLabel();
    }

    public static function getLabel(string $postType = ''): string
    {
        return __("Trigger workflow", 'post-expirator');
    }

    /**
     * @return string
     */
    public function getDynamicLabel($postType = '')
    {
        return self::getLabel($postType);
    }
}
