---
title: "Available Steps in Action Workflows"
order: 40
doc_category: future
section: Action Workflows
---

# Available Steps in Action Workflows

Table of Contents

- [Available Triggers (Free)](#0-toc-title)
- [Available Triggers (Pro)](#1-toc-title)
- [Available Actions (Free)](#2-toc-title)
- [Available Actions (Pro)](#3-toc-title)

The “Workflows” feature in [PublishPress Future](https://publishpress.com/links/future) Pro allows you to create workflows with several steps. Each workflow requires at least one Trigger and one Action.

## Available Triggers (Free) [#](#0-toc-title)

Triggers are used to start each workflow.

- **Post is saved**: This trigger activates whenever a post is saved, regardless of whether it’s newly created, imported, or updated.
- **Post is updated**: This trigger activates when an existing post is updated.
- **Post is published**: This trigger activates when an existing post is updated.
- **Manually run via checkbox**: This allows users to manually trigger the workflow. It can be enabled via the post editing screen or the “Quick Edit” panel. [Find out more](https://publishpress.com/knowledge-base/manually-enabled-checkbox/).
- ****Manually enabled via** Future Actions box**: This adds the workflow to the default “Future Actions” box so it can be manually triggered by users. [Find out more](https://publishpress.com/knowledge-base/manually-enabled-step/).
- **Terms are added to post**: This trigger activates when specific taxonomy terms are added to a post.

## Available Triggers (Pro) [#](#1-toc-title)

This feature requires the Pro version of the PublishPress Future plugin.

- **Post is scheduled (Pro version)**: This trigger activates when a post is scheduled.
- **Post status changed (Pro version)**: This trigger activates when a post status is changed.
- **Post meta changed (Pro version)**: This trigger activates when a post meta field is changed.
- **Post author changed (Pro version)**: This trigger activates when an existing post author is changed.
- **Manually run via posts row action (Pro)**: This adds a link to the “Posts” screen from where users can trigger the workflow. [Find out more](https://publishpress.com/knowledge-base/manually-enabled-via-post-row-action/).
- **User role is changed (Pro version)**: This trigger activates when a user’s role is changed.
- **On custom action (Pro version)**: This trigger activates upon a custom WordPress action hook. Use it to integrate with other plugins or custom code. [Find out more](https://publishpress.com/knowledge-base/on-custom-action/).
- **On schedule (Pro version)**: This trigger allows to run a workflow at a specific time, or relative to another date. You can also use this trigger to repeat workflows. [Find out more](https://publishpress.com/knowledge-base/the-schedule-trigger-in-action-workflows/).

## Available Actions (Free) [#](#2-toc-title)

Actions are available once a workflow has started to run.

- **Delete post**: This action will completely remove the post from your site.
- **Stick post**: This action will enable the “Stick to the top of the blog” feature for the post.
- **Unstick post**: This action will disable the “Stick to the top of the blog” feature for the post.
- **Add extra terms to post**: This action will keep the current terms and add additional terms that you choose.
- **Replace all terms on post**: This action will remove all the current terms and add additional terms that you choose to the post.
- **Remove terms from post**: This action will remove the current terms that you choose from the post.
- **Move post to status**: This action will remove the current status for a post and add a new status.
- **Deactivate Workflow for Post**: This action enables you to deactivate the “Manually enabled via checkbox” workflow after use.
- **Send email**: This action will send an email that you can customize. [Find out more](https://publishpress.com/knowledge-base/email-step/).
- **Schedule Delay**: This step allows to run the next part of a workflow at a specific time, or relative to another date. You can also use this step to repeat the next part of the workflow. [Find out more](https://publishpress.com/knowledge-base/schedule-step/).
- **Send to debug log**: This step transmits the flow’s data to the debug log. [Find out more](https://publishpress.com/knowledge-base/log-add-step/).

## Available Actions (Pro) [#](#3-toc-title)

This feature requires the Pro version of the PublishPress Future plugin.

- **Add post meta (Pro version)**: This action adds post meta to a post.
- **Delete post meta (Pro version)**: This action deletes post meta from a post.
- **Update post meta (Pro version)**: This action updates post meta for a post.
- **Update post details (Pro version)**: This action updates key information about each post such as the data, title, content, author, and more. [Find out more](https://publishpress.com/knowledge-base/update-post-details/).
- **Duplicate post (Pro version)**: This step duplicates one or more posts.
- **Do custom action (Pro version)**: This step executes an action based on a hook in your site’s code. [Find out more](https://publishpress.com/knowledge-base/do-action/).
- **User interaction (Pro version)**: This step requires user action before the workflow can proceed. The actions are done via the in-site notification area. [Find out more](https://publishpress.com/knowledge-base/user-interaction/).
- **Conditional (Pro version)**: This step allows you to continue the workflow only if certain conditions are met. It is not required to have both a True and a False option. [Find out more](https://publishpress.com/knowledge-base/conditional-split/).
- **Query posts (Pro version)**: This step defines the posts that will be passed to the next step in the workflow. [Find out more](https://publishpress.com/knowledge-base/query-posts-step/).
- **Send in-site notification (Pro version)**: This action will show a notification inside your WordPress site. [Find out more](https://publishpress.com/knowledge-base/send-in-site-notification/).
