<?php

namespace PublishPress\Future\Framework\WordPress\Facade;

use PublishPress\Future\Core\HookableInterface;

\defined('ABSPATH') || exit;

interface NoticeInterface
{
    public function init();

    public function registerErrorNotice($name, $message);

    public function registerSuccessNotice($name, $message);

    public function redirectShowingNotice($name);

    public function renderNotices();
}
