@php
    $general = $general ?? app(\App\Settings\GeneralSettings::class);
    $welcome = $welcome ?? app(\App\Settings\WelcomePageSettings::class);
    $contact = $contact ?? app(\App\Settings\ContactSettings::class);
    $social = $social ?? app(\App\Settings\SocialSettings::class);

    $siteName = $general->site_name ?: config('app.name');
    $shortName = $general->site_short_name ?: strtoupper(mb_substr($siteName, 0, 3));
    $logo = filled($general->site_logo) ? asset('storage/'.$general->site_logo) : null;
    $generalSettings = $general;
    $title = $siteName;

    $socialNetworks = [
        'telegram' => 'send',
        'eitaa' => 'message-circle',
        'bale' => 'message-square',
        'rubika' => 'smartphone',
        'soroush' => 'messages-square',
        'aparat' => 'video',
        'instagram' => 'camera',
        'linkedin' => 'briefcase',
        'x_twitter' => 'at-sign',
    ];

    $activeSocials = collect($socialNetworks)
        ->filter(fn (string $icon, string $network) => filled($social->{$network}))
        ->map(fn (string $icon, string $network) => [
            'network' => $network,
            'icon' => $icon,
            'url' => $social->{$network},
            'label' => __('general.'.$network),
        ])
        ->values();

    $hasContact = filled($contact->address)
        || filled($contact->support_email)
        || filled($contact->fax)
        || ! empty($contact->phone_numbers);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.page_direction') }}">
