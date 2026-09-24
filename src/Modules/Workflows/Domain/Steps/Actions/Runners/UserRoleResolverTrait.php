<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

/**
 * Shared helper for user-role action runners: reads the selected role from the
 * node settings, tolerating both scalar and array-shaped `select` values.
 */
trait UserRoleResolverTrait
{
    private function getRoleFromSettings(array $nodeSettings): string
    {
        $role = $nodeSettings['role'] ?? '';

        if (is_array($role)) {
            $role = $role['value'] ?? ($role['role'] ?? '');
        }

        return is_string($role) ? trim($role) : '';
    }
}
