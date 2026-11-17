---
title: "Programmatically Check for Future Actions"
order: 4
doc_category: future
section: "Future Actions: Technical Details"
---

# Programmatically Check for Future Actions

You can use custom code to determine if a post has a scheduled future action.

You can utilize the `isExpirationEnabled()` method from an instance of `ExpirablePostModel` in the `PublishPress\Future\Modules\Expirator\Models` namespace.

In this code below, the `get_the_ID()` function should be replaced with the actual post ID for which you want to check. By calling the `isExpirationEnabled()` method on the `$postModel` instance, you can determine whether the post has a scheduled future action for expiration.

```
<?php

$container = PublishPress\Future\Core\DI\Container::getInstance();
$postModelFactory = $container->get('future.free/expirable-post-model-factory');
$postModel = $postModelFactory(get_the_ID());

if ($postModel->isExpirationEnabled()) {
    ...
}
```
