<?php

namespace Tests\Feature;

use App\Models\Sms\Gateway;
use App\Models\Sms\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class OtpSmsSendTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_sends_sms_via_first_service_gateway_through_sabanovin(): void
    {
        Http::fake([
            'api.sabanovin.com/*' => Http::response([
                'status' => ['code' => 200, 'message' => 'OK'],
                'entries' => [],
            ], 200),
        ]);

        $provider = Provider::create([
            'name' => 'SabaNovin',
            'driver' => 'sabanovin',
            'credentials' => [
                'api_key' => 'test-api-key',
                'webhook_token' => 'test-webhook-token',
            ],
            'is_active' => true,
        ]);

        Gateway::create([
            'provider_id' => $provider->id,
            'number' => '10001234',
            'title' => 'Line 1',
            'access_type' => 'shared',
            'usage_type' => 'service',
            'is_public' => true,
            'is_active' => true,
            'sms_rate' => 1500,
        ]);

        Gateway::create([
            'provider_id' => $provider->id,
            'number' => '20005678',
            'title' => 'Line 2',
            'access_type' => 'shared',
            'usage_type' => 'advertising',
            'is_public' => true,
            'is_active' => true,
            'sms_rate' => 1500,
        ]);

        $user = User::factory()->create([
            'mobile' => '09123456789',
        ]);

        Livewire::test('pages::auth.otp')
            ->set('mobile', '09123456789')
            ->call('send')
            ->assertHasNoErrors()
            ->assertSet('step', 'verify');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.sabanovin.com/v1/test-api-key/sms/send.json')
                && $request['gateway'] === '10001234'
                && $request['to'] === '09123456789'
                && str_contains((string) $request['text'], 'کد یک‌بارمصرف');
        });
    }

    public function test_otp_shows_error_when_no_sms_gateway_is_configured(): void
    {
        $user = User::factory()->create([
            'mobile' => '09123456789',
        ]);

        Livewire::test('pages::auth.otp')
            ->set('mobile', '09123456789')
            ->call('send')
            ->assertHasErrors(['otp']);
    }
}
