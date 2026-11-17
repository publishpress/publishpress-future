---
title: "Troubleshooting in PublishPress Future"
order: 41
doc_category: future
section: Future Actions
---

# Troubleshooting in PublishPress Future

Table of Contents

- [Check that scheduled tasks are being created](#h-step-4-are-cron-tasks-being-created)
- [Check the Diagnostic and Tools tab](#h-step-2-have-you-confirmed-wp-cron-is-working-on-your-site)
- [Check caching services and plugins](#h-step-1-have-you-disabled-all-caching-services-and-plugins)
- [Check the Site Health screen](#h-step-3-check-site-health-tests)
- [Test for server issues](#4-toc-title)

If changes that you have scheduled via [PublishPress Future](https://publishpress.com/links/future) are not happening at the correct time, this guide will help. Please follow through all the steps to check that your site is ready to use [PublishPress Future](https://publishpress.com/links/future).

## Check that scheduled tasks are being created [#](#h-step-4-are-cron-tasks-being-created)

PublishPress Future will show whether or not a cron job has been successfully set up for your posts to expire. You can confirm this by going to the “Scheduled Actions” button on the PublishPress Future screens.

![Scheduled Actions button](https://docs-media.publishpress.com/publishpress-future/scheduled-actions-button-a327799e0f.webp)

Check on this screen to make sure that your actions are marked as “Scheduled”, as in this screenshot below. If the action is created, does it have the correct information in the “Arguments” column? Does the action have the correct time and date in the “Scheduled date” column?

![Future scheduled action](https://docs-media.publishpress.com/publishpress-future/future-scheduled-actions-fcda08b77c.webp)

## Check the Diagnostic and Tools tab [#](#h-step-2-have-you-confirmed-wp-cron-is-working-on-your-site)

By default, PublishPress Future relies on WP Cron to run future actions on your content. [This post has technical details on how it works](https://publishpress.com/knowledge-base/technical-details/https://publishpress.com/knowledge-base/technical-details/).

- Go to “Settings” in your WordPress admin menu and then clicking the “Diagnostics and Tools” tab. You will see a green check mark if WP Cron is enabled.
- While you’re on this screen, you can also see if any errors are reported under “Database Schema Check”.

![Diagnostics and Tools tab](https://docs-media.publishpress.com/publishpress-future/diagnostic-tools-6d29a705f2.webp)

Some developers and hosting companies add lines that will disable cron on your site. For example: `define(‘DISABLE_WP_CRON’, true);` If you have that constant defined as true, that means your site won’t trigger cron events when your site is visited. But it still can trigger those events if your server has a cron job properly configured to call `wp-cron.php` or run the CLI command `wp cron event run --due-now`.

## Check caching services and plugins [#](#h-step-1-have-you-disabled-all-caching-services-and-plugins)

Caching plugins and services and the most common cause of problems with PublishPress Future. If posts are not expiring correctly, please test your site with all caching layers disabled.

## Check the Site Health screen [#](#h-step-3-check-site-health-tests)

Go to “Tools” in your WordPress admin menu and click on “Site Health”. Wait a few seconds until all the tests are finished and check if there is any recommended improvements or errors. You can double check clicking on the button “Passed tests” and looking for a test “Scheduled events are running”.

Sometimes you can see a recommended improvement “A scheduled event is late”. Click on it, and check if it is related to the event “action_scheduler_run_queue”. If so, wait a few minutes, than check again.

## Test for server issues [#](#4-toc-title)

- Test PublishPress Future another site on the same server. If you see the same problem, the issue is with your site. If you don’t see the same problem, the issue is with your original site.
- Test PublishPress Future on a clone of your site but on a different server or local host. If you don’t see the same problem, the issue is with your original server.
