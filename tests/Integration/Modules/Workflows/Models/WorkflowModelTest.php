<?php

/**
 * Integration tests for the workflow model.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace Tests\Modules\Workflows\Models;

use DateTimeImmutable;
use DateTimeZone;
use lucatume\WPBrowser\TestCase\WPTestCase;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Module;
use WP_Post;

class WorkflowModelTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var mixed
     */
    private $originalTimezoneString;

    /**
     * @var mixed
     */
    private $originalGmtOffset;

    /**
     * Set up the test timezone.
     *
     * @since 4.10.5
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->originalTimezoneString = get_option('timezone_string', false);
        $this->originalGmtOffset = get_option('gmt_offset', false);

        update_option('timezone_string', 'Europe/Berlin');
        update_option('gmt_offset', 1);
    }

    /**
     * Restore the original timezone settings.
     *
     * @since 4.10.5
     */
    public function tearDown(): void
    {
        $this->restoreOption('timezone_string', $this->originalTimezoneString);
        $this->restoreOption('gmt_offset', $this->originalGmtOffset);

        parent::tearDown();
    }

    /**
     * Ensure republishing stores current, correctly paired local and GMT dates.
     *
     * @since 4.10.5
     */
    public function testShouldRepublishWorkflowWithPairedLocalAndGmtDates(): void
    {
        $workflowId = wp_insert_post([
            'post_title' => 'Workflow republish date regression',
            'post_content' => '',
            'post_status' => WorkflowModel::STATUS_DISABLED,
            'post_type' => Module::POST_TYPE_WORKFLOW,
        ]);

        $this->assertGreaterThan(0, $workflowId);

        $workflow = new WorkflowModel();
        $this->assertTrue($workflow->load($workflowId));

        $workflow->publish();
        $workflow->unpublish();

        $beforeRepublish = time();
        $workflow->publish();
        $afterRepublish = time();

        clean_post_cache($workflowId);

        $reloadedWorkflow = new WorkflowModel();
        $this->assertTrue($reloadedWorkflow->load($workflowId));

        $reloadedPost = get_post($workflowId);
        $this->assertInstanceOf(WP_Post::class, $reloadedPost);
        $this->assertSame(WorkflowModel::STATUS_ENABLED, $reloadedPost->post_status);

        $localPostDate = new DateTimeImmutable($reloadedPost->post_date, wp_timezone());
        $gmtPostDate = new DateTimeImmutable($reloadedPost->post_date_gmt, new DateTimeZone('UTC'));
        $localTimestamp = $localPostDate->getTimestamp();
        $gmtTimestamp = $gmtPostDate->getTimestamp();

        $this->assertGreaterThanOrEqual($beforeRepublish, $localTimestamp);
        $this->assertLessThanOrEqual($afterRepublish, $localTimestamp);
        $this->assertGreaterThanOrEqual($beforeRepublish, $gmtTimestamp);
        $this->assertLessThanOrEqual($afterRepublish, $gmtTimestamp);
        $this->assertLessThanOrEqual(1, abs($localTimestamp - $gmtTimestamp));
        $this->assertNotSame($reloadedPost->post_date, $reloadedPost->post_date_gmt);
    }

    /**
     * Restore an option or remove it when it did not previously exist.
     *
     * @since 4.10.5
     *
     * @param string $optionName Option name.
     * @param mixed  $value      Original option value.
     */
    private function restoreOption(string $optionName, $value): void
    {
        if (false === $value) {
            delete_option($optionName);

            return;
        }

        update_option($optionName, $value);
    }
}
