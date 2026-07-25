<?php

namespace App\Http\Controllers;

use App\Jobs\Sms\ProcessInboundSmsWebhook;
use App\Jobs\Sms\ProcessSmsStatusWebhook;
use App\Models\Sms\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function webhook(Request $request, string $provider, string $type): JsonResponse
    {
        $providerModel = Provider::query()
            ->where('driver', $provider)
            ->where('is_active', true)
            ->first();

        if (! $providerModel) {
            return response()->json(['ok' => false, 'message' => 'Provider not found'], 404);
        }

        $token = (string) $providerModel->credential('webhook_token', '');

        if ($token !== '' && $request->query('token') !== $token && $request->header('X-Sms-Token') !== $token) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $payload = $request->all();

        match ($type) {
            'receive' => ProcessInboundSmsWebhook::dispatch($provider, $payload),
            'status' => ProcessSmsStatusWebhook::dispatch($provider, $payload),
            default => null,
        };

        if (! in_array($type, ['receive', 'status'], true)) {
            return response()->json(['ok' => false, 'message' => 'Unsupported webhook type'], 422);
        }

        return response()->json(['ok' => true]);
    }
}
