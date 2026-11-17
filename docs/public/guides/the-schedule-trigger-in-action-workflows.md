---
title: "On Schedule (Pro)"
order: 39
doc_category: future
---

# On Schedule (Pro)

Table of Contents

- [When to Run](#0-toc-title)
- [Repeating Action](#1-toc-title)

This feature requires the Pro version of the PublishPress Future plugin.

The “On schedule” trigger is available in the Pro version of [PublishPress Future](https://publishpress.com/links/future). This allows you to trigger the a workflow at a specific time, or relative to another date. You can also use this trigger to repeat the workflow. [PublishPress Future](https://publishpress.com/links/future) will automatically handle the scheduling: you do not need to create a cron job or make any other changes.

![On schedule trigger](https://docs-media.publishpress.com/publishpress-future/on-schedule-f227c9243c.webp)

The options for this “On schedule” step are available in the right sidebar. There are two options:

- When to run.
- Repeating action.

![On schedule trigger](https://docs-media.publishpress.com/publishpress-future/on-schedule-run-244e7456f5.webp)

## When to Run [#](#0-toc-title)

There are three options for when to run the workflow:

- As soon as possible
- On a specific date
- Relative to a specific date

![When to run](https://docs-media.publishpress.com/publishpress-future/when-to-run-592bfbf7e5.webp)

Both “On a specific date” and “Relative to a specific date” will provide several more options for the source of the data used in the trigger.

- Selected in the calendar
- When the trigger is activated
- When the step is activated
- Custom date source. This option allows you to use [post meta](https://publishpress.com/knowledge-base/future-postmeta-field/) and other WordPress data sources to choose a date.

![When to run](https://docs-media.publishpress.com/publishpress-future/date-source-98ab31b378.webp)

The “Custom date source” option enables you to open a modal window as in the screenshot below. You can use this customize the date that is used to schedule the workflow. Go to “Site” then “Metadata” and you can enter a date source in your WordPress database.

![Custom date source](https://docs-media.publishpress.com/publishpress-future/custom-date-source-52146a3c53.webp)

Let’s see one example of this metadata in action for the “Custom date source” option. In this screenshot, we’re using the popular plugin called “The Events Calendar”. There is a field in the _postmeta table called _EventEndDate. This stores the end date of events that are created with the plugin. We can use this date to trigger the workflow, so that the workflow starts when the event ends.

![The Events Calendar](https://docs-media.publishpress.com/publishpress-future/post-meta-7bb654f9ee.webp)

To use the end date field as a trigger, enter _EventEndDate into the “Metadata” field and click the “Insert” button.

![](https://docs-media.publishpress.com/publishpress-future/end-date-1-8bec1b765d.webp)

## Repeating Action [#](#1-toc-title)

The “Repeating Action” allows you to choose if and when the workflow runs again. There are multiple default options and also a “Custom interval” option. For example, in the screenshot below, this workflow will repeat once every day.

![Workflow schedle](https://docs-media.publishpress.com/publishpress-future/schedule-a150961f8e.webp)
