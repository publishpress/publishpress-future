<?php

namespace PublishPress\Future\Modules\Expirator\Models;

\defined('ABSPATH') || exit;

interface ActionModelInterface
{
    public function getPostId(): int;

    public function getPostType(): string;

    public function getAction(): string;

    public function getActionArgs(): array;

    public function getActionDateAsUnixTime(): int;

    public function getActionDateAsFormattedString(): string;
}
