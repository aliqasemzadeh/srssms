<?php

namespace App\Http\Controllers\Sms;

use App\Enums\Sms\SmsMessageSourceEnum;
use App\Http\Controllers\Controller;
use App\Models\Sms\Gateway;
use App\Models\Sms\Token;
use App\Models\Sms\TokenLog;
use App\Services\Sms\SmsSender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class SendController extends Controller
{
    public function send(Request $request, SmsSender $smsSender): JsonResponse
    {
        $payload = $this->requestPayload($request);
        $ip = $this->resolveClientIp($request);
        $tokenModel = null;
        $messageId = null;

        try {
            $tokenValue = $this->resolveTokenValue($request, $payload);

            if ($tokenValue === '') {
                return $this->finish(
                    $request,
                    $payload,
                    $ip,
                    null,
                    null,
                    $this->error('invalid_token', __('general.sms_api_errors.invalid_token'), 401)
                );
            }

            $tokenModel = Token::query()
                ->with('user')
                ->where('token', $tokenValue)
                ->where('is_active', true)
                ->first();

            if (! $tokenModel || ! $tokenModel->user) {
                return $this->finish(
                    $request,
                    $payload,
                    $ip,
                    null,
                    null,
                    $this->error('invalid_token', __('general.sms_api_errors.invalid_token'), 401)
                );
            }

            if (! $tokenModel->allowsIp($ip)) {
                return $this->finish(
                    $request,
                    $payload,
                    $ip,
                    $tokenModel,
                    null,
                    $this->error('ip_not_allowed', __('general.sms_api_errors.ip_not_allowed'), 403)
                );
            }

            $to = trim((string) ($payload['to'] ?? ''));
            $messageText = trim((string) ($payload['message'] ?? ''));
            $gatewayNumber = trim((string) ($payload['gateway'] ?? ''));

            if ($to === '' || $messageText === '' || $gatewayNumber === '') {
                return $this->finish(
                    $request,
                    $payload,
                    $ip,
                    $tokenModel,
                    null,
                    $this->error('validation_error', __('general.sms_api_errors.validation_error'), 422)
                );
            }

            $recipients = collect(preg_split('/[\s,;]+/', $to) ?: [])
                ->map(fn (string $mobile) => ['mobile' => trim($mobile)])
                ->filter(fn (array $row) => $row['mobile'] !== '')
                ->unique('mobile')
                ->values()
                ->all();

            if ($recipients === []) {
                return $this->finish(
                    $request,
                    $payload,
                    $ip,
                    $tokenModel,
                    null,
                    $this->error('no_recipients', __('general.sms_api_errors.no_recipients'), 422)
                );
            }

            $user = $tokenModel->user;

            $gateway = Gateway::query()
                ->usableBy($user)
                ->where('number', $gatewayNumber)
                ->where('is_active', true)
                ->first();

            if (! $gateway) {
                return $this->finish(
                    $request,
                    $payload,
                    $ip,
                    $tokenModel,
                    null,
                    $this->error('gateway_not_found', __('general.sms_api_errors.gateway_not_found'), 404)
                );
            }

            $message = $smsSender->queueCampaign(
                $gateway,
                $user,
                $messageText,
                $recipients,
                bill: true,
                source: SmsMessageSourceEnum::Api,
                token: $tokenModel,
            );

            $tokenModel->forceFill(['last_used_at' => now()])->save();

            $messageId = $message->id;

            return $this->finish(
                $request,
                $payload,
                $ip,
                $tokenModel,
                $messageId,
                $this->success([
                    'message_id' => $message->id,
                    'status' => $message->status->value,
                    'recipients_count' => $message->recipients()->count(),
                    'parts_count' => $message->parts_count,
                    'cost' => $message->cost,
                ])
            );
        } catch (RuntimeException $e) {
            [$code, $httpStatus] = $this->mapRuntimeException($e);

            return $this->finish(
                $request,
                $payload,
                $ip,
                $tokenModel,
                $messageId,
                $this->error($code, $e->getMessage() ?: __('general.sms_api_errors.'.$code), $httpStatus)
            );
        } catch (Throwable) {
            return $this->finish(
                $request,
                $payload,
                $ip,
                $tokenModel,
                $messageId,
                $this->error('server_error', __('general.sms_api_errors.server_error'), 500)
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function requestPayload(Request $request): array
    {
        $json = $request->json()?->all() ?? [];

        return array_merge($request->query(), $request->request->all(), is_array($json) ? $json : []);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function resolveTokenValue(Request $request, array $payload): string
    {
        $bearer = $request->bearerToken();

        if (filled($bearer)) {
            return trim((string) $bearer);
        }

        $header = $request->header('X-Sms-Token');

        if (filled($header)) {
            return trim((string) $header);
        }

        return trim((string) ($payload['token'] ?? ''));
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function mapRuntimeException(RuntimeException $e): array
    {
        $message = $e->getMessage();

        return match (true) {
            $message === __('general.insufficient_wallet_balance') => ['insufficient_balance', 402],
            $message === __('general.no_active_wallet') => ['no_wallet', 402],
            $message === __('general.sms_gateway_inactive') => ['gateway_inactive', 403],
            $message === __('general.no_sms_recipients') => ['no_recipients', 422],
            default => ['server_error', 500],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{body: array<string, mixed>, status: int}
     */
    protected function success(array $data): array
    {
        return [
            'body' => [
                'ok' => true,
                'code' => 'queued',
                'message' => __('general.sms_api_errors.queued'),
                'data' => $data,
            ],
            'status' => 200,
        ];
    }

    /**
     * @return array{body: array<string, mixed>, status: int}
     */
    protected function error(string $code, string $message, int $status): array
    {
        return [
            'body' => [
                'ok' => false,
                'code' => $code,
                'message' => $message,
                'data' => null,
            ],
            'status' => $status,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array{body: array<string, mixed>, status: int}  $result
     */
    protected function finish(
        Request $request,
        array $payload,
        ?string $ip,
        ?Token $token,
        ?int $messageId,
        array $result,
    ): JsonResponse {
        $clientIp = $this->resolveClientIp($request, $ip);

        $safeRequest = $payload;
        if (isset($safeRequest['token'])) {
            $safeRequest['token'] = '***';
        }

        $safeRequest['_meta'] = [
            'ip' => $clientIp,
            'forwarded_for' => $request->header('X-Forwarded-For'),
            'real_ip' => $request->header('X-Real-IP'),
            'user_agent' => $request->userAgent(),
        ];

        TokenLog::query()->create([
            'token_id' => $token?->id,
            'user_id' => $token?->user_id,
            'ip' => $clientIp,
            'method' => $request->method(),
            'path' => '/'.$request->path(),
            'request' => $safeRequest,
            'response' => $result['body'],
            'status_code' => $result['status'],
            'message_id' => $messageId,
        ]);

        return response()->json($result['body'], $result['status']);
    }

    protected function resolveClientIp(Request $request, ?string $fallback = null): ?string
    {
        $ip = $request->ip() ?: $fallback;

        if (filled($ip)) {
            return trim((string) $ip);
        }

        $forwarded = $request->header('X-Forwarded-For');

        if (filled($forwarded)) {
            $first = trim(explode(',', (string) $forwarded)[0]);

            if ($first !== '') {
                return $first;
            }
        }

        $realIp = $request->header('X-Real-IP');

        if (filled($realIp)) {
            return trim((string) $realIp);
        }

        return null;
    }
}
