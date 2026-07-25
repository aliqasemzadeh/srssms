<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ImpersonationService
{
    public const SESSION_KEY = 'impersonator_id';

    public const PERMISSION = 'user-management.user.impersonate';

    public function start(User $target): void
    {
        $actor = Auth::user();

        if (! $actor instanceof User) {
            abort(403);
        }

        if ($this->isImpersonating()) {
            throw ValidationException::withMessages([
                'impersonate' => __('app.already_impersonating'),
            ]);
        }

        if ($actor->is($target)) {
            throw ValidationException::withMessages([
                'impersonate' => __('app.cannot_impersonate_self'),
            ]);
        }

        $actorId = $actor->id;

        // Auth::login migrates the session (destroying prior data), so store after login.
        Auth::login($target);
        session([self::SESSION_KEY => $actorId]);
    }

    public function leave(): void
    {
        if (! $this->isImpersonating()) {
            throw ValidationException::withMessages([
                'impersonate' => __('app.not_impersonating'),
            ]);
        }

        $admin = User::query()->findOrFail(session(self::SESSION_KEY));

        session()->forget(self::SESSION_KEY);
        Auth::login($admin);
    }

    public function isImpersonating(): bool
    {
        return session()->has(self::SESSION_KEY);
    }

    public function impersonator(): ?User
    {
        if (! $this->isImpersonating()) {
            return null;
        }

        return User::query()->find(session(self::SESSION_KEY));
    }
}
