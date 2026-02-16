<?php

namespace Tests\Modules\Workflows\Domain\Engine\NodeRunnerProcessors;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Workflows\Domain\Engine\ExecutionContext;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Processors\General;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowEngineInterface;

class GeneralStepTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var HookableInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hooks;

    /**
     * @var ExecutionContextProcessorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextProcessor;

    private const DEFAULT_WORKFLOW_EXECUTION_ID = '000000-00000-00000af';

    private function getContext(): ExecutionContext
    {
        $container = Container::getInstance();

        return $container->get(ServicesAbstract::EXECUTION_CONTEXT_REGISTRY)->getExecutionContext(
            $executionId ?? self::DEFAULT_WORKFLOW_EXECUTION_ID
        );
    }

    public function testPrepareLogMessage(): void
    {
        $executionContext = $this->getContext();
        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                    'title' => 'Our Workflow',
                ],
            ],
        ]);

        $container = Container::getInstance();
        $processorFactory = $container->get(ServicesAbstract::GENERAL_STEP_PROCESSOR_FACTORY);
        $processor = call_user_func($processorFactory, self::DEFAULT_WORKFLOW_EXECUTION_ID);

        $this->assertEquals(
            '[Workflow]   → Workflow #123 → Setting up step step1',
            $processor->prepareLogMessage('Setting up step %s', 'step1')
        );

        $this->assertEquals(
            '[Workflow]   → Workflow #123 → Setting up step step1',
            $processor->prepareLogMessage('Setting up step %1$s', 'step1')
        );

        $this->assertEquals(
            '[Workflow]   → Workflow #123 → Setting up step step1 with title "step title"',
            $processor->prepareLogMessage('Setting up step %1$s with title "%2$s"', 'step1', 'step title')
        );
    }
}
