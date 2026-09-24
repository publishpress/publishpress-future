<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\AddUserRole;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class AddUserRoleRunner implements StepRunnerInterface
{
    use UserRoleResolverTrait;

    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StepProcessorInterface $stepProcessor,
        LoggerInterface $logger
    ) {
        $this->stepProcessor = $stepProcessor;
        $this->logger = $logger;
    }

    public static function getNodeTypeName(): string
    {
        return AddUserRole::getNodeTypeName();
    }

    public function setup(array $step): void
    {
        $this->stepProcessor->setup($step, [$this, 'setupCallback']);
    }

    public function setupCallback(int $userId, array $nodeSettings, array $step)
    {
        $this->stepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step, $userId, $nodeSettings) {
                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);
                $role = $this->getRoleFromSettings($nodeSettings);

                if ($role === '') {
                    $this->logger->debugWithArgs('No role selected, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $user = get_userdata($userId);
                if (! ($user instanceof \WP_User)) {
                    $this->logger->debugWithArgs('User %1$s not found | Slug: %2$s', $userId, $nodeSlug);
                    return;
                }

                $user->add_role($role);

                $this->logger->debugWithArgs(
                    'Role %1$s added to user | User ID: %2$s | Slug: %3$s',
                    $role,
                    $userId,
                    $nodeSlug
                );
            },
            $userId,
            $nodeSettings
        );
    }
}