@include('layouts.shared.head')
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <div class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(13,148,136,0.18),_transparent_55%),linear-gradient(to_bottom,_rgba(255,255,255,0.4),_transparent)] dark:bg-[radial-gradient(ellipse_at_top,_rgba(45,212,191,0.12),_transparent_55%),linear-gradient(to_bottom,_rgba(9,9,11,0.6),_transparent)]"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.35] [background-image:radial-gradient(rgba(113,113,122,0.35)_1px,transparent_1px)] [background-size:18px_18px] dark:opacity-[0.2]"></div>

        <header class="relative z-20 border-b border-zinc-200/70 bg-white/70 backdrop-blur-md dark:border-zinc-800/70 dark:bg-zinc-950/60">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-9 w-auto object-contain" />
                    @else
                        <span class="flex size-9 items-center justify-center rounded-lg bg-teal-600 text-xs font-bold tracking-wide text-white">
                            {{ $shortName }}
                        </span>
                    @endif
                    <span class="text-base font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $siteName }}</span>
                </a>

                <nav class="hidden items-center gap-6 text-sm text-zinc-600 dark:text-zinc-300 md:flex">
                    <a href="#features" class="transition hover:text-teal-700 dark:hover:text-teal-300">{{ __('general.nav_features') }}</a>
                    @if ($hasContact)
                        <a href="#contact" class="transition hover:text-teal-700 dark:hover:text-teal-300">{{ __('general.nav_contact') }}</a>
                    @endif
                    @if ($activeSocials->isNotEmpty())
                        <a href="#social" class="transition hover:text-teal-700 dark:hover:text-teal-300">{{ __('general.nav_social') }}</a>
                    @endif
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <flux:button href="{{ route('panels.administrator.dashboard.index') }}" size="sm" variant="primary" color="teal" icon="layout-dashboard">
                            {{ __('general.administrator_panel') }}
                        </flux:button>
                        <flux:button href="{{ route('panels.user.dashboard.index') }}" size="sm" variant="filled" color="zinc" icon="user">
                            {{ __('general.user_panel') }}
                        </flux:button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <flux:button type="submit" size="sm" variant="ghost" icon="arrow-right-start-on-rectangle">
                                {{ __('actions.log_out') }}
                            </flux:button>
                        </form>
                    @else
                        <flux:button href="{{ route('login') }}" size="sm" variant="primary" color="teal" icon="arrow-right-end-on-rectangle">
                            {{ __('general.login') }}
                        </flux:button>
                    @endauth
                </div>
            </div>
        </header>

        <main class="relative z-10">
            <section class="mx-auto flex min-h-[calc(100vh-4.5rem)] max-w-6xl flex-col items-center justify-center px-4 py-16 text-center sm:px-6">
                <div class="mb-8 flex flex-col items-center gap-4">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-16 w-auto object-contain sm:h-20" />
                    @else
                        <div class="flex size-16 items-center justify-center rounded-2xl bg-teal-600 text-xl font-bold tracking-wide text-white shadow-lg shadow-teal-600/20 sm:size-20 sm:text-2xl">
                            {{ $shortName }}
                        </div>
                    @endif
                    <h1 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl md:text-6xl dark:text-white">
                        {{ $siteName }}
                    </h1>
                </div>

                <div
                    class="mb-5 min-h-[2.5rem] text-xl font-medium text-teal-700 sm:min-h-[3rem] sm:text-2xl md:text-3xl dark:text-teal-300"
                    x-data="{
                        phrases: @js($welcome->typewriter_phrases),
                        typeDelay: {{ (int) $welcome->typewriter_type_delay }},
                        deleteDelay: {{ (int) $welcome->typewriter_delete_delay }},
                        pauseDelay: {{ (int) $welcome->typewriter_pause_delay }},
                        text: '',
                        i: 0,
                        sleep(ms) {
                            return new Promise((resolve) => setTimeout(resolve, ms));
                        },
                        async start() {
                            if (! Array.isArray(this.phrases) || this.phrases.length === 0) {
                                return;
                            }

                            while (true) {
                                const phrase = String(this.phrases[this.i] ?? '');

                                for (let c = 0; c < phrase.length; c++) {
                                    this.text = phrase.slice(0, c + 1);
                                    await this.sleep(this.typeDelay);
                                }

                                await this.sleep(this.pauseDelay);

                                for (let c = phrase.length; c > 0; c--) {
                                    this.text = phrase.slice(0, c - 1);
                                    await this.sleep(this.deleteDelay);
                                }

                                this.i = (this.i + 1) % this.phrases.length;
                            }
                        }
                    }"
                    x-init="start()"
                >
                    <span x-text="text"></span><span class="ms-0.5 inline-block animate-pulse text-teal-500">|</span>
                </div>

                @if (filled($welcome->hero_subtitle))
                    <p class="mb-10 max-w-2xl text-base text-zinc-600 sm:text-lg dark:text-zinc-300">
                        {{ $welcome->hero_subtitle }}
                    </p>
                @endif

                <div class="flex flex-wrap items-center justify-center gap-3">
                    @auth
                        <flux:button href="{{ route('panels.administrator.dashboard.index') }}" variant="primary" color="teal" icon="layout-dashboard">
                            {{ __('general.administrator_panel') }}
                        </flux:button>
                        <flux:button href="{{ route('panels.user.dashboard.index') }}" variant="filled" color="zinc" icon="user">
                            {{ __('general.user_panel') }}
                        </flux:button>
                    @else
                        <flux:button href="{{ route('login') }}" variant="primary" color="teal" icon="arrow-right-end-on-rectangle">
                            {{ __('general.login') }}
                        </flux:button>
                    @endauth
                </div>
            </section>

            <section id="features" class="scroll-mt-24 border-t border-zinc-200/80 bg-white/60 py-20 backdrop-blur-sm dark:border-zinc-800/80 dark:bg-zinc-950/40">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <div class="mb-12">
                        <flux:heading size="xl">{{ __('general.nav_features') }}</flux:heading>
                    </div>

                    <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($welcome->features as $index => $feature)
                            <div class="space-y-3">
                                <div class="flex size-11 items-center justify-center rounded-xl bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300">
                                    <flux:icon name="{{ $feature['icon'] ?? 'message-square' }}" variant="outline" class="size-6" />
                                </div>
                                <flux:heading size="lg">{{ $feature['title'] ?? '' }}</flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">{{ $feature['description'] ?? '' }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            @if ($hasContact)
                <section id="contact" class="scroll-mt-24 border-t border-zinc-200/80 py-20 dark:border-zinc-800/80">
                    <div class="mx-auto max-w-6xl px-4 sm:px-6">
                        <div class="mb-12 max-w-2xl">
                            <flux:heading size="xl">{{ __('general.contact_section_heading') }}</flux:heading>
                            <flux:subheading class="mt-2">{{ __('general.contact_section_hint') }}</flux:subheading>
                        </div>

                        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                            @if (filled($contact->address))
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-teal-700 dark:text-teal-300">
                                        <flux:icon name="map-pin" variant="mini" class="size-5" />
                                        <flux:heading size="sm">{{ __('general.address') }}</flux:heading>
                                    </div>
                                    <flux:text>{{ $contact->address }}</flux:text>
                                    @if (filled($contact->postal_code))
                                        <flux:text size="sm" class="text-zinc-500">{{ __('general.postal_code') }}: {{ $contact->postal_code }}</flux:text>
                                    @endif
                                </div>
                            @endif

                            @if (! empty($contact->phone_numbers))
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-teal-700 dark:text-teal-300">
                                        <flux:icon name="phone" variant="mini" class="size-5" />
                                        <flux:heading size="sm">{{ __('general.phone_numbers') }}</flux:heading>
                                    </div>
                                    <ul class="space-y-1" dir="ltr">
                                        @foreach ($contact->phone_numbers as $phone)
                                            <li>
                                                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="font-mono text-sm text-zinc-700 transition hover:text-teal-700 dark:text-zinc-300 dark:hover:text-teal-300">
                                                    {{ $phone }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (filled($contact->support_email))
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-teal-700 dark:text-teal-300">
                                        <flux:icon name="mail" variant="mini" class="size-5" />
                                        <flux:heading size="sm">{{ __('general.support_email') }}</flux:heading>
                                    </div>
                                    <a href="mailto:{{ $contact->support_email }}" class="text-sm text-zinc-700 transition hover:text-teal-700 dark:text-zinc-300 dark:hover:text-teal-300" dir="ltr">
                                        {{ $contact->support_email }}
                                    </a>
                                </div>
                            @endif

                            @if (filled($contact->fax))
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-teal-700 dark:text-teal-300">
                                        <flux:icon name="printer" variant="mini" class="size-5" />
                                        <flux:heading size="sm">{{ __('general.fax') }}</flux:heading>
                                    </div>
                                    <flux:text class="font-mono" dir="ltr">{{ $contact->fax }}</flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            @if ($activeSocials->isNotEmpty())
                <section id="social" class="scroll-mt-24 border-t border-zinc-200/80 bg-white/60 py-20 backdrop-blur-sm dark:border-zinc-800/80 dark:bg-zinc-950/40">
                    <div class="mx-auto max-w-6xl px-4 sm:px-6">
                        <div class="mb-12 max-w-2xl">
                            <flux:heading size="xl">{{ __('general.social_section_heading') }}</flux:heading>
                            <flux:subheading class="mt-2">{{ __('general.social_section_hint') }}</flux:subheading>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @foreach ($activeSocials as $item)
                                <a
                                    href="{{ $item['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 rounded-xl bg-teal-50 px-4 py-3 text-sm font-medium text-teal-800 transition hover:bg-teal-100 dark:bg-teal-500/10 dark:text-teal-200 dark:hover:bg-teal-500/20"
                                >
                                    <flux:icon name="{{ $item['icon'] }}" variant="mini" class="size-5" />
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </main>

        <footer class="relative z-10 border-t border-zinc-200/80 bg-white/70 py-6 dark:border-zinc-800/80 dark:bg-zinc-950/50">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6">
                <div class="flex flex-col items-center gap-3 sm:items-start">
                    <flux:text size="sm" class="text-zinc-500">{{ $siteName }}</flux:text>
                    @if ($activeSocials->isNotEmpty())
                        <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                            @foreach ($activeSocials as $item)
                                <flux:tooltip content="{{ $item['label'] }}">
                                    <a
                                        href="{{ $item['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex size-9 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-teal-50 hover:text-teal-700 dark:hover:bg-teal-500/10 dark:hover:text-teal-300"
                                        aria-label="{{ $item['label'] }}"
                                    >
                                        <flux:icon name="{{ $item['icon'] }}" variant="mini" class="size-4" />
                                    </a>
                                </flux:tooltip>
                            @endforeach
                        </div>
                    @endif
                </div>
                @include('layouts.shared.theme')
            </div>
        </footer>
    </div>

    @include('layouts.shared.foot')
</body>
</html>
