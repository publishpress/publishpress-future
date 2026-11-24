<?php

namespace PublishPress\Future\Framework\System;

\defined('ABSPATH') || exit;

interface DateTimeHandlerInterface
{
    public function getTimezoneString(): string;

    public function applySiteTimezoneToUTCOffset(string $offset): string;

    public function extractTimeFromOffset(string $offset, $convertTo24h = false): string;

    public function getCurrentTime($gmt = false): int;

    public function getCalculatedTimeWithOffset(int $currentTime, string $offset): int;

    public function formatTimestamp(int $time): string;

    public function getDateFormat(): string;

    public function getTimeFormat(): string;

    public function getDateTimeFormat(): string;
}
