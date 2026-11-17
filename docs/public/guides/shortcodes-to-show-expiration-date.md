---
title: "Shortcodes to Show Action Date"
order: 36
doc_category: future
---

# Shortcodes to Show Action Date

Table of Contents

- [Default Shortcode](#0-toc-title)
- [Shortcode Arguments](#h-arguments)[type](#h-type)[dateformat](#h-dateformat)[timeformat](#h-timeformat)

You can use shortcodes to show the [PublishPress Future](https://publishpress.com/links/future) action date inside your post content.

## Default Shortcode [#](#0-toc-title)

The default shortcode is **[postexpirator]**. This will show the action date. In the image below, you can see the output from that shortcode:

![Postexpirator Shortcode](https://docs-media.publishpress.com/publishpress-future/postexpirator-shortcode-359b3a2c09.webp)

*Postexpirator Shortcode*

You can customize this shortcode output with extra formatting options. You can find details about the shortcode by going to the “Future Actions” menu link and then clicking the “Display” tab.

![PublishPress Future shortcode](https://docs-media.publishpress.com/publishpress-future/future-shortcode-settings-b55112bfda.webp)

## Shortcode Arguments [#](#h-arguments)

### type [#](#h-type)

You can use ‘type” as a format. For example: **[postexpirator type=date]**

The valid options are “full, “date”, and “time”. The default is full.

**[postexpirator type=date]** will show only the expiration date. **[postexpirator type=time]** will show only the expiration time.

### dateformat [#](#h-dateformat)

The format set here will override the value set on the settings page. For example, **[postexpirator dateformat=l F jS, Y]**

### timeformat [#](#h-timeformat)

The format set here will override the value set on the settings page.**[postexpirator timeformat=g:ia]**
