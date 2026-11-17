---
title: "User Interaction (Pro)"
order: 43
doc_category: future
section: "Action Workflows: Actions"
---

# User Interaction (Pro)

This feature requires the Pro version of the PublishPress Future plugin.

The “User interaction” action is available in the Pro version of [PublishPress Future](https://publishpress.com/links/future). This step allows you to require user action before the workflow can proceed. The actions are done via the [in-site notification area](https://publishpress.com/knowledge-base/send-in-site-notification/).

This screenshot shows an example of the “User interaction” action. The user can click either “Approve” or “Deny”.

![In-site notifications for approval](https://docs-media.publishpress.com/publishpress-future/user-interaction-interface-c2aecebc65.webp)

This next screenshot shows an example workflow with a “User interaction” action. If the user clicks “Approve”, extra terms will be added to the post. If the user clicks “Deny”, terms will be removed from the post.

![User interaction workflow](https://docs-media.publishpress.com/publishpress-future/user-interaction-example-6bb1b75df4.webp)

There are several ways to configure the “User interaction” action. These options appear in the right sidebar of the “Action Workflows” area, as shown in this next screenshot:

![User interaction options](https://docs-media.publishpress.com/publishpress-future/user-interaction-options-9f0155e7c3.webp)

- **Responders**: You can enter a comma-separated list of user names, ids, emails or user roles that can interact with this step. The default is “administrator” so only users in the Administrator role can respond.
- **Message**: You can edit the message that will be displayed to the responders. There are three options for the message:  **Subject**: This is the title of the message. **Message**: This is the main body of the message. **Notification Type**: This will control the colored border for the notification. Choose from Info (Blue), Success (Green), Warning (Yellow), and Error (Red).
- **Options**: You can the options that users can click on.

Let’s see a couple more examples of this “User interaction” step. Instead of “Approve” or “Deny”, you can customize the interactive text. We’ve chosen “Email Steve” and “Email Jane” as the two options. The user can choose who receive an email from this workflow.

![Post is updated - emails](https://docs-media.publishpress.com/publishpress-future/post-is-updated-emails-4595355eff.webp)

We’ve shown examples with two choices, but this feature can expand. In this next screenshot we’ve added 5 paths to the workflow.

![User interaction multiple options](https://docs-media.publishpress.com/publishpress-future/user-interaction05-f89286059a.webp)

Here’s how that notification would appear with five different options:

![Five different options in a workflow](https://docs-media.publishpress.com/publishpress-future/email-5-users-interaction-deb5364579.webp)
