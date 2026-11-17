---
title: "Technical Details for Metadata Scheduling (Pro)"
order: 25
doc_category: future
---

# Technical Details for Metadata Scheduling (Pro)

Table of Contents

- [Activation and Triggering](#h-activation-and-triggering)
- [Conditions for Scheduling](#h-conditions-for-scheduling)
- [Cleanup](#h-cleanup)

This feature requires the Pro version of the PublishPress Future plugin.

In [PublishPress Future](https://publishpress.com/links/future) Pro it is possible to schedule changes to your posts based on metadata. For example, you can create a date field in the Advanced Custom Fields plugin, and use that to control the date for Future Actions.

By default, Future does not utilize post metadata for scheduling actions. Instead, it employs the dedicated custom table `<prefix>ppfuture_actions_args`. This table is connected to the Action Scheduler library for managing scheduling tasks.

With [PublishPress Future](https://publishpress.com/links/future) Pro, it is possible to bypass the `ppfuture_actions_args` table and use metadata to schedule Future Actions.

## Activation and Triggering [#](#h-activation-and-triggering)

The Metadata Scheduling feature listens to the `save_post` action and specific metadata to configure the post’s action scheduling.

An alternative way to trigger the processing of metadata for scheduling is using the action `publishpressfuturepro_process_metadata`, passing the post ID as the first argument. Optionally, you can also pass instance of `WP_Post` as the second argument.

## Conditions for Scheduling [#](#h-conditions-for-scheduling)

The plugin checks if there is a timestamp in the metadata `_expiration-date` and if `_expiration-date-status` is equal to “saved” or “1”. If so, a hash is calculated based on the future action metadata and compared with the value found in the metadata `pp_future_metadata_hash`. If different, or not existent, the scheduling continues. A different hash means the scheduling data has changed or the action has been already scheduled with different settings.

If the post already has a future action scheduled it cancels it and re-schedule with the new settings.

## Cleanup [#](#h-cleanup)

After a future action is unscheduled or executed, the related metadata, including the hash, is deleted.
