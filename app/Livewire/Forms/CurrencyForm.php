<?php

namespace App\Livewire\Forms;

use App\Models\Finance\Currency;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class CurrencyForm extends Form
{
    public ?Currency $currency = null;

    public string $symbol = '';

    public string $name = '';

    public string $type = 'fiat';

    public int $decimals = 0;

    public bool $is_active = true;

    public ?TemporaryUploadedFile $logo = null;

    public ?string $current_logo = null;

    public function setModel(Currency $currency): void
    {
        $this->currency = $currency;

        $this->symbol = $currency->symbol;
        $this->name = $currency->name;
        $this->type = $currency->type;
        $this->decimals = $currency->decimals;
        $this->is_active = $currency->is_active;
        $this->current_logo = $currency->logo;
        $this->logo = null;
    }

    public function rules(): array
    {
        return [
            'symbol' => [
                'required',
                'string',
                'max:10',
                Rule::unique('currencies', 'symbol')->ignore($this->currency?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['fiat', 'crypto', 'commodity'])],
            'decimals' => ['required', 'integer', 'min:0', 'max:18'],
            'is_active' => ['boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function store(): Currency
    {
        $this->validate();

        $logoPath = null;

        if ($this->logo instanceof TemporaryUploadedFile) {
            $logoPath = $this->logo->store('currencies', 'public');
        }

        return Currency::create([
            'symbol' => strtoupper($this->symbol),
            'name' => $this->name,
            'type' => $this->type,
            'decimals' => $this->decimals,
            'is_active' => $this->is_active,
            'logo' => $logoPath,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $data = [
            'symbol' => strtoupper($this->symbol),
            'name' => $this->name,
            'type' => $this->type,
            'decimals' => $this->decimals,
            'is_active' => $this->is_active,
        ];

        if ($this->logo instanceof TemporaryUploadedFile) {
            if ($this->currency->logo) {
                Storage::disk('public')->delete($this->currency->logo);
            }

            $data['logo'] = $this->logo->store('currencies', 'public');
        }

        $this->currency->update($data);

        $this->setModel($this->currency->fresh());
    }

    public function removeLogo(): void
    {
        if (! $this->currency?->logo) {
            $this->current_logo = null;
            $this->logo = null;

            return;
        }

        Storage::disk('public')->delete($this->currency->logo);

        $this->currency->update(['logo' => null]);

        $this->current_logo = null;
        $this->logo = null;
    }
}
