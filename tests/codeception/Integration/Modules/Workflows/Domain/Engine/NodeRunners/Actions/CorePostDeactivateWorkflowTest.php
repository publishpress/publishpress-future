<?php

namespace Tests\Modules\Workflows\Domain\Engine\NodeRunners\Actions;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\WorkflowResolver;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners\DeactivatePostWorkflowRunner;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Models\PostModel;

class CorePostDeactivateWorkflowTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    private function createWorkflows(): array
    {
        $ids = [];

        $ids[] = wp_insert_post([
            'post_title' => 'Test workflow 1',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'workflow'
        ]);

        $ids[] = wp_insert_post([
            'post_title' => 'Test workflow 2',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'workflow'
        ]);

        return $ids;
    }

    public function testDisablingWorkflowOnPost(): void
    {
        $postId = wp_insert_post([
            'post_title' => 'Test post',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post'
        ]);

        $workflows = $this->createWorkflows();

        $runner = new DeactivatePostWorkflowRunner(
            $this->makeEmpty(StepProcessorInterface::class, [
                'executeSafelyWithErrorHandling' => function ($step, $callback, ...$args) {
                    call_user_func($callback, $step, ...$args);
                }
            ]),
            $this->makeEmpty(ExecutionContextInterface::class, [
                'getVariable' => new WorkflowResolver(['id' => $workflows[0]])
            ]),
            $this->makeEmpty(LoggerInterface::class)
        );

        $model = new PostModel();
        $model->load($postId);

        $model->addManuallyEnabledWorkflow($workflows[0]);

        $this->assertEquals([$workflows[0]], $model->getManuallyEnabledWorkflows());

        $runner->setupCallback(
            $postId,
            [
                'post' => [
                    'variable' => 'onManualEnableForPost1.post',
                ],
                'workflow' => [
                    'variable' => 'global.workflow',
                ],
                'status' => [
                    'variable' => 'disable',
                ],
            ],
            [],
            [
                'global.workflow' => new WorkflowResolver(['id' => $workflows[0]]),
            ]
        );

        $this->assertEmpty($model->getManuallyEnabledWorkflows());
    }
}
