<?php

/**
 * Regression tests for workflow publish, unpublish, and delete capabilities.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace Tests\Modules\Workflows\Regression;

use Codeception\Stub;
use lucatume\WPBrowser\TestCase\WPTestCase;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Utils\WorkflowSanitizationUtil;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\CapabilitiesAbstract;
use PublishPress\Future\Modules\Workflows\Controllers\WorkflowsList;
use PublishPress\Future\Modules\Workflows\Interfaces\StepTypesModelInterface;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Module;
use PublishPress\Future\Modules\Workflows\Rest\RestApiV1;
use RuntimeException;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_User;

/**
 * @group regression
 */
class WorkflowStatusAuthorizationTest extends WPTestCase
{
    /**
     * @var array<string, mixed>
     */
    private $originalPost;

    /**
     * @var string|null
     */
    private $originalRequestMethod;

    /**
     * @since 4.12.0
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->originalPost = $_POST;
        $this->originalRequestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
    }

    /**
     * @since 4.12.0
     */
    public function tearDown(): void
    {
        $_POST = $this->originalPost;

        if (null === $this->originalRequestMethod) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->originalRequestMethod;
        }

        remove_filter('wp_redirect', [$this, 'throwOnRedirect'], 1);

        parent::tearDown();
    }

    /**
     * Stop the list handler before its redirect calls exit.
     *
     * @since 4.12.0
     */
    public function throwOnRedirect(): void
    {
        throw new RuntimeException('workflow_status_redirect');
    }

    /**
     * @since 4.12.0
     */
    public function test_should_reject_publish_when_user_lacks_publish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([CapabilitiesAbstract::EDIT_WORKFLOWS]),
            WorkflowModel::STATUS_DISABLED
        );

        $result = $this->restApi()->updateWorkflow($this->statusRequest($workflowId, 'publish'));

        $this->assertForbidden($result);
        $this->assertSame(WorkflowModel::STATUS_DISABLED, get_post_status($workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_reject_edit_of_enabled_workflow_when_user_lacks_publish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([CapabilitiesAbstract::EDIT_WORKFLOWS]),
            WorkflowModel::STATUS_ENABLED
        );

        $request = $this->statusRequest($workflowId, 'publish');
        $request->set_param('title', 'Changed enabled workflow');

        $result = $this->restApi()->updateWorkflow($request);

        $this->assertForbidden($result);
        $this->assertSame(WorkflowModel::STATUS_ENABLED, get_post_status($workflowId));
        $this->assertNotSame('Changed enabled workflow', get_post_field('post_title', $workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_reject_unpublish_when_user_lacks_unpublish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([
                CapabilitiesAbstract::EDIT_WORKFLOWS,
                CapabilitiesAbstract::PUBLISH_WORKFLOWS,
            ]),
            WorkflowModel::STATUS_ENABLED
        );

        $result = $this->restApi()->updateWorkflow(
            $this->statusRequest($workflowId, WorkflowModel::STATUS_DISABLED)
        );

        $this->assertForbidden($result);
        $this->assertSame(WorkflowModel::STATUS_ENABLED, get_post_status($workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_reject_delete_when_user_lacks_unpublish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([CapabilitiesAbstract::EDIT_WORKFLOWS]),
            WorkflowModel::STATUS_DISABLED
        );

        $result = $this->restApi()->deleteWorkflow($this->idRequest($workflowId));

        $this->assertForbidden($result);
        $this->assertSame(WorkflowModel::STATUS_DISABLED, get_post_status($workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_publish_when_user_has_publish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([
                CapabilitiesAbstract::EDIT_WORKFLOWS,
                CapabilitiesAbstract::PUBLISH_WORKFLOWS,
            ]),
            WorkflowModel::STATUS_DISABLED
        );

        $result = $this->restApi()->updateWorkflow($this->statusRequest($workflowId, 'publish'));

        $this->assertNotInstanceOf(WP_Error::class, $result);
        $this->assertSame(WorkflowModel::STATUS_ENABLED, get_post_status($workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_unpublish_when_user_has_publish_and_unpublish_caps(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([
                CapabilitiesAbstract::EDIT_WORKFLOWS,
                CapabilitiesAbstract::PUBLISH_WORKFLOWS,
                CapabilitiesAbstract::UNPUBLISH_WORKFLOWS,
                'edit_published_posts',
            ]),
            WorkflowModel::STATUS_ENABLED
        );

        $result = $this->restApi()->updateWorkflow(
            $this->statusRequest($workflowId, WorkflowModel::STATUS_DISABLED)
        );

        $this->assertNotInstanceOf(WP_Error::class, $result);
        $this->assertSame(WorkflowModel::STATUS_DISABLED, get_post_status($workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_delete_when_user_has_unpublish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([
                CapabilitiesAbstract::EDIT_WORKFLOWS,
                CapabilitiesAbstract::UNPUBLISH_WORKFLOWS,
            ]),
            WorkflowModel::STATUS_DISABLED
        );

        $result = $this->restApi()->deleteWorkflow($this->idRequest($workflowId));

        $this->assertNotInstanceOf(WP_Error::class, $result);
        $post = get_post($workflowId);
        if ($post instanceof WP_Post) {
            $this->assertSame('trash', $post->post_status);
        } else {
            $this->assertNull($post);
        }
    }

    /**
     * A body without status is not a publish or unpublish.
     *
     * @since 4.12.0
     */
    public function test_should_save_when_status_is_omitted_and_user_lacks_publish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([CapabilitiesAbstract::EDIT_WORKFLOWS]),
            WorkflowModel::STATUS_DISABLED
        );

        $request = $this->idRequest($workflowId);
        $request->set_param('title', 'Renamed draft');

        $result = $this->restApi()->updateWorkflow($request);

        $this->assertNotInstanceOf(WP_Error::class, $result);
        $this->assertSame(WorkflowModel::STATUS_DISABLED, get_post_status($workflowId));
        $this->assertSame('Renamed draft', get_post_field('post_title', $workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_reject_list_publish_when_user_lacks_publish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([CapabilitiesAbstract::EDIT_WORKFLOWS]),
            WorkflowModel::STATUS_DISABLED
        );
        $this->setStatusChangePost($workflowId, 'publish');

        $this->workflowsList()->updateWorkflowStatus();

        $this->assertSame(WorkflowModel::STATUS_DISABLED, get_post_status($workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_reject_list_unpublish_when_user_lacks_unpublish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([CapabilitiesAbstract::EDIT_WORKFLOWS]),
            WorkflowModel::STATUS_ENABLED
        );
        $this->setStatusChangePost($workflowId, WorkflowModel::STATUS_DISABLED);

        $this->workflowsList()->updateWorkflowStatus();

        $this->assertSame(WorkflowModel::STATUS_ENABLED, get_post_status($workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_publish_from_list_when_user_has_publish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([
                CapabilitiesAbstract::EDIT_WORKFLOWS,
                CapabilitiesAbstract::PUBLISH_WORKFLOWS,
            ]),
            WorkflowModel::STATUS_DISABLED
        );
        $this->setStatusChangePost($workflowId, 'publish');
        add_filter('wp_redirect', [$this, 'throwOnRedirect'], 1);

        try {
            $this->workflowsList()->updateWorkflowStatus();
            $this->fail('Expected the status change to redirect.');
        } catch (RuntimeException $exception) {
            $this->assertSame('workflow_status_redirect', $exception->getMessage());
        }

        $this->assertSame(WorkflowModel::STATUS_ENABLED, get_post_status($workflowId));
    }

    /**
     * @since 4.12.0
     */
    public function test_should_unpublish_from_list_when_user_has_unpublish_cap(): void
    {
        $workflowId = $this->workflowForUser(
            $this->userWithCaps([
                CapabilitiesAbstract::EDIT_WORKFLOWS,
                CapabilitiesAbstract::UNPUBLISH_WORKFLOWS,
                'edit_published_posts',
            ]),
            WorkflowModel::STATUS_ENABLED
        );
        $this->setStatusChangePost($workflowId, WorkflowModel::STATUS_DISABLED);
        add_filter('wp_redirect', [$this, 'throwOnRedirect'], 1);

        try {
            $this->workflowsList()->updateWorkflowStatus();
            $this->fail('Expected the status change to redirect.');
        } catch (RuntimeException $exception) {
            $this->assertSame('workflow_status_redirect', $exception->getMessage());
        }

        $this->assertSame(WorkflowModel::STATUS_DISABLED, get_post_status($workflowId));
    }

    /**
     * @since 4.12.0
     *
     * @param string[] $capabilities
     */
    private function userWithCaps(array $capabilities): int
    {
        $userId = (int) $this->factory()->user->create(
            [
                'role' => 'subscriber',
            ]
        );

        $user = new WP_User($userId);
        foreach ($capabilities as $capability) {
            $user->add_cap($capability);
        }

        return $userId;
    }

    /**
     * @since 4.12.0
     *
     * @param int    $userId
     * @param string $status
     */
    private function workflowForUser(int $userId, string $status): int
    {
        $workflowId = wp_insert_post(
            [
                'post_title' => 'Workflow capability regression',
                'post_status' => $status,
                'post_type' => Module::POST_TYPE_WORKFLOW,
                'post_author' => $userId,
            ]
        );

        $this->assertIsInt($workflowId);
        $this->assertGreaterThan(0, $workflowId);

        wp_set_current_user($userId);
        $user = new WP_User($userId);
        $this->assertTrue($user->has_cap(CapabilitiesAbstract::EDIT_WORKFLOWS));

        return (int) $workflowId;
    }

    /**
     * @since 4.12.0
     */
    private function restApi(): RestApiV1
    {
        return new RestApiV1(
            Stub::makeEmpty(HookableInterface::class),
            new WorkflowSanitizationUtil()
        );
    }

    /**
     * @since 4.12.0
     */
    private function workflowsList(): WorkflowsList
    {
        return new WorkflowsList(
            Stub::makeEmpty(HookableInterface::class),
            Stub::makeEmpty(StepTypesModelInterface::class),
            Stub::makeEmpty(LoggerInterface::class),
            Stub::makeEmpty(SettingsFacade::class)
        );
    }

    /**
     * @since 4.12.0
     *
     * @param int $workflowId
     */
    private function idRequest(int $workflowId): WP_REST_Request
    {
        $request = new WP_REST_Request('POST');
        $request->set_param('id', $workflowId);

        return $request;
    }

    /**
     * @since 4.12.0
     *
     * @param int    $workflowId
     * @param string $status
     */
    private function statusRequest(int $workflowId, string $status): WP_REST_Request
    {
        $request = $this->idRequest($workflowId);
        $request->set_param('status', $status);

        return $request;
    }

    /**
     * @since 4.12.0
     *
     * @param int    $workflowId
     * @param string $newStatus
     */
    private function setStatusChangePost(int $workflowId, string $newStatus): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['workflow_id'] = (string) $workflowId;
        $_POST['new_status'] = $newStatus;
        $_POST['workflow_nonce'] = wp_create_nonce('workflow_status_change');
    }

    /**
     * @since 4.12.0
     *
     * @param mixed $result
     */
    private function assertForbidden($result): void
    {
        $this->assertInstanceOf(WP_Error::class, $result);
        $data = $result->get_error_data();
        $this->assertIsArray($data);
        $this->assertSame(403, $data['status']);
    }
}
