<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\ContextProcessors;

use DateTime;
use DateTimeZone;
use PublishPress\Future\Framework\System\DateTimeHandlerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextProcessorInterface;
use Throwable;

class DateProcessor implements ExecutionContextProcessorInterface
{
    private DateTimeHandlerInterface $dateTimeHandler;

    public function __construct(DateTimeHandlerInterface $dateTimeHandler)
    {
        $this->dateTimeHandler = $dateTimeHandler;
    }

    public function getType(): string
    {
        return 'date';
    }

    public function process(string $value, array $parameters)
    {
        try {
            // We want the parameters keys to be case insensitive.
            $parameters = array_change_key_case($parameters, CASE_UPPER);

            $inputFormat = $parameters['INPUT'] ?? 'Y-m-d H:i:s';
            $outputFormat = $parameters['OUTPUT']
                ?? $this->dateTimeHandler->getDateTimeFormat() . ' ' . $this->dateTimeHandler->getTimeFormat();
            $convertToUtc = isset($parameters['CONVERTTOUTC']) ? (bool)$parameters['CONVERTTOUTC'] : false;

            // Parse the date string according to input format
            $date = DateTime::createFromFormat($inputFormat, $value);

            if ($date === false) {
                return $value;
            }

            // If convertToUtc is true, treat the input date as being in site timezone and convert to UTC
            if ($convertToUtc) {
                // Convert from site timezone to UTC using WordPress function
                // Format the date as Y-m-d H:i:s for get_gmt_from_date
                // get_gmt_from_date expects the date string to be in site timezone
                $dateString = $date->format('Y-m-d H:i:s');
                $timestamp = (int)get_gmt_from_date($dateString, 'U');
            } else {
                $timestamp = $date->getTimestamp();
            }

            // If output format is 'U', return the timestamp directly
            if ($outputFormat === 'U') {
                return (string)$timestamp;
            }

            // Create DateTime object from timestamp
            $resultDate = DateTime::createFromFormat('U', (string)$timestamp);

            if ($convertToUtc) {
                $resultDate->setTimezone(new DateTimeZone('UTC'));
            }

            // Apply offset if specified
            if ($parameters['OFFSET'] ?? false) {
                $newTimestamp = $this->dateTimeHandler->getCalculatedTimeWithOffset($resultDate->getTimestamp(), $parameters['OFFSET']);
                $resultDate = DateTime::createFromFormat('U', (string)$newTimestamp);

                if ($convertToUtc) {
                    $resultDate->setTimezone(new DateTimeZone('UTC'));
                }
            }

            return $resultDate->format($outputFormat);
        } catch (Throwable $e) {
            return $value;
        }
    }
}
