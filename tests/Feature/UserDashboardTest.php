<?php

namespace Tests\Feature;

use App\Models\Finance\Currency;
use App\Models\Finance\Wallet;
use App\Models\Phonebook\Contact;
use App\Models\Sms\Gateway;
use App\Models\Sms\Message;
use App\Models\Sms\Provider;
use App\Models\Support\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_user_dashboard(): void
    {
        $response = $this->get(route('panels.user.dashboard.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('panels.user.dashboard.index'));
        $response->assertStatus(200);
    }

    public function test_dashboard_computes_stats_correctly(): void
    {
        $user = User::factory()->create();

        $currency = Currency::create([
            'name' => 'Iranian Rial',
            'symbol' => 'IRR',
            'decimals' => 0,
            'is_active' => true,
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'currency_id' => $currency->id,
            'balance' => 250000,
            'is_active' => true,
        ]);

        $provider = Provider::create([
            'name' => 'SabaNovin',
            'driver' => 'sabanovin',
            'is_active' => true,
        ]);

        $gateway = Gateway::create([
            'provider_id' => $provider->id,
            'number' => '10001234',
            'title' => 'Test Gateway',
            'is_public' => true,
            'is_active' => true,
            'access_type' => 'shared',
            'usage_type' => 'advertising',
        ]);

        Message::create([
            'user_id' => $user->id,
            'gateway_id' => $gateway->id,
            'number' => '10001234',
            'body' => 'Hello test message',
            'direction' => 'outbound',
            'encoding' => 'ucs2',
            'parts_count' => 1,
            'status' => 'sent',
            'source' => 'panel',
        ]);

        Contact::create([
            'user_id' => $user->id,
            'first_name' => 'Ali',
            'last_name' => 'Rezaei',
            'mobile' => '09123456789',
        ]);

        Ticket::create([
            'user_id' => $user->id,
            'title' => 'Support issue',
            'priority' => 'medium',
            'status' => 'new',
        ]);

        Livewire::actingAs($user)
            ->test('pages::panels.user.dashboard.index')
            ->assertSee('10001234')
            ->assertSee('Hello test message')
            ->assertSee('250,000')
            ->assertSee('IRR')
            ->assertSee('Support issue');
    }

    public function test_dashboard_displays_empty_states_gracefully(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::panels.user.dashboard.index')
            ->assertSee(__('general.no_sent_messages_yet'))
            ->assertSee(__('general.no_tickets_found'));
    }
}
