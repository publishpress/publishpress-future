---
title: "Metadata Scheduling Example With ACF (Pro)"
order: 24
doc_category: future
section: "Future Actions: Metadata Scheduling"
---

# Metadata Scheduling Example With ACF (Pro)

Table of Contents

- [Example #1: Update the Action Date from ACF](#0-toc-title)
- [Example #2: Update the Action from ACF](#1-toc-title)

This feature requires the Pro version of the PublishPress Future plugin.

In [PublishPress Future](https://publishpress.com/links/future) Pro it is possible to schedule changes to your posts based on metadata. This article will show examples of how to use this Metadata Scheduling feature with the Advanced Custom Fields (ACF) plugin.

## Example #1: Update the Action Date from ACF [#](#0-toc-title)

This example will show how to update the Future Action Date from an ACF field. The first step is to create a field in the ACF plugin:

- **Field Type**: choose the “Date Time Picker” option.
- **Display Format**: choose the “Y-m-d H:i:s” option.

![Create a new ACF field for PublishPress Future Pro](https://docs-media.publishpress.com/publishpress-future/acf-metadata-scheduling-1a77d544e5.webp)

- Go to “Future”, then “Settings” in your WordPress admin menu.
- Click the “Post Types” tab.
- In the “Posts” area, check the box “Enable metadata integration for the post type”.
- In the “Action Data” area, enter the “Field Name” that you created in the step above.

![Future Action and ACF metadata](https://docs-media.publishpress.com/publishpress-future/future-action-date-353cfd8def.webp)

You can now use the ACF field to update the date for Future Actions.

- Edit a post.
- Enable the Future Action option in the sidebar.
- Enter a date in the ACF field.
- Save the post and refresh the field.
- The Future Action will be updated with the date from the ACF field.

![Future Action and ACF field](https://docs-media.publishpress.com/publishpress-future/acf-update-metadata-4418cff09a.webp)

## Example #2: Update the Action from ACF [#](#1-toc-title)

This example will show how to update the Future Action type from an ACF field. We recommend following the first example before this one because the Action Date field is required. The next step in this example is to create a field in the ACF plugin:

- **Field Type**: choose the “Radio Button” option.
- **Choices**: enter at least one of the [available action types](https://publishpress.com/knowledge-base/metadata-scheduling/#1-toc-title) as a value. In this example, we are giving “draft”, “trash”, and “delete” as possible values.

![ACF Radio button values](https://docs-media.publishpress.com/publishpress-future/future-action-type-acf-1-578986a3c4.webp)

- Go to “Future”, then “Settings” in your WordPress admin menu.
- Click the “Post Types” tab.
- In the “Posts” area, check the box “Enable metadata integration for the post type”.
- In the “Action Type” area, enter the “Field Name” that you created in the step above.

![Future Action Date and Type](https://docs-media.publishpress.com/publishpress-future/screenshot-2024-02-27-at-23944e280afpm-873fb33fc0.webp)

You can now use the ACF field to update the date for Future Actions.

- Edit a post.
- Enable the Future Action option in the sidebar.
- Choose a type in the “Future Action Type” ACF field.
- Save the post and refresh the field.
- The Future Action will be updated with the type from the ACF field.

![Future Action Change and Date](https://docs-media.publishpress.com/publishpress-future/future-action-acf-edf0575340.webp)
