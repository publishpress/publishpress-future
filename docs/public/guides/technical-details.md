---
title: "Technical Details for PublishPress Future"
order: 37
doc_category: future
---

# Technical Details for PublishPress Future

Table of Contents

- [Adding an expiry date to posts](#adding-an-expiry-date-to-posts)
- [About the PublishPress Future cron jobs](#about-the-publishpress-future-cron-jobs)[This applies to both features that schedule things for the future:](#2-toc-title)[How the scheduling actually works](#3-toc-title)[What happens when the cron job runs](#4-toc-title)[Who triggers the queue: WordPress vs. your server](#5-toc-title)[Why the default mode is a problem for expiration dates](#6-toc-title)[Setting up a reliable cron trigger](#7-toc-title)[Step 1: Disable WordPress's own trigger](#8-toc-title)[Step 2: Add a real cron job on the server](#9-toc-title)[Option A — curl hitting wp-cron.php (simplest, no shell access to WP-CLI needed)](#10-toc-title)[Option B — WP-CLI (recommended if you have shell access)](#11-toc-title)[Option C — Third-party uptime/cron pinger](#12-toc-title)[How often should the cron run?](#13-toc-title)[Verifying it's working](#14-toc-title)

In [another documentation article](https://publishpress.com/knowledge-base/scheduling-cron-jobs/), we explained that the scheduling is handled by a cron job and the `_ppfuture_actions_args` table.

This post will explain more about that process and how it works.

## Adding an expiry date to posts [#](#adding-an-expiry-date-to-posts)

On every post save, the [PublishPress Future](https://publishpress.com/links/future) plugin will check if a Future action is enabled in the post.

- If the feature is enabled, the plugin stores the data set in the Future Action metabox in the _ppfuture_actions_args table and schedules a cron task.
- If the feature is not enabled, it will ignore any cron task for it.

## About the PublishPress Future cron jobs [#](#about-the-publishpress-future-cron-jobs)

### This applies to **both** features that schedule things for the future: [#](#2-toc-title)

- **Future Actions** — a single scheduled change to a post (expire, trash, change status, etc.)
- **Action Workflows → Scheduled Action / Schedule delay step** — any workflow step that runs later instead of immediately

Both are handled by the same underlying job queue, so everything below applies equally to a post expiration and to a delayed workflow step.

### How the scheduling actually works [#](#3-toc-title)

Since [PublishPress Future](https://publishpress.com/links/future) 3.0, scheduled jobs are **not** plain WordPress cron events. They’re queued in **Action Scheduler** — the same background-job library WooCommerce uses for orders, subscriptions, and webhooks. Each scheduled item is stored as a database row (in `wp_actionscheduler_actions`) with a hook name, arguments, and a due date/time, rather than living only in WordPress’s `cron` option like a classic `wp_schedule_event()` call.

Why this matters for you: Action Scheduler is more reliable than plain WP-Cron (survives PHP crashes, gives you a visible admin log at **Future Actions / Action Workflows → Scheduled Actions**, retries, batches). But it still needs *something* to trigger it to check the queue — and by default, that something is still WP-Cron underneath.

### What happens when the cron job runs [#](#4-toc-title)

When the scheduled task runs, it calls the `publishpressfuture_run_workflow` action, passing the post ID stored as the task’s argument.

The task then reads the `ppfuture_actions_args` table for that post to look up the rest of the settings for the scheduled change — such as which [action type](https://publishpress.com/knowledge-base/ways-to-expire-posts/) was selected (expire, trash, change status, etc.).

Finally, it carries out the future action according to what was configured for that post.

> Note: this supersedes the older `publishpressfuture_expire` hook, which is deprecated. If you’re hooking into this programmatically or writing `--hooks=` filters for debugging, use `publishpressfuture_run_workflow`.

### Who triggers the queue: WordPress vs. your server [#](#5-toc-title)

WordPress does **not** have a real, always-on cron daemon by default. It uses “WP-Cron,” triggered opportunistically, controlled by the `DISABLE_WP_CRON` constant in `wp-config.php`. Action Scheduler hooks into this same system (via the `action_scheduler_run_schedule` cron event) unless you trigger it another way:

| Constant value | What happens |
| --- | --- |
| Not defined, or `define('DISABLE_WP_CRON', false);` | **Default WordPress behavior.** WP-Cron runs on page/visit load. On every front-end request, WordPress checks if any scheduled events are due and runs them inline before serving the page. |
| `define('DISABLE_WP_CRON', true);` | **WordPress’s own trigger is disabled.** Nothing runs cron automatically anymore. Something else — a real server cron job — must trigger it. |

### Why the default mode is a problem for expiration dates [#](#6-toc-title)

With the default (`DISABLE_WP_CRON` not set), cron only fires when a visitor loads a page:

- **Low-traffic sites:** if a post is scheduled to expire at 2:00 AM and nobody visits the site until 9:00 AM, the post doesn’t expire until 9:00 AM. The scheduled time is a *minimum*, not a guarantee.
- **High-traffic sites:** WP-Cron runs on nearly every page load, which adds unnecessary overhead — checking the cron table dozens of times a minute when once every few minutes would do.

Either way, relying on visit-triggered cron is imprecise. For time-sensitive actions — post expiration or a workflow’s scheduled step — we recommend disabling WordPress’s built-in trigger and using a real, server-level cron job instead.

### Setting up a reliable cron trigger [#](#7-toc-title)

#### Step 1: Disable WordPress’s own trigger [#](#8-toc-title)

In `wp-config.php`, add:

```
define('DISABLE_WP_CRON', true);
```

Place this above the line `/* That's all, stop editing! */`.

#### Step 2: Add a real cron job on the server [#](#9-toc-title)

Once WordPress’s trigger is off, **you must** add a system-level cron job, or scheduled events will never run. Choose one of the methods below.

##### Option A — `curl` hitting `wp-cron.php` (simplest, no shell access to WP-CLI needed) [#](#10-toc-title)

```
*/5 * * * * curl -s --max-time 30 "https://example.com/wp-cron.php?doing_wp_cron" > /dev/null 2>&1
```

- Runs every 5 minutes.
- `-s` silences output, `--max-time 30` prevents a hung request from piling up.
- Discards output; add logging (`>> /var/log/wp-cron.log 2>&1`) if you want a trail.

`wget` equivalent, if `curl` isn’t available:

```
*/5 * * * * wget -q -O /dev/null "https://example.com/wp-cron.php?doing_wp_cron" >/dev/null 2>&1
```

##### Option B — WP-CLI (recommended if you have shell access) [#](#11-toc-title)

Two WP-CLI commands both work here — use whichever fits your setup:

bash

```
# Most direct: runs the Action Scheduler queue synchronously, right now.
*/5 * * * * cd /path/to/wordpress && wp action-scheduler run --quiet

# Also works: fires WordPress's own due cron events, which includes the
# action_scheduler_run_schedule event that kicks off the same queue.
*/5 * * * * cd /path/to/wordpress && wp cron event run --due-now --quiet
```

Why WP-CLI is generally the better option than curl/wget:

- Runs in the WordPress bootstrap directly — no HTTP round trip, no dependency on the site being publicly reachable at `wp-cron.php`.
- Doesn’t expose `wp-cron.php` to the outside world at all, which removes a low-value but real attack surface (some sites get hit with automated DoS-style floods of repeated `wp-cron.php` requests).
- `wp action-scheduler run` skips the WP-Cron event layer entirely and drains the job queue directly — useful if you want scheduled posts/workflow steps processed without waiting on WordPress’s own cron event timing.
- You can scope it to just this plugin’s jobs while debugging, e.g. `wp action-scheduler run --hooks=publishpressfuture_run_workflow`.
- `--quiet` suppresses output for clean cron logs; drop it while testing.

If `wp` isn’t in your `PATH` inside cron’s minimal environment, use the full path:

```
*/5 * * * * cd /path/to/wordpress && /usr/local/bin/wp action-scheduler run --quiet
```

#### Option C — Third-party uptime/cron pinger [#](#12-toc-title)

If you don’t have server/crontab access (common on some managed hosts), a service like cron-job.org, EasyCron, or your host’s built-in “cron jobs” panel can hit the same URL as Option A on a schedule. Functionally identical to Option A, just externally triggered instead of from the server itself.

### How often should the cron run? [#](#13-toc-title)

Every 5 minutes is a reasonable default — it means a scheduled action (expiration or workflow step) fires within 5 minutes of its due time, worst case. Tighten to every 1 minute for time-critical use cases; there’s no real cost to running these commands that often since they’re a no-op when nothing is due.

### Verifying it’s working [#](#14-toc-title)

**In WordPress admin:** go to **Future Actions** or **Action Workflows → Scheduled Actions**. Confirm the item shows status “Scheduled” with the correct arguments and scheduled date. After it runs, it should flip to “Complete” — click “View log” for a full execution trace.

**From the command line**, list what Action Scheduler has pending:

bash

```
wp action-scheduler list --status=pending
```

**Site Health check:** WordPress admin → Tools → Site Health → Passed tests → look for “Scheduled events are running.” If you see a recommended improvement saying “A scheduled event is late,” check whether it’s referencing `action_scheduler_run_schedule` — if so, that’s your trigger not firing on time, which points back to the crontab entry above.

After the scheduled time passes, re-check — pending items should move to complete, and the post/workflow step should reflect the expected change.
