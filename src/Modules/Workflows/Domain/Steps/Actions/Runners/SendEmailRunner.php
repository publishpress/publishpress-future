<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\EmailFacade;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SendEmail;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;

class SendEmailRunner implements StepRunnerInterface
{
    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var EmailFacade
     */
    private $emailFacade;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $stepSlug;

    public function __construct(
        StepProcessorInterface $stepProcessor,
        EmailFacade $emailFacade,
        ExecutionContextInterface $executionContext,
        LoggerInterface $logger
    ) {
        $this->stepProcessor = $stepProcessor;
        $this->emailFacade = $emailFacade;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
    }

    public static function getNodeTypeName(): string
    {
        return SendEmail::getNodeTypeName();
    }

    public function setup(array $step): void
    {
        $this->stepSlug = $this->stepProcessor->getSlugFromStep($step);

        $this->stepProcessor->setup($step, [$this, 'setupCallback']);
    }

    public function setupCallback(array $step)
    {
        $this->logDebugMessage('Start processing email sending');

        $this->stepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step) {
                $node = $this->stepProcessor->getNodeFromStep($step);
                $nodeSettings = $this->stepProcessor->getNodeSettings($node);

                $subject = $this->getSubject($nodeSettings);
                $message = $this->getMessage($nodeSettings);
                $recipients = $this->getRecipients($nodeSettings);

                if (empty($recipients)) {
                    $this->logDebugMessage('List of recipients is empty');

                    return;
                }

                foreach ($recipients as $recipientAddress) {
                    $recipientAddress = trim(sanitize_email($recipientAddress));

                    if (empty($recipientAddress)) {
                        $this->logDebugMessage('Recipient is empty');

                        continue;
                    }

                    $this->logDebugMessage(
                        'Sending email to %1$s | Subject: %2$s',
                        $recipientAddress,
                        $subject
                    );

                    $emailSent = $this->emailFacade->send($recipientAddress, $subject, $message);

                    $debugMessage = $emailSent ? 'Email sent' : 'Email not sent';
                    $this->logDebugMessage($debugMessage);
                }
            }
        );
    }

    private function logDebugMessage(string $message, ...$args): void
    {
        $message .= ' | Slug: ' . $this->stepSlug;

        $this->logger->debug($this->stepProcessor->prepareLogMessage($message, ...$args));
    }

    private function getRecipients(array $nodeSettings): array
    {
        if (isset($nodeSettings['recipient']['expression'])) {
            $recipient = $nodeSettings['recipient']['expression'];
        } elseif (isset($nodeSettings['recipient']['recipient'])) {
            // Legacy settings
            $recipient = $nodeSettings['recipient']['recipient'] ?? null;
        }

        if (empty($recipient)) {
            $recipient = '{{global.site.admin_email}}';
        }

        if ($recipient === 'custom') {
            // Legacy settings
            $recipient = $nodeSettings['recipient']['custom'] ?? '';
        }

        if (false === strpos($recipient, '{{')) {
            // Legacy settings
            $recipient = $this->executionContext->getVariable($recipient);
        } else {
            $recipient = $this->executionContext->resolveExpressionsInText($recipient);
        }

        if (! is_array($recipient)) {
            $recipient = explode(',', (string)$recipient);
        }

        $recipient = array_map('trim', $recipient);
        $recipient = array_unique($recipient);

        return $recipient;
    }

    private function getSubject(array $nodeSettings): string
    {
        $subject = $nodeSettings['subject'] ?? '';

        if (is_array($subject) && isset($subject['expression'])) {
            $subject = $subject['expression'];
        }

        if (empty($subject)) {
            $subject = SendEmail::getDefaultSubject();
        }
        $subject = $this->executionContext->resolveExpressionsInText($subject);

        return sanitize_text_field($subject);
    }

    private function getMessage(array $nodeSettings): string
    {
        $message = $nodeSettings['message'] ?? '';

        if (is_array($message) && isset($message['expression'])) {
            $message = $message['expression'];
        }

        if (empty($message)) {
            $message = SendEmail::getDefaultMessage();
        }

        $message = $this->executionContext->resolveExpressionsInText($message);

        // TODO: Add support for HTML emails. Block editor or separated email templates?
        return sanitize_textarea_field($message);
    }
}
