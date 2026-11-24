# Creating a Node Runner

The "Node Rnuner" is the class containing the logic executed by the specific node. It is only used at runtime. The workflow engine instantiates the specific runner for the node type of the step in execution.

## Adding a Node Runner

Create a class that implements the `StepRunnerInterface` interface and add it to the `services.php` file in the node types runner factory service.

## Node Runner Interface

A node runner must implement the interface `PublishPress\FuturePro\Modules\Workflows\Interfaces\StepRunnerInterface`.

```php
<?php

namespace PublishPress\FuturePro\Modules\Workflows\Interfaces;

interface StepRunnerInterface
{
    /**
     * The node type name.
     */
    public static function getNodeTypeName(): string;

    /**
     * Setup the node runner with the step and context variables, and
     * execute the next steps if needed.
     */
    public function setup(array $step, array $contextVariables = []): void;
}

```

## Node Runner Example

The following code implements a node runner for a step that sticks the post set in the step's settings.

```php
<?php

namespace PublishPress\FuturePro\Modules\Workflows\Domain\Engine\NodeRunners\Actions;

use PublishPress\FuturePro\Modules\Workflows\Domain\NodeTypes\Actions\CorePostStick as NodeTypeCorePostStick;
use PublishPress\FuturePro\Modules\Workflows\Interfaces\StepRunnerInterface;
use PublishPress\FuturePro\Modules\Workflows\Interfaces\NodeRunnerPreparerInterface;

class CorePostStick implements StepRunnerInterface
{
    /**
     * @var NodeRunnerPreparerInterface
     */
    private $nodeRunnerPreparer;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    public function __construct(
        NodeRunnerPreparerInterface $nodeRunnerPreparer,
        \Closure $expirablePostModelFactory
    ) {
        $this->nodeRunnerPreparer = $nodeRunnerPreparer;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
    }

    public static function getNodeTypeName(): string
    {
        return NodeTypeCorePostStick::getNodeTypeName();
    }

    public function setup(array $step, array $contextVariables = []): void
    {
        $this->nodeRunnerPreparer->setup($step, [$this, 'actionCallback'], $contextVariables);
    }

    public function actionCallback(int $postId, array $nodeSettings)
    {
        $postModel = call_user_func($this->expirablePostModelFactory, $postId);
        $postModel->stick();
    }
}

```
