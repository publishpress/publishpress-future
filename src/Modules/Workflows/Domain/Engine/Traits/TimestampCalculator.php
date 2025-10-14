<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\Traits;

trait TimestampCalculator
{
    protected function calculateTimestamp(
        string $dateStrategy = 'now',
        string $dateSource = 'calendar',
        string $specificDate = '',
        string $customDate = '',
        string $offset = ''
    ) {
        $timestamp = 0;

        switch ($dateStrategy) {
            case 'now':
            case 'event':
                $timestamp = time();
                break;
            case 'date':
            case 'offset':
                if ($dateSource === 'calendar') {
                    $timestamp = strtotime($specificDate);
                } elseif ($dateSource === 'event') {
                    $timestamp = $this->executionContext->getVariable('global.trigger.activation_timestamp');
                } elseif ($dateSource === 'step') {
                    $timestamp = time();
                } elseif ($dateSource === 'custom') {
                    $timestamp = $this->executionContext->resolveExpressionsInText($customDate);
                    // use current time when no custom date source field is empty
                    if (empty($timestamp) && !empty($offset)) {
                        $timestamp = time();
                    }
                } else {
                    $timestamp = $this->executionContext->getVariable($dateSource);
                }

                break;
        }

        if (is_numeric($timestamp)) {
            $timestamp = (int)$timestamp;
        } else {
            $timestamp = strtotime($timestamp);

            if ($timestamp === false) {
                $timestamp = null;
            }
        }

        if (empty($timestamp)) {
            return null;
        }

        if ($dateStrategy === 'offset') {
            if (! empty($offset)) {
                $timestamp = strtotime($offset, (int)$timestamp);
            }
        }

        return $timestamp;
    }
}
