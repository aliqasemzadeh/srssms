<?php

namespace App\Services\Permission;

use Illuminate\Support\Facades\Cache;

class PermissionCatalog
{
    public const ROLE_NAME = 'Administrator';

    public const ROLE_GROUP = 'administrator';

    public const CACHE_KEY = 'app.permission.catalog.names';

    public const CACHE_TTL_SECONDS = 86400;

    /**
     * Flatten nested permission labels from lang into Spatie permission names.
     *
     * @return list<string>
     */
    public function names(?string $roleGroup = self::ROLE_GROUP): array
    {
        return Cache::remember(
            self::CACHE_KEY.'.'.$roleGroup,
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->flatten(trans('permissions.'.$roleGroup)),
        );
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY.'.'.self::ROLE_GROUP);
    }

    /**
     * @param  mixed  $node
     * @return list<string>
     */
    public function flatten(mixed $node, string $prefix = ''): array
    {
        if (! is_array($node)) {
            return [];
        }

        $names = [];

        foreach ($node as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $path = $prefix === '' ? $key : $prefix.'.'.$key;

            if (is_array($value)) {
                array_push($names, ...$this->flatten($value, $path));

                continue;
            }

            if (is_string($value)) {
                $names[] = $path;
            }
        }

        sort($names);

        return array_values(array_unique($names));
    }
}
