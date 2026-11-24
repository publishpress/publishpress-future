<?php

namespace Tests\Framework\System;

use Codeception\Attribute\Examples;
use Codeception\Example;
use Exception;
use PublishPress\Future\Framework\System\DateTimeHandler;

class DateTimeHandlerTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    public function testGetCurrentTime(): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $result = $dateTimeHandler->getCurrentTime();

        $this->assertEquals(time(), $result);
        $this->assertIsInt($result);
    }

    #[Examples(['UTC', '0', 'UTC'])]
    #[Examples(['Europe/Madrid', '+1', 'Europe/Madrid'])]
    #[Examples(['America/New_York', '-4', 'America/New_York'])]
    #[Examples(['America/Sao_Paulo', '-3', 'America/Sao_Paulo'])]
    #[Examples(['UTC-3', '-3', '-03:00'])]
    #[Examples(['UTC-4', '-4', '-04:00'])]
    #[Examples(['UTC+5', '+5', '+05:00'])]
    public function testGetTimezoneString(array $example): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $this->setTimezone($example[0], $example[1]);

        $this->assertEquals($example[2], $dateTimeHandler->getTimezoneString());
    }

    #[Examples(['2024-10-29 16:46:29', '+1 hour', '2024-10-29 17:46:29'])]
    #[Examples(['2024-10-29 16:46:29', '+1 minute', '2024-10-29 16:47:29'])]
    #[Examples(['2024-10-29 16:46:29', '+1 second', '2024-10-29 16:46:30'])]
    #[Examples(['2024-10-29 16:46:29', '-1 hour', '2024-10-29 15:46:29'])]
    #[Examples(['2024-10-29 16:46:29', '+3 days 16:00', '2024-11-01 16:00:00'])]
    #[Examples(['2024-10-29 16:46:29', '+3 days 4:00 pm', '2024-11-01 16:00:00'])]
    #[Examples(['2024-10-29 16:46:29', '+3 days 11:00 am', '2024-11-01 11:00:00'])]
    #[Examples(['2024-10-29 16:46:29', '+3 days 4:00 PM', '2024-11-01 16:00:00'])]
    #[Examples(['2024-10-29 16:46:29', '+3 days 11:00 AM', '2024-11-01 11:00:00'])]
    public function testGetCalculatedTimeWithOffset(array $example): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $baseTime = strtotime($example[0]);
        $offset = $example[1];
        $expectedTime = strtotime($example[2]);

        $this->assertEquals(
            $expectedTime,
            $dateTimeHandler->getCalculatedTimeWithOffset($baseTime, $offset)
        );
    }

    public function testGetCalculatedTimeWithOffsetPassingInvalidOffset(): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $this->expectException(Exception::class);

        $dateTimeHandler->getCalculatedTimeWithOffset(time(), 'invalid offset');
    }

    /**
     * Test timezone offset conversion with fixed offsets to avoid DST issues
     *
     * Using fixed timezone offsets instead of named timezones ensures tests
     * won't fail due to Daylight Saving Time changes throughout the year.
     */
    #[Examples(['+1 month 13:00', '-5', 'UTC-5', '+1 month 18:00'])]
    #[Examples(['+1 month 17:00', '0', 'UTC', '+1 month 17:00'])]
    #[Examples(['+1 month 18:00', '+1', 'UTC+1', '+1 month 17:00'])]
    #[Examples(['+1 month 14:00', '-3', 'UTC-3', '+1 month 17:00'])]
    #[Examples(['+1 month 13:00', '-4', 'UTC-4', '+1 month 17:00'])]
    #[Examples(['+1 month 22:00', '+5', 'UTC+5', '+1 month 17:00'])]
    public function testApplyTimezoneToOffset(array $example): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $offset = $example[0];
        $timezoneOffset = $example[1];
        $timezoneString = $example[2];
        $expectedOffset = $example[3];

        // Mock the timezone setting
        $this->setTimezone($timezoneString, $timezoneOffset);

        try {
            $actualOffset = $dateTimeHandler->applySiteTimezoneToUTCOffset($offset);
            $this->assertEquals(
                $expectedOffset,
                $actualOffset,
                'Failed to apply timezone to offset: ' . $offset
            );
        } finally {
            // Always reset timezone to UTC after test
            $this->setTimezone('UTC', '0');
        }
    }

    public function testGetDateFormat(): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $this->assertEquals('F j, Y', $dateTimeHandler->getDateFormat());
    }

    public function testGetTimeFormat(): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $this->assertEquals('g:i a', $dateTimeHandler->getTimeFormat());
    }

    public function testGetDateTimeFormat(): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $this->assertEquals('F j, Y g:i a', $dateTimeHandler->getDateTimeFormat());
    }

    #[Examples([1730234197, '0', 'UTC', 'U', '1730234197'])]
    #[Examples([1730234197, '0', 'UTC', 'F j, Y', 'October 29, 2024'])]
    #[Examples([1730234197, '+1', 'Europe/Madrid', 'U', '1730234197'])]
    #[Examples([1730234197, '+1', 'Europe/Madrid', 'F j, Y', 'October 29, 2024'])]
    #[Examples([1730234197, '+1', 'Europe/Madrid', 'Y-m-d H:i:s', '2024-10-29 21:36:37'])]
    #[Examples([1730234197, '-4', 'America/New_York', 'Y-m-d H:i:s', '2024-10-29 16:36:37'])]
    #[Examples([1730234197, '-3', 'America/Sao_Paulo', 'Y-m-d H:i:s', '2024-10-29 17:36:37'])]
    #[Examples([1730234197, '-3', 'UTC-3', 'Y-m-d H:i:s', '2024-10-29 17:36:37'])]
    #[Examples([1730234197, '-4', 'UTC-4', 'Y-m-d H:i:s', '2024-10-29 16:36:37'])]
    #[Examples([1730234197, '+5', 'UTC+5', 'Y-m-d H:i:s', '2024-10-30 01:36:37'])]
    public function testFormatTimestamp(array $example): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $timestamp = $example[0];
        $timezoneOffset = $example[1];
        $timezoneString = $example[2];
        $format = $example[3];
        $expectedTime = $example[4];

        if ($timezoneString !== 'UTC') {
            $this->setTimezone($timezoneString, $timezoneOffset);
            $result = $dateTimeHandler->formatTimestamp($timestamp, $format);
        } else {
            $result = $dateTimeHandler->formatTimestamp($timestamp, $format, true);
        }

        $this->assertEquals($expectedTime, $result);

        $this->setTimezone('UTC', '0');
    }

    #[Examples(['+1 month 17:00', '17:00', true])]
    #[Examples(['+1 month 5:00 pm', '17:00', true])]
    #[Examples(['+1 month 5:00 am', '5:00', true])]
    #[Examples(['+1 month 5:00 PM', '17:00', true])]
    #[Examples(['+1 month 5:00 AM', '5:00', true])]
    #[Examples(['+2 days 10:00 am', '10:00', true])]
    #[Examples(['+2 days 10:00 pm', '22:00', true])]
    #[Examples(['+2 days 10:00 AM', '10:00', true])]
    #[Examples(['+2 days 10:00 PM', '22:00', true])]
    #[Examples(['+1 month 17:00', '17:00', false])]
    #[Examples(['+1 month 5:00 am', '5:00 am', false])]
    #[Examples(['+1 month 5:00 pm', '5:00 pm', false])]
    #[Examples(['+1 month 5:00 AM', '5:00 am', false])]
    #[Examples(['+1 month 5:00 PM', '5:00 pm', false])]
    public function testExtractTimeFromOffset(array $example): void
    {
        $dateTimeHandler = new DateTimeHandler();

        $offset = $example[0];
        $expectedTime = $example[1];
        $convertTo24h = $example[2];

        $this->assertEquals(
            $expectedTime,
            $dateTimeHandler->extractTimeFromOffset($offset, $convertTo24h)
        );
    }

    private function setTimezone(string $timezoneString, string $timezoneOffset): void
    {
        update_option('timezone_string', $timezoneString);
        update_option('gmt_offset', $timezoneOffset);
    }
}
