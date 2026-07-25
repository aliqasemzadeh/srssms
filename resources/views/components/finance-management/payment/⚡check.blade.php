<?php

use App\Enums\DepositStatusEnum;
use App\Models\Finance\Deposit;
use App\Support\PaymentGateways;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?Deposit $deposit = null;

    public string $status = '';

    public string $admin_note = '';

    #[On('panels.administrator.finance-management.payment.check.assign-data')]
    public function assignData(int $deposit, string $decision = 'approved'): void
    {
        $this->deposit = Deposit::query()
            ->with([
                'user' => fn ($query) => $query->withTrashed(),
                'wallet.currency' => fn ($query) => $query->withTrashed(),
            ])
            ->where('method', 'like', 'gateway_%')
            ->findOrFail($deposit);

        $this->status = in_array($decision, [
            DepositStatusEnum::Approved->value,
            DepositStatusEnum::Rejected->value,
        ], true) ? $decision : DepositStatusEnum::Approved->value;

        $this->admin_note = (string) ($this->deposit->admin_note ?? '');
        $this->resetValidation();

        Flux::modal('finance-management.payment.check')->show();
    }

    public function save(): void
    {
        if (! $this->deposit) {
            return;
        }

        $this->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    DepositStatusEnum::Approved->value,
                    DepositStatusEnum::Rejected->value,
                ]),
            ],
            'admin_note' => [
                Rule::requiredIf($this->status === DepositStatusEnum::Rejected->value),
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(function (): void {
            $deposit = Deposit::query()
                ->whereKey($this->deposit->id)
                ->lockForUpdate()
                ->firstOrFail();

            $deposit->status = DepositStatusEnum::from($this->status);
            $deposit->admin_note = filled($this->admin_note) ? $this->admin_note : null;
            $deposit->save();
        });

        $this->deposit = null;
        $this->status = '';
        $this->admin_note = '';

        $this->dispatch('panels.administrator.finance-management.payment.index.refresh');

        Flux::modals()->close();

        Flux::toast(__('general.payment_checked'));
    }
};
?>

<flux:modal name="finance-management.payment.check" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.check_payment') }}</flux:heading>
        <flux:subheading>{{ __('general.payments') }}</flux:subheading>
    </div>

    @if ($deposit)
        @php
            $decimals = $deposit->wallet?->currency?->decimals ?? 8;
            $symbol = $deposit->wallet?->currency?->symbol ?? '';
            $userLabel = ($deposit->user && ! $deposit->user->trashed())
                ? $deposit->user->full_name
                : __('general.deleted');
            $methodLabel = PaymentGateways::methodLabel((string) $deposit->method);
        @endphp

        <flux:callout icon="credit-card" variant="secondary" inline>
            <flux:callout.heading>
                {{ $userLabel }}
                —
                <span dir="ltr">{{ number_format((float) $deposit->amount, $decimals) }} {{ $symbol }}</span>
            </flux:callout.heading>
            <flux:callout.text>
                {{ $methodLabel }}
                @if ($deposit->tracking_code)
                    — <span dir="ltr">{{ $deposit->tracking_code }}</span>
                @endif
            </flux:callout.text>
        </flux:callout>

        <form wire:submit="save" class="space-y-6">
            <flux:select
                wire:model.live="status"
                variant="listbox"
                label="{{ __('general.status') }}"
            >
                <flux:select.option value="{{ DepositStatusEnum::Approved->value }}">
                    {{ DepositStatusEnum::Approved->label() }}
                </flux:select.option>
                <flux:select.option value="{{ DepositStatusEnum::Rejected->value }}">
                    {{ DepositStatusEnum::Rejected->label() }}
                </flux:select.option>
            </flux:select>

            <flux:textarea
                wire:model="admin_note"
                label="{{ __('general.admin_note') }}"
                description="{{ $status === DepositStatusEnum::Rejected->value ? __('general.admin_note_required_for_reject') : __('general.admin_note') }}"
                rows="4"
            />

            <flux:button
                type="submit"
                variant="primary"
                color="{{ $status === DepositStatusEnum::Rejected->value ? 'red' : 'green' }}"
                icon="{{ $status === DepositStatusEnum::Rejected->value ? 'x' : 'check' }}"
                class="w-full"
            >
                {{ $status === DepositStatusEnum::Rejected->value ? __('general.reject') : __('general.approve') }}
            </flux:button>
        </form>
    @endif
</flux:modal>
