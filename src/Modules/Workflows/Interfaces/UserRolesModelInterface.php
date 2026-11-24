<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface UserRolesModelInterface
{
    public function getUserRoles(): array;

    public function getUserRolesAsOptions(): array;
}
