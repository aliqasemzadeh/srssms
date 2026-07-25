<?php

namespace App\Livewire\Forms\Sms;

use App\Models\Sms\Token;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class TokenForm extends Form
{
    public ?Token $token = null;

    public string $name = '';

    public string $allowed_ips = '';

    public bool $is_active = true;

    public function setModel(Token $token): void
    {
        $this->token = $token;
        $this->name = $token->name;
        $this->allowed_ips = collect($token->allowed_ips ?? [])->implode("\n");
        $this->is_active = (bool) $token->is_active;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'allowed_ips' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
        ];
    }

    public function store(): Token
    {
        $this->validate();

        return Token::query()->create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'token' => Token::generateToken(),
            'allowed_ips' => $this->parseIps(),
            'is_active' => $this->is_active,
        ]);
    }

    public function update(bool $regenerate = false): void
    {
        $this->validate();

        if (! $this->token || $this->token->user_id !== Auth::id()) {
            return;
        }

        $data = [
            'name' => $this->name,
            'allowed_ips' => $this->parseIps(),
            'is_active' => $this->is_active,
        ];

        if ($regenerate) {
            $data['token'] = Token::generateToken();
        }

        $this->token->update($data);
        $this->token->refresh();
    }

    /**
     * @return list<string>|null
     */
    protected function parseIps(): ?array
    {
        $ips = collect(preg_split('/[\s,;]+/', $this->allowed_ips) ?: [])
            ->map(fn (string $ip) => trim($ip))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $ips === [] ? null : $ips;
    }
}
