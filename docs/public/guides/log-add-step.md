---
title: "Send to Debug Log"
order: 20
doc_category: future
section: "Action Workflows: Actions"
---

# Send to Debug Log

The “Send to debug log” step is helpful for debugging workflows. This step can transmit the workflow’s data to the [debug log](https://publishpress.com/knowledge-base/debug-logs-in-publishpress-future/).

This step can be added anywhere in your workflow. In this example, the “Send to debug log” step is in the middle of the workflow, but it could be added in any position in this workflow.

![Send to debug log](https://docs-media.publishpress.com/publishpress-future/send-debug-log-b1ef6cc984.webp)

You can use the right sidebar to decide which data is sent to the debug log. There are two options here:

- **Message**: The data that will be sent to the debug log.
- **Level**: Choose to send debug data, or error reporting.

![Send to debug log message](https://docs-media.publishpress.com/publishpress-future/send-to-debug-log-message-56019b386b.webp)

To customize the message sent to the debug log, click the {} brackets icon next to the “Message” field. You can customize the data using variables. In the example below, we added the title of the post, and the time when the trigger was activated.

![](https://docs-media.publishpress.com/publishpress-future/activating-time-c332b29065.webp)

After this workflow runs, this data will now appear in the debug log.

![Debug log report](https://docs-media.publishpress.com/publishpress-future/debug-report-b65e83cb9b.webp)
