<?php

namespace App\Http\Controllers;

use App\Services\Auth\ImpersonationService;
use Illuminate\Http\RedirectResponse;

class ImpersonationController extends Controller
{
    public function leave(ImpersonationService $impersonation): RedirectResponse
    {
        $impersonation->leave();

        return redirect()
            ->route('panels.administrator.user-management.user.index')
            ->with('toast', __('app.impersonation_stopped'));
    }
}
