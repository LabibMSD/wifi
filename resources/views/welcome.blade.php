@php($profile = config('business.public_profile'))

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ __('landing.meta.description', ['business' => config('business.name')]) }}">
        <meta name="theme-color" content="#f59e0b">

        <title>{{ __('landing.brand.name', ['business' => config('business.name')]) }} — {{ __('landing.brand.descriptor') }}</title>

        <script>
            (() => {
                const themePreferenceKey = 'eponet-theme';
                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                let theme = systemPrefersDark ? 'dark' : 'light';

                try {
                    const savedTheme = window.localStorage.getItem(themePreferenceKey);

                    if (savedTheme === 'light' || savedTheme === 'dark') {
                        theme = savedTheme;
                    }
                } catch (error) {
                    if (!(error instanceof DOMException) || error.name !== 'SecurityError') {
                        throw error;
                    }

                    console.warn('Eponet could not access the saved theme preference.', error);
                }

                document.documentElement.classList.toggle('dark', theme === 'dark');
                document.documentElement.style.colorScheme = theme;
            })();
        </script>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-stone-50 font-sans text-slate-950 antialiased selection:bg-amber-200 selection:text-slate-950 dark:bg-slate-950 dark:text-slate-100 dark:selection:bg-amber-400 dark:selection:text-slate-950">
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-stone-50/90 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/90">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-5 py-3.5 sm:px-8 lg:px-10">
                <a href="#beranda" class="flex shrink-0 items-center gap-3 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-500" aria-label="{{ __('landing.nav.home', ['business' => config('business.name')]) }}">
                    <img src="{{ asset('logo.png') }}" alt="{{ __('landing.brand.logo_alt', ['business' => config('business.name')]) }}" class="size-11 object-contain">
                    <span class="grid leading-tight">
                        <span class="text-xl font-bold tracking-tight text-slate-950 dark:text-white">{{ __('landing.brand.name', ['business' => config('business.name')]) }}</span>
                        <span class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ __('landing.brand.descriptor') }}</span>
                    </span>
                </a>

                <nav aria-label="{{ __('landing.nav.main') }}" class="order-3 flex w-full flex-wrap items-center justify-center gap-3 border-t border-slate-200 pt-3 text-[0.7rem] font-medium text-slate-600 dark:border-slate-800 dark:text-slate-300 sm:gap-5 sm:text-xs md:order-none md:w-auto md:gap-6 md:border-0 md:pt-0 md:text-sm">
                    <a href="#layanan" class="transition hover:text-amber-700 dark:hover:text-amber-300">{{ __('landing.nav.services') }}</a>
                    <a href="#tentang" class="transition hover:text-amber-700 dark:hover:text-amber-300">{{ __('landing.nav.about') }}</a>
                </nav>

                <div class="order-2 flex shrink-0 items-center md:order-none">
                    <button
                        type="button"
                        data-theme-toggle
                        data-label-light="{{ __('landing.theme.switch_to_light') }}"
                        data-label-dark="{{ __('landing.theme.switch_to_dark') }}"
                        aria-label="{{ __('landing.theme.toggle') }}"
                        title="{{ __('landing.theme.toggle') }}"
                        class="grid size-10 cursor-pointer place-items-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-sm transition hover:border-amber-400 hover:text-amber-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-500 dark:border-slate-700 dark:bg-slate-900 dark:text-amber-300 dark:hover:border-amber-400"
                    >
                        <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4 dark:hidden">
                            <circle cx="10" cy="10" r="3.3" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M10 2.5v1.3m0 12.4v1.3m7.5-7.5h-1.3M3.8 10H2.5m12.8-5.3-.9.9m-8.8 8.8-.9.9m12.8 0-.9-.9m-8.8-8.8-.9-.9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="hidden size-4 dark:block">
                            <path d="M16.4 12.4A7.2 7.2 0 0 1 7.6 3.6a7.3 7.3 0 1 0 8.8 8.8Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <main id="beranda">
            <section aria-labelledby="hero-title" class="relative isolate overflow-hidden bg-gradient-to-br from-amber-50 via-white to-sky-50 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900">
                <div aria-hidden="true" class="absolute -left-40 top-12 -z-10 size-96 rounded-full bg-amber-200/30 blur-3xl dark:bg-amber-500/10"></div>
                <div aria-hidden="true" class="absolute -right-36 bottom-0 -z-10 size-96 rounded-full bg-sky-200/30 blur-3xl dark:bg-sky-500/10"></div>

                <div class="mx-auto grid max-w-7xl items-center gap-14 px-5 pb-20 pt-16 sm:px-8 sm:pb-24 sm:pt-20 lg:grid-cols-[1.02fr_0.98fr] lg:gap-10 lg:px-10 lg:py-28">
                    <div class="max-w-2xl">
                        <p class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white/80 px-3.5 py-2 text-xs font-bold uppercase tracking-[0.15em] text-amber-800 shadow-sm dark:border-amber-400/20 dark:bg-slate-900/80 dark:text-amber-300">
                            <span class="size-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/15"></span>
                            {{ __('landing.hero.eyebrow') }}
                        </p>

                        <h1 id="hero-title" class="mt-7 max-w-xl text-4xl font-semibold leading-[1.08] tracking-[-0.045em] text-slate-950 sm:text-5xl lg:text-6xl dark:text-white">
                            {{ __('landing.hero.title') }}
                        </h1>
                        <p class="mt-6 max-w-xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8 dark:text-slate-300">
                            {{ __('landing.hero.description', ['business' => config('business.name')]) }}
                        </p>

                        <div class="mt-9 flex flex-wrap items-center gap-3">
                            <a href="#layanan" class="inline-flex items-center justify-center gap-2 rounded-full bg-amber-400 px-6 py-3.5 text-sm font-bold text-slate-950 shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 hover:bg-amber-300 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-600">
                                {{ __('landing.hero.primary_action') }}
                                <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4">
                                    <path d="M4.5 10h10m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <a href="#tentang" class="inline-flex items-center justify-center rounded-full px-5 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-white/70 hover:text-slate-950 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-500 dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-white">
                                {{ __('landing.hero.secondary_action', ['business' => config('business.name')]) }}
                            </a>
                        </div>

                        <div class="mt-9 flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400">
                            <div class="flex -space-x-2" aria-hidden="true">
                                <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-amber-100 text-amber-800 dark:border-slate-950 dark:bg-amber-400/20 dark:text-amber-300">
                                    <svg viewBox="0 0 20 20" fill="none" class="size-4"><path d="M10 3.2v2m0 9.6v2M3.2 10h2m9.6 0h2M5.2 5.2l1.4 1.4m6.8 6.8 1.4 1.4m0-9.6-1.4 1.4m-6.8 6.8-1.4 1.4M7 10a3 3 0 1 0 6 0 3 3 0 0 0-6 0Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                </span>
                                <span class="grid size-8 place-items-center rounded-full border-2 border-white bg-sky-100 text-sky-700 dark:border-slate-950 dark:bg-sky-400/20 dark:text-sky-300">
                                    <svg viewBox="0 0 20 20" fill="none" class="size-4"><path d="M3 7.5a10 10 0 0 1 14 0M5.8 10.3a6 6 0 0 1 8.4 0M8.6 13.1a2 2 0 0 1 2.8 0M10 16h.01" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                </span>
                            </div>
                            <span>{{ __('landing.hero.trust_note', ['business' => config('business.name')]) }}</span>
                        </div>
                    </div>

                    <div class="relative mx-auto w-full max-w-xl lg:ml-auto">
                        <div class="absolute -inset-4 rounded-[2rem] bg-gradient-to-br from-amber-300/30 via-sky-300/15 to-transparent blur-2xl dark:from-amber-400/15 dark:via-sky-400/10"></div>
                        <div class="relative overflow-hidden rounded-[1.75rem] border border-slate-700/80 bg-slate-950 p-5 text-white shadow-2xl shadow-slate-950/20 sm:p-7">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ __('landing.hero.visual_eyebrow') }}</p>
                                    <p class="mt-2 text-lg font-semibold tracking-tight sm:text-xl">{{ __('landing.hero.visual_title') }}</p>
                                </div>
                                <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-amber-400 text-slate-950">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-6">
                                        <path d="M3.5 9.5a13.2 13.2 0 0 1 17 0M6.7 12.7a8.3 8.3 0 0 1 10.6 0M9.8 15.8a3.7 3.7 0 0 1 4.4 0M12 19h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                            </div>

                            <div class="relative mt-8 flex aspect-[1.35] items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800">
                                <div aria-hidden="true" class="absolute inset-0 opacity-30" style="background-image: radial-gradient(#94a3b8 0.8px, transparent 0.8px); background-size: 22px 22px;"></div>
                                <svg aria-hidden="true" viewBox="0 0 480 300" fill="none" class="absolute inset-0 size-full">
                                    <path d="M240 147 115 83m125 64 126-71m-126 71-145 76m145-76 147 85m-147-85v106" stroke="#64748b" stroke-width="1.5" stroke-dasharray="5 7"/>
                                    <path d="M115 83 80 46m35 37-8 44m134 20 25-53m101-35 42 13m-42-13-3-47M95 218l-30 30m30-30-5-41m296 55 27 31m-27-31 13-38M240 253l-39 24m39-24 39 24" stroke="#475569" stroke-width="1.4" stroke-dasharray="3 6"/>
                                    <circle cx="240" cy="147" r="52" fill="#f59e0b" fill-opacity=".08"/>
                                    <circle cx="240" cy="147" r="38" fill="#f59e0b" fill-opacity=".12"/>
                                    <circle cx="240" cy="147" r="25" fill="#fbbf24"/>
                                    <path d="M228 149a17 17 0 0 1 24 0m-19-5a10 10 0 0 1 14 0m-9 10h4" stroke="#0f172a" stroke-width="2.3" stroke-linecap="round"/>
                                    <circle cx="115" cy="83" r="12" fill="#38bdf8"/>
                                    <circle cx="366" cy="76" r="12" fill="#38bdf8"/>
                                    <circle cx="95" cy="218" r="12" fill="#fb923c"/>
                                    <circle cx="389" cy="233" r="12" fill="#4ade80"/>
                                    <circle cx="240" cy="253" r="12" fill="#a78bfa"/>
                                    <circle cx="80" cy="46" r="6" fill="#94a3b8"/>
                                    <circle cx="107" cy="127" r="6" fill="#94a3b8"/>
                                    <circle cx="265" cy="94" r="6" fill="#94a3b8"/>
                                    <circle cx="408" cy="89" r="6" fill="#94a3b8"/>
                                    <circle cx="363" cy="195" r="6" fill="#94a3b8"/>
                                    <circle cx="65" cy="248" r="6" fill="#94a3b8"/>
                                    <circle cx="90" cy="177" r="6" fill="#94a3b8"/>
                                    <circle cx="416" cy="264" r="6" fill="#94a3b8"/>
                                    <circle cx="201" cy="277" r="6" fill="#94a3b8"/>
                                    <circle cx="279" cy="277" r="6" fill="#94a3b8"/>
                                </svg>

                                <div class="absolute left-3 top-3 rounded-xl border border-white/10 bg-slate-950/85 px-3 py-2 shadow-lg backdrop-blur sm:left-5 sm:top-5">
                                    <p class="text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400">{{ __('landing.hero.internet_label') }}</p>
                                    <p class="mt-0.5 text-sm font-bold text-white">{{ \Illuminate\Support\Number::format($profile['customers']['internet'], locale: app()->getLocale()) }}</p>
                                </div>
                                <div class="absolute bottom-3 right-3 rounded-xl border border-white/10 bg-slate-950/85 px-3 py-2 shadow-lg backdrop-blur sm:bottom-5 sm:right-5">
                                    <p class="text-[0.6rem] font-semibold uppercase tracking-wider text-slate-400">{{ __('landing.hero.television_label') }}</p>
                                    <p class="mt-0.5 text-sm font-bold text-white">{{ \Illuminate\Support\Number::format($profile['customers']['digital_tv'], locale: app()->getLocale()) }}</p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-4">
                                <p class="text-xs leading-5 text-slate-400">{{ __('landing.hero.visual_caption') }}</p>
                                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/10 px-3 py-1.5 text-xs font-semibold text-emerald-300">
                                    <span class="size-1.5 rounded-full bg-emerald-400"></span>
                                    {{ __('landing.hero.connected_label', ['business' => config('business.name')]) }}
                                </span>
                            </div>
                        </div>
                        <div class="absolute -bottom-5 -left-3 hidden items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-xl shadow-slate-900/10 sm:flex dark:border-slate-700 dark:bg-slate-900">
                            <span class="grid size-10 place-items-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-400/15 dark:text-sky-300">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-5"><path d="M4 5.5h16v11H4zM9 20h6m-3-3.5V20m-5-11h4m-4 3h7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span>
                                <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('landing.hero.digital_tv_label') }}</span>
                                <span class="mt-0.5 block text-sm font-bold text-slate-900 dark:text-white">{{ __('landing.hero.digital_tv_caption') }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="layanan" aria-labelledby="services-title" class="scroll-mt-24 py-20 sm:py-24 lg:py-28">
                <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">{{ __('landing.services.eyebrow', ['business' => config('business.name')]) }}</p>
                        <h2 id="services-title" class="mt-4 text-3xl font-semibold tracking-[-0.035em] text-slate-950 sm:text-4xl dark:text-white">{{ __('landing.services.heading') }}</h2>
                        <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-300">{{ __('landing.services.description', ['business' => config('business.name')]) }}</p>
                    </div>

                    <div class="mt-12 grid gap-5 md:grid-cols-3">
                        <article class="group rounded-3xl border border-amber-200/80 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-950/5 dark:border-slate-800 dark:bg-slate-900 dark:hover:shadow-black/20">
                            <span class="grid size-12 place-items-center rounded-2xl bg-amber-100 text-amber-800 transition group-hover:bg-amber-400 group-hover:text-slate-950 dark:bg-amber-400/15 dark:text-amber-300 dark:group-hover:bg-amber-400">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-6"><path d="M3.5 9.5a13.2 13.2 0 0 1 17 0M6.7 12.7a8.3 8.3 0 0 1 10.6 0M9.8 15.8a3.7 3.7 0 0 1 4.4 0M12 19h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </span>
                            <h3 class="mt-6 text-lg font-semibold text-slate-950 dark:text-white">{{ __('landing.services.internet_title') }}</h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('landing.services.internet_description') }}</p>
                            <span class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                <span class="size-1.5 rounded-full bg-amber-500"></span>{{ __('landing.services.internet_tag') }}
                            </span>
                        </article>

                        <article class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-950/5 dark:border-slate-800 dark:bg-slate-900 dark:hover:shadow-black/20">
                            <span class="grid size-12 place-items-center rounded-2xl bg-sky-100 text-sky-700 transition group-hover:bg-sky-500 group-hover:text-white dark:bg-sky-400/15 dark:text-sky-300 dark:group-hover:bg-sky-500">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-6"><path d="M3 5.5h18v12H3zM8 21h8m-4-3.5V21m-5-11h5m-5 3.5h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <h3 class="mt-6 text-lg font-semibold text-slate-950 dark:text-white">{{ __('landing.services.digital_title') }}</h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('landing.services.digital_description') }}</p>
                            <span class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-sky-800 dark:text-sky-300">
                                <span class="size-1.5 rounded-full bg-sky-500"></span>{{ __('landing.services.digital_tag') }}
                            </span>
                        </article>

                        <article class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-950/5 dark:border-slate-800 dark:bg-slate-900 dark:hover:shadow-black/20">
                            <span class="grid size-12 place-items-center rounded-2xl bg-orange-100 text-orange-700 transition group-hover:bg-orange-500 group-hover:text-white dark:bg-orange-400/15 dark:text-orange-300 dark:group-hover:bg-orange-500">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-6"><path d="M4 8.5h16v10H4zM8 5h8m-6 3.5v5m4-5v5m-5 5h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <h3 class="mt-6 text-lg font-semibold text-slate-950 dark:text-white">{{ __('landing.services.analog_title') }}</h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('landing.services.analog_description', ['business' => config('business.name')]) }}</p>
                            <span class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-orange-800 dark:text-orange-300">
                                <span class="size-1.5 rounded-full bg-orange-500"></span>{{ __('landing.services.analog_tag') }}
                            </span>
                        </article>
                    </div>
                </div>
            </section>

            <section id="tentang" aria-labelledby="about-title" class="scroll-mt-24 border-y border-slate-200 bg-white py-20 dark:border-slate-800 dark:bg-slate-900/50 sm:py-24 lg:py-28">
                <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">
                    <div class="grid items-start gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">{{ __('landing.about.eyebrow', ['business' => config('business.name')]) }}</p>
                            <h2 id="about-title" class="mt-4 max-w-xl text-3xl font-semibold leading-tight tracking-[-0.035em] text-slate-950 sm:text-4xl dark:text-white">{{ __('landing.about.heading') }}</h2>
                            <p class="mt-5 max-w-xl text-base leading-7 text-slate-600 dark:text-slate-300">{{ __('landing.about.description', ['business' => config('business.name')]) }}</p>
                            <p class="mt-4 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400">{{ __('landing.about.detail') }}</p>
                            <a href="#layanan" class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-sky-800 transition hover:text-sky-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-sky-500 dark:text-sky-300 dark:hover:text-sky-200">
                                {{ __('landing.about.services_link') }}
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>

                        <div class="rounded-3xl bg-slate-950 p-6 text-white shadow-2xl shadow-slate-900/10 sm:p-8">
                            <div class="flex items-center justify-between gap-4 border-b border-white/10 pb-5">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.17em] text-amber-300">{{ __('landing.about.panel_eyebrow', ['business' => config('business.name')]) }}</p>
                                    <h3 class="mt-2 text-lg font-semibold">{{ __('landing.about.panel_heading') }}</h3>
                                </div>
                                <span class="grid size-11 place-items-center rounded-2xl bg-sky-400/15 text-sky-300">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" class="size-5"><path d="M4 6.5h16v13H4zM8 6.5V4h8v2.5m-9 5h10m-10 4h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                            <dl class="mt-2 divide-y divide-white/10">
                                <div class="flex items-center gap-4 py-4">
                                    <dt class="flex items-center gap-3 text-sm font-medium text-slate-200">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-amber-400/15 text-amber-300">
                                            <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4"><path d="M3.5 8a10 10 0 0 1 13 0m-10.4 2.8a6 6 0 0 1 7.8 0M8.5 13.5a2.3 2.3 0 0 1 3 0M10 16h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        </span>
                                        {{ __('landing.about.internet_customers') }}
                                    </dt>
                                    <dd class="ml-auto text-lg font-semibold text-white">{{ \Illuminate\Support\Number::format($profile['customers']['internet'], locale: app()->getLocale()) }}</dd>
                                </div>
                                <div class="flex items-center gap-4 py-4">
                                    <dt class="flex items-center gap-3 text-sm font-medium text-slate-200">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-sky-400/15 text-sky-300">
                                            <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4"><path d="M3 4.5h14v10H3zM7 17h6m-3-2.5V17m-4-9h3m-3 3h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                        {{ __('landing.about.digital_customers') }}
                                    </dt>
                                    <dd class="ml-auto text-lg font-semibold text-white">{{ \Illuminate\Support\Number::format($profile['customers']['digital_tv'], locale: app()->getLocale()) }}</dd>
                                </div>
                                <div class="flex items-center gap-4 py-4">
                                    <dt class="flex items-center gap-3 text-sm font-medium text-slate-200">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-orange-400/15 text-orange-300">
                                            <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4"><path d="M3 5h14v10H3zm3 3h8m-8 3h5m-5 2h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                        {{ __('landing.about.analog_customers') }}
                                    </dt>
                                    <dd class="ml-auto text-lg font-semibold text-white">{{ \Illuminate\Support\Number::format($profile['customers']['analog_tv'], locale: app()->getLocale()) }}</dd>
                                </div>
                            </dl>
                            <p class="mt-3 rounded-xl bg-white/[0.06] px-4 py-3 text-xs leading-5 text-slate-400">{{ __('landing.about.snapshot_note') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section aria-labelledby="cta-title" class="px-5 py-16 sm:px-8 sm:py-20 lg:px-10">
                <div class="relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-amber-400 px-6 py-12 sm:px-12 sm:py-14 lg:px-16">
                    <div aria-hidden="true" class="absolute -right-16 -top-32 size-80 rounded-full border-[36px] border-white/15"></div>
                    <div aria-hidden="true" class="absolute -bottom-36 right-40 size-64 rounded-full border-[28px] border-slate-950/5"></div>
                    <div class="relative flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">
                        <div class="max-w-2xl">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-950/70">{{ __('landing.cta.eyebrow', ['business' => config('business.name')]) }}</p>
                            <h2 id="cta-title" class="mt-3 text-3xl font-semibold tracking-[-0.035em] text-slate-950 sm:text-4xl">{{ __('landing.cta.heading') }}</h2>
                            <p class="mt-4 max-w-xl text-sm leading-6 text-slate-900/75 sm:text-base">{{ __('landing.cta.description', ['business' => config('business.name')]) }}</p>
                        </div>
                        <a href="#layanan" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-slate-950 px-6 py-3.5 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950">
                            {{ __('landing.cta.action') }}
                            <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-4"><path d="M4.5 10h10m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-6 sm:px-8 lg:px-10">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="#beranda" class="flex items-center gap-3">
                        <img src="{{ asset('logo.png') }}" alt="" class="size-9 object-contain">
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ __('landing.brand.name', ['business' => config('business.name')]) }}</span>
                    </a>
                    <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">{{ __('landing.footer.copyright', ['year' => now()->year, 'brand' => __('landing.brand.name', ['business' => config('business.name')])]) }}</p>
                </div>
                <div class="border-t border-slate-100 pt-4 text-center dark:border-slate-800">
                    <a href="{{ route('filament.admin.auth.login') }}" class="text-[11px] text-slate-500 transition hover:text-slate-700 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-500 dark:text-slate-400 dark:hover:text-slate-300">{{ __('landing.nav.admin') }}</a>
                </div>
            </div>
        </footer>
    </body>
</html>
