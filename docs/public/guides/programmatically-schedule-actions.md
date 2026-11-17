---
title: "Programmatically Add or Remove Future Actions"
order: 29
doc_category: future
---

# Programmatically Add or Remove Future Actions

Table of Contents

- [How to add Future Actions](#0-toc-title)[Moving a post to a custom post status](#1-toc-title)
- [How to remove Future Actions](#2-toc-title)
- [How to hook into the action that schedules actions](#how-to-hook-into-action-schedule-posts)
- [How to hook into when the action expired](#how-to-hook-into-action-schedule-posts)
- [How to prevent double schedule action](#how-to-hook-into-action-schedule-posts)
- [How to update Future Actions from post metadata](#6-toc-title)

## How to add Future Actions [#](#0-toc-title)

For scheduling Future Actions programmatically, try this code:

```
if (defined('PUBLISHPRESS_FUTURE_LOADED')) {
    /**
     * Expiration types:
     *
     *  - draft
     *  - delete
     *  - trash
     *  - private
     *  - stick
     *  - unstick
     *  - category
     *  - category-add
     *  - category-remove
     *
     *  For "category", add additional attributes:
     *   'category' => $categoryId,
     *   'categoryTaxonomy' => $taxonomyName
     */
    $options   = [
        'expireType' => 'delete',
        'id'         => $postId,
    ];
    $postId    = 258;
    $timestamp = date('U', strtotime('2021-06-25 10:00:00'));
    do_action(
        'publishpressfuture_schedule_expiration',
        $postId,
        $timestamp,
        $options
    );
}
```

### Moving a post to a custom post status [#](#1-toc-title)

**[PublishPress Future](https://publishpress.com/links/future) Pro** allows expiring the post moving it to custom post statuses. For doing that programmatically, ensure you have enabled the custom post status for the specific post type in the Settings > Post Statuses tab.

For scheduling Future Actions programmatically for moving the post to a custom post status, try this code:

```
if (defined('PUBLISHPRESS_FUTURE_LOADED')) {
    $options   = [
        'expireType' => 'change-status',
        'newStatus' => 'deferred', // Change this according to the custom post status you want to use.
        'id'         => $postId,
    ];
    $postId    = 258;
    $timestamp = date('U', strtotime('2021-06-25 10:00:00'));
    do_action(
        'publishpressfuture_schedule_expiration',
        $postId,
        $timestamp,
        $options
    );
}
```

## How to remove Future Actions [#](#2-toc-title)

To remove the Future Action programmatically, try this code:

```
if (defined('PUBLISHPRESS_FUTURE_LOADED')) {
    $postId = 258;
    do_action('publishpressfuture_unschedule_expiration', $postId);
}
```

The actual expiration date for the post is stored in the `_expiration-date` row of the `_postmeta` table and in the cron job.

## How to hook into the action that schedules actions [#](#how-to-hook-into-action-schedule-posts)

When scheduling the future action the plugin will do the action `publishpressfuture_schedule_expiration` with a priority of number 10 (default number).

If you want to hook into that action to execute a custom action after the expiration is scheduled for the post, you can add a hook with a priority higher number than 10.

```
add_action('publishpressfuture_schedule_expiration', 'custom_action_after_expiration_was_scheduled', 20, 3);
```

## How to hook into when the action expired [#](#how-to-hook-into-action-schedule-posts)

When a post expires, the plugin triggers the `publishpressfuture_post_expired` action. This action allows you to hook in and execute custom code after the post has expired.

```
add_action('publishpressfuture_post_expired', 'custom_action_after_expiration', 20, 3);
```

## How to prevent double schedule action [#](#how-to-hook-into-action-schedule-posts)

Sometimes, when you add future action programmatically, there are 2 actions created (scheduled & cancelled). This could happen if the action is triggered multiple times.

[PublishPress Future](https://publishpress.com/links/future) does not allow for multiple schedules for the same post; thus, it automatically cancels any existing schedule when a new one is set.

To prevent duplication, one possible solution would be to introduce a validation check using a function like the following:

```
use PublishPress\Future\Core\DI\Container as FutureContainer;
use PublishPress\Future\Core\DI\ServicesAbstract as FutureServices;

add_action('acf/save_post', 'schedule_post_expiration', 25);

function postIsScheduledWithSameDate($postId, $date)
{
    $container = FutureContainer::getInstance();
    $postModelFactory = $container->get(FutureServices::EXPIRABLE_POST_MODEL_FACTORY);
    $postModel = $postModelFactory($postId);

    if (! $postModel->isExpirationEnabled()) {
        return false;
    }

    return $postModel->getExpirationDateAsUnixTime(false) === (int)$date;
}

function schedule_post_expiration($postId)
{
    $expectedPostType = 'post';

    if (! defined('PUBLISHPRESS_FUTURE_LOADED')) {
		return;
	}

    // It is important to test the post type here, because the save_post
    // action is triggered for all post types, including  'revision' for the
    // post that was originally saved.
    $postType = get_post_type($postId);
    if ($postType !== $expectedPostType) {
        return;
    }

    $startDate = get_field('start_date', $postId);
    $expiration = date('U', strtotime($startDate . ' -1 days'));


	if (postIsScheduledWithSameDate($postId, $expiration)) {
		return;
	}

    /**
	 * Expiration types:
	 *
	 *  - draft
	 *  - delete
	 *  - trash
	 *  - private
	 *  - stick
	 *  - unstick
	 *  - category
	 *  - category-add
	 *  - category-remove
	 *
	 *  For "category", add additional attributes:
	 *   'category' => $categoryId,
	 *   'categoryTaxonomy' => $taxonomyName
	 */
	$options   = [
		'expireType' => 'draft',
        'newStatus' => null,
        'category' => null,
        'categoryTaxonomy' => null,

	];

	do_action(
		'publishpressfuture_schedule_expiration',
		$postId,
		$expiration,
		$options
	);
}
```

## How to update Future Actions from post metadata [#](#6-toc-title)

To programmatically schedule future actions based on metadata, use the following code snippet:

```
$container = PublishPress\Future\Core\DI\Container::getInstance();
$factory = $container->get(PublishPress\Future\Core\DI\ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
$postModel = $factory($postId);
$postModel->syncScheduleWithPostMeta();
```

**Note:**Ensure that the post already has the specific metadata set and that Metadata Scheduling is enabled for the post type in the settings.  
  
You can use this code snippet within a WordPress action hook to trigger it. The effect is the same as using the bulk action option: “Update Future Actions from Post Metadata.”
