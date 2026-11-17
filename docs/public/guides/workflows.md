---
title: "Introduction to Action Workflows"
order: 46
doc_category: future
---

# Introduction to Action Workflows

Table of Contents

- [A Sample Action Workflow](#0-toc-title)
- [How to Build Action Workflows](#1-toc-title)

[PublishPress Future](https://publishpress.com/links/future) has a feature called Action Workflows. These workflows allow you to make updates to your content using custom workflows. Here are some examples of what you can do with Action Workflows:

- Email the site admin when a post is updated.
- Change the post status to “Trash” a week after it was published.
- 15 days after the post is published, move the post to the “Draft” status and add a new category.
- 1 year after a post is published, send an email to the author asking them to check the content.

When you first install [PublishPress Future](https://publishpress.com/links/future), the “Action Workflows” screen will show many examples that are ready for you to enable and use:

![Sample Action Workflows](https://docs-media.publishpress.com/publishpress-future/sample-workflows-bc8b01d45e.webp)

## A Sample Action Workflow [#](#0-toc-title)

The best way to understand what Action Workflows do is to see an example. This image below shows a sample workflow called “Unpublish to Draft after a week”. This workflow will automatically change the status of a post to “Draft” a week after it was published. In this screenshot below we have four steps:

- **Trigger: Post is published**. This trigger activates when a post is published.
- **Advanced: Schedule**. This step allows you to schedule the action. For example, you can set the workflow to run 7 days after the post is published.
- **Action: Move post to status**. This step will move the post a different status such as “Draft”.
- **Action: Send email**. This step will send an email to any addresses you choose.

![Unpublish to Draft after a week](https://docs-media.publishpress.com/publishpress-future/unpublish-to-draft-after-a-week-d82c810dd4.webp)

## How to Build Action Workflows [#](#1-toc-title)

We designed the PublishPress Future workflow builder to look just like the block editor in WordPress. You can drag-and-drop steps to build your workflow. In the screenshot below we’re adding a new action called “Move post to status”.

![](https://docs-media.publishpress.com/publishpress-future/move-post-status-76d0a131af.webp)

Each step can be inserted into the workflow using the lines that connect steps. In this image below, we’ve added the new action as the second step in the workflow.

![Add step to workflow](https://docs-media.publishpress.com/publishpress-future/add-step-workflow-8ea19b5bb7.webp)

If there are any required settings for your step, PublishPress Future will show a small warning sign. In this screenshot below we need to choose the new status that the post will be moved to.

![Settings for new workflow step](https://docs-media.publishpress.com/publishpress-future/choose-settings-step-1-0c1bfd3748.webp)
