<?php

namespace Tests\CustomCode;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract;

class ScheduleUsingHookTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    public function test_schedule_expiration_change_status_custom_status(): void
    {
        register_post_status('expired', ['label' => 'Expired', 'public' => true]);

        $postId = $this->factory()->post->create(
            [
                'post_status' => 'publish',
                'post_type' => 'post',
            ]
        );

        $options = [
            'expireType' => 'change-status',
            'newStatus' => 'expired',
            'id' => $postId,
        ];

        $timestamp = strtotime('+2 days');

        $allowExpiredStatus = static function ($statuses) {
            $statuses['expired'] = 'Expired';

            return $statuses;
        };

        add_filter(HooksAbstract::FILTER_EXPIRATION_STATUSES, $allowExpiredStatus);

        try {
            do_action(
                HooksAbstract::ACTION_SCHEDULE_POST_EXPIRATION,
                $postId,
                $timestamp,
                $options
            );

            $container = Container::getInstance();
            $factory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
            /** @var \PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel $postModel */
            $postModel = $factory($postId);

            $expirationAction = $postModel->getExpirationAction();

            $this->assertTrue($postModel->isExpirationEnabled());
            $this->assertIsObject($expirationAction);
            $this->assertEquals('expired', $postModel->getExpirationNewStatus());
            $this->assertEquals($timestamp, $postModel->getExpirationDateAsUnixTime());
        } finally {
            remove_filter(HooksAbstract::FILTER_EXPIRATION_STATUSES, $allowExpiredStatus);
        }
    }
}
