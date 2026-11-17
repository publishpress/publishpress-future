---
title: "How Scheduling Works in PublishPress Future"
order: 34
doc_category: future
section: "Future Actions: Technical Details"
---

# How Scheduling Works in PublishPress Future

Table of Contents

- [Check that WP Cron is Active](#h-check-that-wp-cron-is-active)
- [Technical details for PublishPress Future cron jobs](#h-technical-details-for-publishpress-future-cron-jobs)

The scheduling in [PublishPress Future](https://publishpress.com/links/future) is handled by cron jobs.

This plugin does require that WP Cron is setup and functional on your webhost. Some hosts do not support this, so please check and confirm if you run into issues using the plugin.

## Check that WP Cron is Active [#](#h-check-that-wp-cron-is-active)

Go to “Future” in your WordPress admin menu and then click the “Diagnostics and Tools” tab. This table will show you whether WP Cron is active on your site:

![Diagnostics and Tools tab in PublishPress Future](https://docs-media.publishpress.com/publishpress-future/wp-cron-check-d84a5564b7.webp)

## Technical details for PublishPress Future cron jobs [#](#h-technical-details-for-publishpress-future-cron-jobs)

If you want to see expiry details in the database, WordPress stores cron jobs in the `_`ppfuture_actions_args table.

![Database table entry for PublishPress Future](https://docs-media.publishpress.com/publishpress-future/action-table-b94535ff4a.webp)

*Cron Table*

This code below shows a typical cron job entry from [PublishPress Future](https://publishpress.com/links/future):

```
{"expireType":"draft","category":[],"categoryTaxonomy":"","enabled":false,"date":0,"id":24}
```

Some of the specific details are also held in the `_postmeta` table. In the image below, the “_expiration-date-type” is set to “Draft” so this post will move to the “Draft” status.

The _expirationdate is set using Unix time which is an integer number that counts date and time in seconds since 1970-01-01. There are convertors available for Unix time including [Epoch Converter](https://www.epochconverter.com/).

![PublishPress Future database](https://docs-media.publishpress.com/publishpress-future/action-post-meta-e9814f7553.webp)

*Expiration Date Postmeta*
