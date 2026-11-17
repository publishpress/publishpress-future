---
title: "Technical Details for Scheduled Action Logs"
order: 16
doc_category: future
section: Publishpress Future Logs
---

# Technical Details for Scheduled Action Logs

Table of Contents

- [Deleting Future Action Logs](#h-deleting-future-action-logs)
- [Where are the logs stored in the database?](#h-where-are-the-logs-stored-in-the-database)
- [Programmatically Deleting Future Action Logs](#h-programmatically-deleting-future-action-logs)

To process large queues of tasks in WordPress, Future Action uses Action Scheduler, which is a scalable job queue for background processing. The same library is being used by WooCommerce, the popular eCommerce plugin.

You will find the logs for [PublishPress Future](https://publishpress.com/links/future) by clicking the “Schedule Actions” button at the top of the “Action Workflows” or “Future Actions” screen.

## Deleting Future Action Logs [#](#h-deleting-future-action-logs)

[PublishPress Future](https://publishpress.com/links/future) will retain “completed” log items for 30 days. You can always delete the logs manually by selecting the logs and using the “Bulk actions” feature in this screenshot:

![Deleting Future Action Logs](https://docs-media.publishpress.com/publishpress-future/delete-future-actions-logs-manually-e4f09ca276.webp)

## Where are the logs stored in the database? [#](#h-where-are-the-logs-stored-in-the-database)

The logs for Future Action are stored in two tables: *wp_actionscheduler_logs* and *wp_actionscheduler_actions*.

You can safely delete these logs directly from the database using SQL commands. For instance:

```
DELETE FROM `wp_actionscheduler_actions` WHERE `status` = 'canceled'
```

```
DELETE FROM `wp_actionscheduler_actions` WHERE `status` = 'failed'
```

```
DELETE FROM `wp_actionscheduler_actions` WHERE `status` = 'complete'
```

This will delete logs that have the status canceled, failed, or complete from the *wp_actionscheduler_actions* table.

Make sure to adjust the table prefix. In the above example, we use “wp_” which are the most common one.

![table prefix](https://docs-media.publishpress.com/publishpress-future/image-cdc514dea0.webp)

Since the statuses of ‘failed’, ‘canceled’, or ‘complete’ are already in the past, it is safe to delete them from the wp_actionscheduler_actions table.

## Programmatically Deleting Future Action Logs [#](#h-programmatically-deleting-future-action-logs)

PublishPress Future will retain “completed” log items for 30 days. However, if you want to shorten this time, you can use the following filter. You could apply it on your function.php or using some code snippet plugin.

This code below will change the retention time to one week. 604800 is the value for one week in seconds. If you want to delete the logs every three days, you can change it to 259200.

```
add_filter( 'action_scheduler_retention_period', 'ppf_delete_logs' );
/**
 * Change Action Scheduler default purge to 1 week
 */
function ppf_delete_logs() {
 return 604800;
}
```
