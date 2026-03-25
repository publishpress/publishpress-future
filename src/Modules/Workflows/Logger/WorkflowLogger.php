<?php

/**
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPL v2 or later
 * @since       4.10.0
 */

namespace PublishPress\Future\Modules\Workflows\Logger;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;

/**
 * Decorator around LoggerInterface that automatically prepends the workflow
 * context prefix ([WorkflowEngine:X]: ) to all log messages.
 *
 * @since 4.10.0
 */
class WorkflowLogger implements LoggerInterface
{
    private const LOG_PREFIX = '[WorkflowEngine:%d]: ';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var int
     */
    private $workflowId;

    /**
     * @param LoggerInterface           $logger           Inner logger to delegate to.
     * @param ExecutionContextInterface $executionContext  Execution context for resolving workflow ID.
     * @since 4.10.0
     */
    public function __construct(LoggerInterface $logger, ExecutionContextInterface $executionContext)
    {
        $this->logger = $logger;
        $this->executionContext = $executionContext;
        $this->workflowId = $executionContext->getVariable('global.workflow.id');
    }

    /**
     * Prepend the workflow context prefix to a message.
     *
     * @param string $message Raw message.
     * @return string Prefixed message.
     * @since 4.10.0
     */
    private function formatMessage(string $message): string
    {
        return sprintf(self::LOG_PREFIX, $this->workflowId) . $message;
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function debugWithArgs(string $message, ...$args): void
    {
        if (! $this->logger->isDebugEnabled()) {
            return;
        }

        $this->debug(sprintf($message, ...$args));
    }

    /**
     * {@inheritDoc}
     *
     * Errors will always be logged, even if debug is disabled.
     *
     * @since 4.10.0
     */
    public function errorWithArgs(string $message, ...$args): void
    {
        $this->error(sprintf($message, ...$args));
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function warningWithArgs(string $message, ...$args): void
    {
        if (! $this->logger->isDebugEnabled()) {
            return;
        }

        $this->warning(sprintf($message, ...$args));
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function infoWithArgs(string $message, ...$args): void
    {
        if (! $this->logger->isDebugEnabled()) {
            return;
        }

        $this->info(sprintf($message, ...$args));
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function noticeWithArgs(string $message, ...$args): void
    {
        if (! $this->logger->isDebugEnabled()) {
            return;
        }

        $this->notice(sprintf($message, ...$args));
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed            $message
     * @param array<mixed>     $context
     * @since 4.10.0
     */
    public function emergency($message, $context = [])
    {
        $this->logger->emergency($this->formatMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed            $message
     * @param array<mixed>     $context
     * @since 4.10.0
     */
    public function alert($message, $context = [])
    {
        $this->logger->alert($this->formatMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed            $message
     * @param array<mixed>     $context
     * @since 4.10.0
     */
    public function critical($message, $context = [])
    {
        $this->logger->critical($this->formatMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed            $message
     * @param array<mixed>     $context
     * @since 4.10.0
     */
    public function error($message, $context = [])
    {
        $this->logger->error($this->formatMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed            $message
     * @param array<mixed>     $context
     * @since 4.10.0
     */
    public function warning($message, $context = [])
    {
        $this->logger->warning($this->formatMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed            $message
     * @param array<mixed>     $context
     * @since 4.10.0
     */
    public function notice($message, $context = [])
    {
        $this->logger->notice($this->formatMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed            $message
     * @param array<mixed>     $context
     * @since 4.10.0
     */
    public function info($message, $context = [])
    {
        $this->logger->info($this->formatMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed            $message
     * @param array<mixed>     $context
     * @since 4.10.0
     */
    public function debug($message, $context = [])
    {
        $this->logger->debug($this->formatMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed            $level
     * @param mixed            $message
     * @param array<mixed>     $context
     * @since 4.10.0
     */
    public function log($level, $message, $context = [])
    {
        $this->logger->log($level, $this->formatMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function markCurrentRequestHasTriggerActivated()
    {
        $this->logger->markCurrentRequestHasTriggerActivated();
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function deleteLogs()
    {
        $this->logger->deleteLogs();
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function fetchAll($triggerActivatedOnly = false)
    {
        return $this->logger->fetchAll($triggerActivatedOnly);
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function fetchLatest($limit = 100, $triggerActivatedOnly = false)
    {
        return $this->logger->fetchLatest($limit, $triggerActivatedOnly);
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function getTotalLogs($triggerActivatedOnly = false)
    {
        return $this->logger->getTotalLogs($triggerActivatedOnly);
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function getLogSizeInBytes($triggerActivatedOnly = false)
    {
        return $this->logger->getLogSizeInBytes($triggerActivatedOnly);
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function dropDatabaseTable()
    {
        $this->logger->dropDatabaseTable();
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function isDownloadLogRequested()
    {
        return $this->logger->isDownloadLogRequested();
    }

    /**
     * {@inheritDoc}
     *
     * @since 4.10.0
     */
    public function isDebugEnabled()
    {
        return $this->logger->isDebugEnabled();
    }
}
