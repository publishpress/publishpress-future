---
title: "Send Email"
order: 12
doc_category: future
section: "Action Workflows: Actions"
---

# Send Email

“Send Email” is one of the steps available in the “Action Workflows” feature of the [PublishPress Future](https://publishpress.com/links/future) plugin. This step will send a customizable message via email. You can add an “Send Email” step from the “Actions” tab of the “Action Workflows” area.

![Adding an email step](https://docs-media.publishpress.com/publishpress-future/step-email-b0380da236.webp)

The settings for the Send Email step are available in the right sidebar:

- **Email Recipient**: There are three choices available:  Custom Addresses Site -> Admin Email Activating User -> Email
- **Subject**: This can be customized. The default entry is “Post updated: {{onPostUpdated1.postBefore.post_title}}”
- **Message**: This can be customized. The default entry is “The post “{{onPostUpdated1.postBefore.post_title}} was updated. The new title is “{{onPostUpdated1.postAfter.post_title}}”.

![](https://docs-media.publishpress.com/publishpress-future/email-step-1-87932133a6.webp)

There are variables available to customize the content of the emails sent by Action Workflows. To see the variables, click the brackets icon shown in this screenshot:

![Adding variables to the email step](https://docs-media.publishpress.com/publishpress-future/email-variables-1-6f027aead8.webp)

You will now see a selection of variables that can be added to your emails, as in this screenshot below:

![Email variable](https://docs-media.publishpress.com/publishpress-future/email-variable-author-895dec91f3.webp)
