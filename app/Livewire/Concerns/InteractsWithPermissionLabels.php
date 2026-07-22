<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Arr;

trait InteractsWithPermissionLabels
{
    public function permissionLabel(string $name): string
    {
        $all = trans('permissions');

        if (! is_array($all)) {
            return $name;
        }

        // Direct (flat) key, e.g. "permissions.user-management.user.view".
        $label = Arr::get($all, $name);

        if (is_string($label)) {
            return $label;
        }

        // Grouped by role, e.g. "permissions.administrator.user-management.user.view".
        foreach ($all as $group) {
            if (! is_array($group)) {
                continue;
            }

            $label = Arr::get($group, $name);

            if (is_string($label)) {
                return $label;
            }
        }

        return $name;
    }
}
