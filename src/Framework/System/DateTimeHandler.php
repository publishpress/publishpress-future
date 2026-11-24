<?php

namespace PublishPress\Future\Framework\System;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;

\defined('ABSPATH') || exit;

class DateTimeHandler implements DateTimeHandlerInterface
{
    public function applySiteTimezoneToUTCOffset(string $offset): string
    {
        return $this->translateFixedTimeOnOffsetToUTC($offset);
    }

    public function getTimezoneString(): string
    {
        return wp_timezone_string();
    }

    public function getCurrentTime($gmt = false): int
    {
        return $gmt ? gmdate('U') : time();
    }

    // TODO: Check why the offset with fixed date as 21h or higher returns a day before.
    // "+7 days 20:00" returns the seven days at 20h. But "+7 days 21:00" returns the six days at 21h.
    // Funny thing is that if we use "+7 days 20:59:60" it returns the seven days at 21h.
    // Manually apply the fixed time after calculation can solve the issue.
    public function getCalculatedTimeWithOffset(int $currentTime, string $offset): int
    {
        // Validate offset format before processing
        if (empty($offset)) {
            throw new InvalidArgumentException(esc_html__('Empty date time offset.', 'post-expirator'));
        }

        $offset = $this->translateFixedTimeOnOffsetToUTC($offset);

        $calculatedTime = @strtotime($offset, $currentTime);

        if ($calculatedTime === false) {
            throw new InvalidArgumentException(esc_html__('Invalid date time offset', 'post-expirator'));
        }

        return $calculatedTime;
    }

    public function formatTimestamp(int $time, $format = null, $useUTC = false): string
    {
        $date = new DateTime(date('Y-m-d H:i:s', $time));

        if (! $useUTC) {
            $siteTimezone = new DateTimeZone($this->getTimezoneString());
            $date->setTimezone($siteTimezone);
        }

        $format ??= $this->getDateTimeFormat();

        return $date->format($format);
    }

    public function getDateFormat(): string
    {
        return get_option('date_format');
    }

    public function getTimeFormat(): string
    {
        return get_option('time_format');
    }

    public function getDateTimeFormat(): string
    {
        return $this->getDateFormat() . ' ' . $this->getTimeFormat();
    }

    public function extractTimeFromOffset(string $offset, $convertTo24h = false): string
    {
        preg_match('/\d{1,2}:\d{2}(?:\s(?:am|pm|AM|PM))?/', $offset, $matches);

        $time = strtolower($matches[0] ?? '');

        if ($convertTo24h) {
            if (str_contains($time, 'am')) {
                $time = trim(str_replace('am', '', $time));
            }

            if (str_contains($time, 'pm')) {
                $time = trim(str_replace('pm', '', $time));
                $timeParts = explode(':', $time);
                $timeParts[0] = (int) $timeParts[0] + 12;
                $time = implode(':', $timeParts);
            }
        }

        return $time;
    }

    private function convertFixedTimeToUTC(string $fixedTimeInSitesTimezone): string
    {
        $date = new DateTime(
            $fixedTimeInSitesTimezone,
            new DateTimeZone($this->getTimezoneString())
        );
        $date->setTimezone(new DateTimeZone('UTC'));

        return $date->format('H:i');
    }

    private function translateFixedTimeOnOffsetToUTC(string $offset): string
    {
        $fixedTimeInSitesTimezone = $this->extractTimeFromOffset($offset);

        if (! empty($fixedTimeInSitesTimezone)) {
            $fixedTimeInUTC = $this->convertFixedTimeToUTC($fixedTimeInSitesTimezone);

            $offset = str_replace($fixedTimeInSitesTimezone, $fixedTimeInUTC, $offset);
        }

        return $offset;
    }
}
