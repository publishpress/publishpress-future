<?php

/**
 * Integration tests for CurrentUserModel live current-user reads.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace Tests\Modules\Expirator\Models;

use PublishPress\Future\Framework\WordPress\Models\CurrentUserModel as FrameworkCurrentUserModel;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use lucatume\WPBrowser\TestCase\WPTestCase;

class CurrentUserModelTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    public function setUp(): void
    {
        parent::setUp();

        wp_set_current_user(0);
    }

    public function tearDown(): void
    {
        wp_set_current_user(0);

        parent::tearDown();
    }

    /**
     * Construct while guest, then authenticate later on the same instance.
     *
     * Mirrors REST Application Password timing: the model is created during
     * plugin init, before determine_current_user runs.
     */
    public function test_should_use_live_current_user_after_constructing_as_guest(): void
    {
        wp_set_current_user(0);

        $model = new CurrentUserModel();

        $this->assertSame(0, $model->getId());
        $this->assertFalse($model->userCanExpirePosts());

        $user = $this->factory()->user->create_and_get(['role' => 'subscriber']);
        $user->add_cap(CapabilitiesAbstract::EXPIRE_POST);
        wp_set_current_user($user->ID);

        $this->assertTrue($model->userCanExpirePosts());
        $this->assertSame((int) $user->ID, $model->getId());
        $this->assertSame((int) $user->ID, (int) $model->getUserInstance()->ID);

        wp_set_current_user(0);

        $this->assertFalse($model->userCanExpirePosts());
        $this->assertSame(0, $model->getId());
    }

    /**
     * Framework CurrentUserModel must also ignore the constructor snapshot.
     */
    public function test_should_read_live_user_from_framework_current_user_model(): void
    {
        wp_set_current_user(0);

        $model = new FrameworkCurrentUserModel();

        $this->assertSame(0, $model->getId());
        $this->assertSame(0, (int) $model->getUserInstance()->ID);

        $userId = (int) $this->factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($userId);

        $this->assertSame($userId, $model->getId());
        $this->assertSame($userId, (int) $model->getUserInstance()->ID);

        wp_set_current_user(0);

        $this->assertSame(0, $model->getId());
        $this->assertSame(0, (int) $model->getUserInstance()->ID);
    }
}
