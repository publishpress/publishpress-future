---
title: "Using Metadata in Workflows"
order: 44
doc_category: future
section: "Action Workflows: Technical Details"
---

# Using Metadata in Workflows

The Action Workflows feature in [PublishPress Future](https://publishpress.com/links/future) allows you to use metadata in your workflows. This means you can use metadata to take actions in your workflow. We also have [a complete guide to using Post Meta in workflows](https://publishpress.com/knowledge-base/future-postmeta-field/).

- Post > Metadata, looks in the `_postmeta` table.
- Post > Author > Metadata, looks in the `_usermeta` table.
- Activating User > Metadata, looks in the `_usermeta` table.
- Workflow > Metadata, looks in the `_postmeta` table.

In the screenshot below, we’re using the “Metadata” field as a date source. Because this is connected to a “Post” variable, this will look in the `_postmeta` table.

![](https://docs-media.publishpress.com/publishpress-future/screenshot-2025-04-24-at-45640e280afpm-843beba0b3.webp)

In the screenshot below, we’re using the “Metadata” field to insert a value into a custom field. Because this is connected to a “User” variable, this will look in the `_usermeta` table.

![User metadata](https://docs-media.publishpress.com/publishpress-future/insert-meta-value-a64746e6e5.webp)
