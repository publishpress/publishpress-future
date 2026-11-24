<?php

namespace Tests\Modules\Workflows\Domain\Engine\RuntimeVariablesHelpers;

use PublishPress\Future\Framework\System\DateTimeHandlerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\ContextProcessors\DateProcessor;
use lucatume\WPBrowser\TestCase\WPTestCase;

class DateProcessorTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var DateTimeHandlerInterface
     */
    private $dateTimeHandler;

    public function setUp(): void
    {
        parent::setUp();

        $this->dateTimeHandler = $this->createMock(DateTimeHandlerInterface::class);
        $this->dateTimeHandler->method('getDateTimeFormat')->willReturn('F j, Y');
        $this->dateTimeHandler->method('getTimeFormat')->willReturn('g:i a');
        $this->dateTimeHandler->method('getCalculatedTimeWithOffset')
            ->willReturnCallback(function ($timestamp, $offset) {
                if ($offset === '+1 day') {
                    return $timestamp + 86400;
                }

                if ($offset === '-1 hour') {
                    return $timestamp - 3600;
                }

                return $timestamp;
            });
    }

    public function tearDown(): void
    {
        // Reset timezone to UTC after tests
        $this->setTimezone('UTC', '0');
        parent::tearDown();
    }

    private function createProcessor(): DateProcessor
    {
        return new DateProcessor($this->dateTimeHandler);
    }

    private function setTimezone(string $timezoneString, string $timezoneOffset): void
    {
        update_option('timezone_string', $timezoneString);
        update_option('gmt_offset', $timezoneOffset);
    }

    public function testGetType(): void
    {
        $processor = $this->createProcessor();
        $this->assertEquals('date', $processor->getType());
    }

    public function testProcessWithDefaultFormats(): void
    {
        $processor = $this->createProcessor();

        // Test with a valid date using default formats
        $result = $processor->process('2023-05-15 14:30:00', []);

        // The expected format is the combination of getDateTimeFormat and getTimeFormat
        $this->assertEquals('May 15, 2023 2:30 pm', $result);
    }

    public function testProcessWithCustomInputFormat(): void
    {
        $processor = $this->createProcessor();

        // Test with a valid date using custom input format
        $result = $processor->process('15/05/2023 14:30', [
            'input' => 'd/m/Y H:i'
        ]);

        $this->assertEquals('May 15, 2023 2:30 pm', $result);
    }

    public function testProcessWithCustomOutputFormat(): void
    {
        $processor = $this->createProcessor();

        // Test with a valid date using custom output format
        $result = $processor->process('2023-05-15 14:30:00', [
            'output' => 'Y-m-d H:i'
        ]);

        $this->assertEquals('2023-05-15 14:30', $result);
    }

    public function testProcessWithBothCustomFormats(): void
    {
        $processor = $this->createProcessor();

        // Test with a valid date using both custom input and output formats
        $result = $processor->process('15/05/2023 14:30', [
            'input' => 'd/m/Y H:i',
            'output' => 'Y-m-d H:i'
        ]);

        $this->assertEquals('2023-05-15 14:30', $result);
    }

    public function testProcessWithInvalidDate(): void
    {
        $processor = $this->createProcessor();

        // Test with an invalid date
        $invalidDate = 'not-a-date';
        $result = $processor->process($invalidDate, []);

        // Should return the original value when date is invalid
        $this->assertEquals($invalidDate, $result);
    }

    public function testProcessWithPositiveOffset(): void
    {
        $processor = $this->createProcessor();

        // Test with a positive offset (adding time)
        $result = $processor->process('2023-05-15 14:30:00', [
            'input' => 'Y-m-d H:i:s',
            'output' => 'Y-m-d H:i:s',
            'offset' => '+1 day'
        ]);

        // Should be one day later
        $this->assertEquals('2023-05-16 14:30:00', $result);
    }

    public function testProcessWithNegativeOffset(): void
    {
        $processor = $this->createProcessor();

        // Test with a negative offset (subtracting time)
        $result = $processor->process('2023-05-15 14:30:00', [
            'offset' => '-1 hour',
            'output' => 'Y-m-d H:i:s'
        ]);

        // Should be one hour earlier
        $this->assertEquals('2023-05-15 13:30:00', $result);
    }

    public function testProcessWithConvertToUtc(): void
    {
        // Set a predefined timezone offset (UTC+4) to ensure predictable conversion
        // Input: 2023-05-15 14:30:00 (in UTC+4) should convert to 2023-05-15 10:30:00 (UTC)
        $this->setTimezone('UTC+4', '+4');

        $processor = $this->createProcessor();

        // Test with convert to UTC
        $result = $processor->process('2023-05-15 14:30:00', [
            'convertToUtc' => true,
            'output' => 'Y-m-d H:i:s'
        ]);

        // Should be in UTC (14:30 UTC+4 = 10:30 UTC)
        $this->assertEquals('2023-05-15 10:30:00', $result);
    }
}
