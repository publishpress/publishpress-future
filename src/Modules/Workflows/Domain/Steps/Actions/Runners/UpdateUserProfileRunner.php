<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\UpdateUserProfile;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class UpdateUserProfileRunner implements StepRunnerInterface
{
    /**
     * Map of node-setting field name => wp_update_user() userdata key.
     */
    private const FIELD_MAP = [
        'displayName' => 'display_name',
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'email' => 'user_email',
        'url' => 'user_url',
        'description' => 'description',
    ];

    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StepProcessorInterface $stepProcessor,
        ExecutionContextInterface $executionContext,
        LoggerInterface $logger
    ) {
        $this->stepProcessor = $stepProcessor;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
    }

    public static function getNodeTypeName(): string
    {
        return UpdateUserProfile::getNodeTypeName();
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

                $userData = ['ID' => $userId];

                foreach (self::FIELD_MAP as $field => $userDataKey) {
                    $value = $this->resolveFieldValue($nodeSettings[$field] ?? '');
                    if ($value === '') {
                        continue;
                    }

                    if ($userDataKey === 'user_email') {
                        $value = sanitize_email($value);
                        if ($value === '' || ! is_email($value)) {
                            $this->logger->debugWithArgs(
                                'Invalid email, skipping email update | Slug: %1$s',
                                $nodeSlug
                            );
                            continue;
                        }
                    }

                    $userData[$userDataKey] = $value;
                }

                if (count($userData) === 1) {
                    $this->logger->debugWithArgs('No profile fields to update, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $result = wp_update_user($userData);

                if (is_wp_error($result)) {
                    $this->logger->errorWithArgs(
                        'Failed to update user %1$s: %2$s | Slug: %3$s',
                        $userId,
                        $result->get_error_message(),
                        $nodeSlug
                    );
                    return;
                }

                $this->logger->debugWithArgs(
                    'User profile updated | User ID: %1$s | Slug: %2$s',
                    $userId,
                    $nodeSlug
                );
            },
            $userId,
            $nodeSettings
        );
    }

    /**
     * @param mixed $field
     */
    private function resolveFieldValue($field): string
    {
        if (is_array($field)) {
            $field = $field['expression'] ?? '';
        }

        return trim($this->executionContext->resolveExpressionsInText((string)$field));
    }
}
