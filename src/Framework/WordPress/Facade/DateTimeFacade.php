<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Facade;

use DateTimeZone;

\defined('ABSPATH') || exit;

class DateTimeFacade
{
    /**
     * @var OptionsFacade
     */
    private $options;

    public function __construct(OptionsFacade $options)
    {
        $this->options = $options;
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone()
    {
        return \wp_timezone();
    }

    /**
     * @param $format
     * @param $timestamp
     * @param $timezone
     * @return string|null
     */
    public function getLocalizedDate($format, $timestamp = null, $timezone = null)
    {
        $date = \wp_date($format, $timestamp, $timezone);

        return false === $date ? null : $date;
    }

    /**
     * @param string $format
     * @param int|null $timestamp
     * @return string|null
     */
    public function getWpDate($format, $timestamp, $defaultFormat = '')
    {
        if (empty($format)) {
            $format = $defaultFormat;
        }

        $gmtTime = gmdate('Y-m-d H:i:s', $timestamp);
        $timezone = $this->getTimezone();
        $datetime = date_create($gmtTime, new DateTimeZone('+0:00'));

        return $this->getLocalizedDate($format, $datetime->getTimestamp(), $timezone);
    }

    public function getDefaultDateFormat(): string
    {
        return $this->options->getOption('date_format');
    }

    public function getDefaultTimeFormat(): string
    {
        return $this->options->getOption('time_format');
    }

    public function getDefaultDateTimeFormat(): string
    {
        return $this->getDefaultDateFormat() . ' ' . $this->getDefaultTimeFormat();
    }
}
