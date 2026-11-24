<?php

namespace Tests\Modules\Workflows\Domain\Engine;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Workflows\Domain\Engine\ExecutionContext;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\WorkflowResolver;

class ExecutionContextTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    private const DEFAULT_WORKFLOW_EXECUTION_ID = '000000-00000-00000af';

    private function getContext($executionId = null): ExecutionContext
    {
        $container = Container::getInstance();

        return $container->get(ServicesAbstract::EXECUTION_CONTEXT_REGISTRY)->getExecutionContext(
            $executionId ?? self::DEFAULT_WORKFLOW_EXECUTION_ID
        );
    }

    public function testSetAllVariablesCanBeRetrievedCorrectly(): void
    {
        $executionContext = $this->getContext();

        $expectedVariables = [
            'test' => 'value',
            'global' => [
                'workflow' => [
                    'id' => 123,
                ],
            ],
        ];

        $executionContext->setAllVariables($expectedVariables);

        // Verify through the public API instead of using reflection
        $this->assertEquals($expectedVariables, $executionContext->getAllVariables());
    }

    public function testGetVariableReturnsCorrectValueForSimpleVariable(): void
    {
        $executionContext = $this->getContext();

        $expectedVariables = [
            'test' => 'value',
            'timestamp' => 1234567890,
            'boolean' => true,
        ];

        $executionContext->setAllVariables($expectedVariables);

        $this->assertEquals('value', $executionContext->getVariable('test'));
        $this->assertEquals(1234567890, $executionContext->getVariable('timestamp'));
        $this->assertEquals(true, $executionContext->getVariable('boolean'));
    }

    public function testGetVariableReturnsCorrectValueForEdgeCases(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([]);

        $this->assertEquals('non-existent', $executionContext->getVariable('non-existent'));
        $this->assertEquals('', $executionContext->getVariable(''));
        $this->assertEquals(false, $executionContext->getVariable(false));
        $this->assertEquals(true, $executionContext->getVariable(true));
        $this->assertEquals(0, $executionContext->getVariable(0));
    }

    public function testGetVariableReturnsCorrectValueForDeeplyNestedVariable(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                ],
            ],
        ]);

        $this->assertEquals(123, $executionContext->getVariable('global.workflow.id'));
    }

    public function testGetVariableReturnsCorrectValueFromDifferentBranches(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                ],
                'site' => [
                    'name' => 'Test Site',
                ],
            ],
        ]);

        $this->assertEquals(123, $executionContext->getVariable('global.workflow.id'));
        $this->assertEquals('Test Site', $executionContext->getVariable('global.site.name'));
    }

    public function testGetVariableReturnsCorrectValueFromTopLevelBranch(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'onSavePost1' => [
                'postId' => 234,
            ]
        ]);

        $this->assertEquals(234, $executionContext->getVariable('onSavePost1.postId'));
    }

    public function testGetVariableReturnsPathForNonExistentVariable(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                ],
            ],
        ]);

        // This behavior seems unusual - typically you'd expect null or an exception
        // for a non-existent variable, not the path itself
        $this->assertEquals('non-existent.id', $executionContext->getVariable('non-existent.id'));
    }

    public function testSetVariableExtendsExistingNestedStructure(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                ],
            ],
        ]);

        $executionContext->setVariable('global.site.id', 234);

        $allVariables = $executionContext->getAllVariables();

        $this->assertEquals(234, $allVariables['global']['site']['id']);
        $this->assertEquals(
            123,
            $allVariables['global']['workflow']['id'],
            'Existing nested structure should be preserved'
        );
    }

    public function testSetVariableAddsToExistingBranch(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'site' => [
                    'id' => 234,
                ],
            ],
        ]);

        $executionContext->setVariable('global.site.name', 'New Site Name');

        $allVariables = $executionContext->getAllVariables();

        $this->assertEquals('New Site Name', $allVariables['global']['site']['name']);
        $this->assertEquals(
            234,
            $allVariables['global']['site']['id'],
            'Existing values in the same branch should be preserved'
        );
    }

    public function testSetVariableCreatesNewBranch(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                ],
            ],
        ]);

        $executionContext->setVariable('user.id', 1);

        $allVariables = $executionContext->getAllVariables();

        $this->assertEquals(1, $allVariables['user']['id']);
        $this->assertEquals(
            123,
            $allVariables['global']['workflow']['id'],
            'Existing structure should be preserved when creating new branches'
        );
    }

    public function testSetVariableWithVariableResolverInterface(): void
    {
        $executionContext = $this->getContext();

        $workflow = [
            'ID' => 123,
            'title' => 'Test Workflow',
            'description' => 'Test Workflow Description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '00000-00000-00000-00000',
            'execution_trace' => [],
        ];

        $workflowResolver = new WorkflowResolver($workflow);

        $executionContext->setAllVariables(
            [
                'global' => [
                    'workflow' => $workflowResolver,
                ],
            ]
        );

        $this->assertEquals(123, $executionContext->getVariable('global.workflow.ID'));

        $executionContext->setVariable('global.workflow.execution_trace', ['step1', 'step2']);
        $executionContext->setVariable('global.workflow.execution_id', '11111-11111-11111-11111');

        $this->assertEquals(['step1', 'step2'], $executionContext->getVariable('global.workflow.execution_trace'));
        $this->assertEquals('11111-11111-11111-11111', $executionContext->getVariable('global.workflow.execution_id'));
    }

    public function testExtractExpressionsFromTextWithBasicVariables(): void
    {
        $executionContext = $this->getContext();

        $text = 'This is a test {{variable1}} and {{variable2}}';
        $placeholders = $executionContext->extractExpressionsFromText($text);

        $this->assertEquals(['variable1', 'variable2'], $placeholders);
    }

    public function testExtractExpressionsFromTextWithNestedVariables(): void
    {
        $executionContext = $this->getContext();

        $textWithNested = 'Testing {{global.workflow.id}} and {{step.variable}}';
        $nestedPlaceholders = $executionContext->extractExpressionsFromText($textWithNested);

        $this->assertEquals(['global.workflow.id', 'step.variable'], $nestedPlaceholders);
    }

    public function testExtractExpressionsFromTextWithHelperFunctions(): void
    {
        $executionContext = $this->getContext();

        $textWithHelpers = 'Testing {{format date.created}} and {{uppercase user.name}}';
        $helperPlaceholders = $executionContext->extractExpressionsFromText($textWithHelpers);

        $this->assertEquals(['format date.created', 'uppercase user.name'], $helperPlaceholders);
    }

    public function testExtractExpressionsFromTextWithHelperFunctionsAndArguments(): void
    {
        $executionContext = $this->getContext();

        $textWithHelpers = 'Testing {{format date.created format="Y-m-d"}} and {{date user.created format="Y-m-d" output="U"}}';
        $helperPlaceholders = $executionContext->extractExpressionsFromText($textWithHelpers);

        $this->assertEquals(['format date.created format="Y-m-d"', 'date user.created format="Y-m-d" output="U"'], $helperPlaceholders);
    }

    public function testExtractExpressionsFromTextWithNoExpressions(): void
    {
        $executionContext = $this->getContext();

        $textWithoutExpressions = 'This text has no expressions';
        $emptyPlaceholders = $executionContext->extractExpressionsFromText($textWithoutExpressions);

        $this->assertEmpty($emptyPlaceholders);
    }

    public function testResolveExpressionsInTextWithBasicVariables(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'step' => [
                'variable1' => 'value1',
                'variable2' => 'value2',
            ],
        ]);

        $text = 'This is a test {{step.variable1}} and {{step.variable2}}';
        $resolvedText = $executionContext->resolveExpressionsInText($text);

        $this->assertEquals('This is a test value1 and value2', $resolvedText);
    }

    public function testResolveExpressionsInTextWithNestedVariables(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                    'name' => 'Test Workflow',
                ],
            ],
        ]);

        $text = 'Workflow ID: {{global.workflow.id}}, Name: {{global.workflow.name}}';
        $resolvedText = $executionContext->resolveExpressionsInText($text);

        $this->assertEquals('Workflow ID: 123, Name: Test Workflow', $resolvedText);
    }

    public function testResolveExpressionsInTextWithMixedVariables(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'step' => [
                'variable1' => 'value1',
                'variable2' => 'value2',
            ],
            'global' => [
                'workflow' => [
                    'id' => 123,
                ],
            ],
        ]);

        $text = 'This is a test {{step.variable1}} and {{step.variable2}} on the workflow {{global.workflow.id}}';
        $resolvedText = $executionContext->resolveExpressionsInText($text);

        $this->assertEquals('This is a test value1 and value2 on the workflow 123', $resolvedText);
    }

    public function testResolveExpressionsInTextWithNonExistentVariables(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'step' => [
                'variable1' => 'value1',
            ],
        ]);

        $text = 'This variable exists: {{step.variable1}}, but this one does not: {{step.nonexistent}}';
        $resolvedText = $executionContext->resolveExpressionsInText($text);

        $this->assertEquals('This variable exists: value1, but this one does not: nonexistent', $resolvedText);
    }

    public function testResolveExpressionsInTextWithNoExpressions(): void
    {
        $executionContext = $this->getContext();

        $text = 'This text has no expressions to resolve';
        $resolvedText = $executionContext->resolveExpressionsInText($text);

        $this->assertEquals($text, $resolvedText);
    }

    public function testResolveExpressionsInArrayWithBasicVariables(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'variable1' => 'value1',
            'variable2' => 'value2',
            'variable3' => 'value3',
            'variable4' => 'value4'
        ]);

        $array = [
            1,
            '1',
            'This is a test {{variable1}} and {{variable2}}',
            'This is another test {{variable3}} and {{variable4}}',
        ];

        $resolvedArray = $executionContext->resolveExpressionsInArray($array);

        $this->assertEquals(
            [
                1,
                '1',
                'This is a test value1 and value2',
                'This is another test value3 and value4'
            ],
            $resolvedArray
        );
    }

    public function testResolveExpressionsInJsonLogicWithBasicVariables(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'variable1' => 'value1',
        ]);

        $jsonLogic = [
            'var' => '{{variable1}}',
        ];

        $resolvedJsonLogic = $executionContext->resolveExpressionsInJsonLogic($jsonLogic);

        $this->assertEquals(['var' => 'value1'], $resolvedJsonLogic);
    }

    public function testResolveExpressionsInJsonLogicWithSimpleNestedVariable(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                    'name' => 'Test Workflow',
                ],
            ],
        ]);

        $jsonLogic = [
            'var' => '{{global.workflow.id}}',
        ];

        $resolvedJsonLogic = $executionContext->resolveExpressionsInJsonLogic($jsonLogic);

        $this->assertEquals(['var' => 123], $resolvedJsonLogic);
    }

    public function testResolveExpressionsInJsonLogicWithMultipleNestedVariables(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                    'name' => 'Test Workflow',
                ],
            ],
        ]);

        $jsonLogic = [
            'and' => [
                '{{global.workflow.id}}',
                '{{global.workflow.name}}',
            ],
        ];

        $resolvedJsonLogic = $executionContext->resolveExpressionsInJsonLogic($jsonLogic);

        $this->assertEquals(['and' => [123, 'Test Workflow']], $resolvedJsonLogic);
    }

    public function testResolveExpressionsInJsonLogicWithNestedLogicStructure(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'id' => 123,
                    'name' => 'Test Workflow',
                ],
            ],
        ]);

        $jsonLogic = [
            'and' => [
                'or' => [
                    '{{global.workflow.id}}',
                    '{{global.workflow.name}}',
                ],
            ],
        ];

        $resolvedJsonLogic = $executionContext->resolveExpressionsInJsonLogic($jsonLogic);

        $this->assertEquals(['and' => ['or' => [123, 'Test Workflow']]], $resolvedJsonLogic);
    }

    public function testResolveExpressionsInJsonLogicVariableHelper(): void
    {
        $executionContext = $this->getContext();

        $executionContext->setAllVariables([
            'global' => [
                'workflow' => [
                    'created' => '2021-01-01 00:00:00',
                ],
            ],
        ]);

        $jsonLogic = [
            'var' => '{{date global.workflow.created input="Y-m-d H:i:s" output="U"}}',
        ];

        $resolvedJsonLogic = $executionContext->resolveExpressionsInJsonLogic($jsonLogic);

        $this->assertEquals(['var' => '1609459200'], $resolvedJsonLogic);
    }
}
