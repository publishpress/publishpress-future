<?php

/**
 * Unit tests for WorkflowExecutionSafeguard infinite loop detection.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace unit\Modules\Workflows\Infrastructure\Safety;

use Codeception\Test\Feature\Stub;
use Codeception\Test\Unit;
use Exception;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\Infrastructure\Safety\WorkflowExecutionSafeguard;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use UnitTester;

/**
 * @since 4.10.4
 */
class WorkflowExecutionSafeguardTest extends Unit
{
    use Stub;

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * Step fixture used by infinite-loop detection tests.
     *
     * @var array
     */
    private array $step = [
        'node' => [
            'id' => 'onPostUpdate1',
        ],
    ];

    /**
     * Different uniqueIds in the same request must not be treated as an infinite loop
     * (bulk-edit case / #1652).
     *
     * @throws Exception
     *
     * @since 4.10.4
     */
    public function test_should_not_detect_infinite_loop_for_different_unique_ids()
    {
        $safeguard = $this->createSafeguard();
        $executionContext = $this->createExecutionContext();

        $firstResult = $safeguard->detectInfiniteLoop($executionContext, $this->step, '100');
        $secondResult = $safeguard->detectInfiniteLoop($executionContext, $this->step, '101');

        $this->assertFalse($firstResult);
        $this->assertFalse($secondResult);
    }

    /**
     * Re-entry with the same uniqueId on the same instance is an infinite loop via runningNodes.
     *
     * @throws Exception
     *
     * @since 4.10.4
     */
    public function test_should_detect_infinite_loop_for_same_unique_id_on_same_instance()
    {
        $safeguard = $this->createSafeguard();
        $executionContext = $this->createExecutionContext();

        $firstResult = $safeguard->detectInfiniteLoop($executionContext, $this->step, '100');
        $secondResult = $safeguard->detectInfiniteLoop($executionContext, $this->step, '100');

        $this->assertFalse($firstResult);
        $this->assertTrue($secondResult);
    }

    /**
     * Same uniqueId with different steps on the same instance hits triggerExecutionCache
     * (cache key excludes step id; runningNodes includes it).
     *
     * @throws Exception
     *
     * @since 4.10.4
     */
    public function test_should_detect_infinite_loop_for_same_unique_id_across_different_steps()
    {
        $safeguard = $this->createSafeguard();
        $executionContext = $this->createExecutionContext();

        $firstStep = [
            'node' => [
                'id' => 'onPostUpdate1',
            ],
        ];
        $secondStep = [
            'node' => [
                'id' => 'onPostSave1',
            ],
        ];

        $firstResult = $safeguard->detectInfiniteLoop($executionContext, $firstStep, '100');
        $secondResult = $safeguard->detectInfiniteLoop($executionContext, $secondStep, '100');

        $this->assertFalse($firstResult);
        $this->assertTrue($secondResult);
    }

    /**
     * @return WorkflowExecutionSafeguard
     *
     * @throws Exception
     */
    private function createSafeguard(): WorkflowExecutionSafeguard
    {
        $hooks = $this->makeEmpty(HookableInterface::class);

        return new WorkflowExecutionSafeguard($hooks);
    }

    /**
     * @return ExecutionContextInterface
     *
     * @throws Exception
     */
    private function createExecutionContext(): ExecutionContextInterface
    {
        $variables = [
            'global.workflow.id' => 'workflow-1',
            'global.engine_execution_id' => 'engine-1',
            'global.workflow.execution_id' => 'execution-1',
            'global.trigger.id' => 'trigger-1',
        ];

        return $this->makeEmpty(
            ExecutionContextInterface::class,
            [
                'getVariable' => function (string $name) use ($variables) {
                    return $variables[$name] ?? null;
                },
            ]
        );
    }
}
