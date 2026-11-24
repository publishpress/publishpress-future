<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Interfaces\UserRolesModelInterface;

\defined('ABSPATH') || exit;

class UserRolesModel implements UserRolesModelInterface
{
    public function getUserRoles($filtered = false): array
    {
        if ($filtered) {
            return get_editable_roles();
        }

        return wp_roles()->roles;
    }

    public function getUserRolesAsOptions($filtered = false): array
    {
        $userRoles = $this->getUserRoles($filtered);

        foreach ($userRoles as $userRoleKey => $userRole) {
            $options[] = [
                'label' => $userRole['name'],
                'value' => $userRoleKey,
            ];
        }

        return $options;
    }
}
