<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Lang;

trait InteractsWithPermissionLabels
{
    public function permissionLabel(string $name): string
    {
        foreach (["permissions.administrator.{$name}", "permissions.{$name}"] as $key) {
            if (Lang::has($key)) {
                $translated = __($key);

                if (is_string($translated)) {
                    return $translated;
                }
            }
        }

        return $name;
    }
}
