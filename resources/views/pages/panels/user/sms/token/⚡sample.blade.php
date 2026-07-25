<?php

use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $language = 'curl';

    /**
     * @return array<string, array{label: string, code: string}>
     */
    #[Computed]
    public function samples(): array
    {
        $endpoint = url('/api/sms/send');

        return [
            'curl' => [
                'label' => 'cURL',
                'code' => <<<CODE
# GET
curl -G "{$endpoint}" \\
  --data-urlencode "token=YOUR_TOKEN" \\
  --data-urlencode "to=09120000000" \\
  --data-urlencode "message=Hello" \\
  --data-urlencode "gateway=1000"

# POST (JSON)
curl -X POST "{$endpoint}" \\
  -H "Authorization: Bearer YOUR_TOKEN" \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{"to":"09120000000","message":"Hello","gateway":"1000"}'
CODE,
            ],
            'php' => [
                'label' => 'PHP',
                'code' => <<<CODE
<?php

\$endpoint = '{$endpoint}';
\$token = 'YOUR_TOKEN';

\$payload = [
    'to' => '09120000000',
    'message' => 'Hello',
    'gateway' => '1000',
];

\$ch = curl_init(\$endpoint);
curl_setopt_array(\$ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . \$token,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode(\$payload),
]);

\$response = curl_exec(\$ch);
\$status = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
curl_close(\$ch);

\$data = json_decode(\$response, true);
print_r(['status' => \$status, 'body' => \$data]);
CODE,
            ],
            'python' => [
                'label' => 'Python',
                'code' => <<<CODE
import requests

endpoint = "{$endpoint}"
token = "YOUR_TOKEN"

response = requests.post(
    endpoint,
    headers={
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json",
        "Accept": "application/json",
    },
    json={
        "to": "09120000000",
        "message": "Hello",
        "gateway": "1000",
    },
    timeout=30,
)

print(response.status_code)
print(response.json())
CODE,
            ],
            'nodejs' => [
                'label' => 'Node.js',
                'code' => <<<CODE
const endpoint = '{$endpoint}';
const token = 'YOUR_TOKEN';

const response = await fetch(endpoint, {
  method: 'POST',
  headers: {
    Authorization: `Bearer \${token}`,
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  body: JSON.stringify({
    to: '09120000000',
    message: 'Hello',
    gateway: '1000',
  }),
});

const data = await response.json();
console.log(response.status, data);
CODE,
            ],
            'csharp' => [
                'label' => 'C#',
                'code' => <<<CODE
using System.Net.Http.Headers;
using System.Net.Http.Json;

var endpoint = "{$endpoint}";
var token = "YOUR_TOKEN";

using var client = new HttpClient();
client.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", token);
client.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));

var payload = new
{
    to = "09120000000",
    message = "Hello",
    gateway = "1000",
};

var response = await client.PostAsJsonAsync(endpoint, payload);
var body = await response.Content.ReadAsStringAsync();

Console.WriteLine((int)response.StatusCode);
Console.WriteLine(body);
CODE,
            ],
        ];
    }

    public function notifyCopied(): void
    {
        Flux::toast(__('general.copied'));
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.sms_api_samples') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.sms.token.index') }}" wire:navigate>{{ __('general.sms_tokens') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('panels.user.sms.token.doc') }}" wire:navigate>{{ __('general.sms_api_docs') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.sms_api_samples') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="flex flex-wrap items-center gap-2">
                <flux:button variant="primary" color="zinc" icon="book-open" :href="route('panels.user.sms.token.doc')" wire:navigate>
                    {{ __('general.sms_api_docs') }}
                </flux:button>
                <flux:button variant="primary" color="zinc" icon="arrow-right" :href="route('panels.user.sms.token.index')" wire:navigate>
                    {{ __('general.sms_tokens') }}
                </flux:button>
            </div>
        </div>

        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('general.sms_api_samples') }}</flux:heading>
                <flux:text>{{ __('general.sms_api_samples_hint') }}</flux:text>
            </div>

            <flux:tab.group>
                <flux:tabs variant="segmented" wire:model.live="language" class="flex flex-wrap">
                    @foreach ($this->samples as $key => $sample)
                        <flux:tab name="{{ $key }}" class="cursor-pointer">{{ $sample['label'] }}</flux:tab>
                    @endforeach
                </flux:tabs>

                @foreach ($this->samples as $key => $sample)
                    <flux:tab.panel name="{{ $key }}">
                        <div class="mt-4 space-y-3" x-data>
                            <div class="flex justify-end">
                                <flux:button
                                    variant="primary"
                                    color="teal"
                                    icon="copy"
                                    type="button"
                                    x-on:click="navigator.clipboard.writeText($refs.code.textContent); $wire.notifyCopied()"
                                >
                                    {{ __('general.copy') }}
                                </flux:button>
                            </div>
                            <pre x-ref="code" class="overflow-x-auto rounded-lg bg-zinc-100 p-4 text-xs dark:bg-zinc-800" dir="ltr">{{ $sample['code'] }}</pre>
                        </div>
                    </flux:tab.panel>
                @endforeach
            </flux:tab.group>
        </flux:card>
    </div>
</div>
