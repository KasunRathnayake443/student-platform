@php
    $currentLocale = app()->getLocale();
    $languages = [
        'en' => 'English',
        'si' => 'සිංහල',
        'ta' => 'தமிழ்',
    ];
@endphp

<header id="landing-header" class="fixed top-0 left-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-20 sm:h-24 md:h-28">
            <a href="/" class="flex min-w-0 items-center gap-2.5 sm:gap-3 group">
                <img src="{{ \App\Services\PlatformSettings::logoUrl() }}" alt="{{ \App\Services\PlatformSettings::name() }}" class="w-12 h-12 sm:w-16 sm:h-16 shrink-0 rounded-xl object-cover shadow-sm group-hover:scale-105 transition-transform duration-200">
                <span class="flex min-w-0 flex-col">
                    <span class="font-display font-semibold text-lg sm:text-2xl text-white tracking-tight leading-tight">{{ \App\Services\PlatformSettings::name() }}</span>
                    <span class="mt-0.5 text-[10px] sm:text-xs font-semibold uppercase tracking-[0.08em] sm:tracking-widest text-teal-300 leading-tight">{{ __('landing.header.tagline') }}</span>
                </span>
            </a>

            <nav class="hidden lg:flex items-center gap-8">
                <a href="#features" class="text-base font-medium text-white/80 hover:text-white transition-colors duration-200">{{ __('landing.header.features') }}</a>
                <a href="#portals" class="text-base font-medium text-white/80 hover:text-white transition-colors duration-200">{{ __('landing.header.portals') }}</a>
                <a href="#about" class="text-base font-medium text-white/80 hover:text-white transition-colors duration-200">{{ __('landing.header.about') }}</a>
                <a href="#contact" class="text-base font-medium text-white/80 hover:text-white transition-colors duration-200">{{ __('landing.contact.nav') }}</a>
            </nav>

            <div class="hidden lg:flex items-center gap-3">
                <details class="relative">
                    <summary class="flex items-center gap-1.5 list-none cursor-pointer px-3 py-2 rounded-lg text-sm font-semibold text-white/90 hover:text-teal-300 hover:bg-white/10 transition-colors duration-200 [&::-webkit-details-marker]:hidden">
                        <span class="text-base leading-none">🌐</span>
                        <span>{{ $languages[$currentLocale] ?? 'English' }}</span>
                        <svg class="w-3 h-3 opacity-70" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                    </summary>
                    <div class="absolute right-0 mt-2 w-40 rounded-xl bg-white py-2 shadow-xl ring-1 ring-slate-200 z-50">
                        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-400">{{ __('landing.header.language') }}</p>
                        @foreach($languages as $code => $label)
                            <a href="{{ route('locale.switch', $code) }}"
                               class="block px-4 py-2 text-sm transition-colors {{ $currentLocale === $code ? 'bg-teal-50 font-semibold text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-teal-600' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </details>

                <a href="/student/login" class="text-base font-medium text-white/80 hover:text-teal-300 transition-colors duration-200">{{ __('landing.header.sign_in') }}</a>
                <a href="#portals" class="px-5 py-2.5 bg-teal-500 text-ink font-semibold text-base rounded-lg hover:bg-teal-400 transition-all duration-200">{{ __('landing.header.get_started') }}</a>
            </div>

            <button id="menu-btn" class="lg:hidden flex flex-col gap-1.5 p-2 shrink-0 text-white" aria-label="Open menu" aria-expanded="false">
                <span class="block w-6 h-0.5 bg-white rounded"></span>
                <span class="block w-6 h-0.5 bg-white rounded"></span>
                <span class="block w-6 h-0.5 bg-white rounded"></span>
            </button>
        </div>
    </div>
</header>

<div id="mobile-menu" class="lg:hidden fixed inset-x-0 bottom-0 top-20 sm:top-24 md:top-28 z-40 backdrop-blur-xl flex flex-col items-center justify-start gap-7 px-5 py-8 sm:px-8 overflow-y-auto overscroll-contain">
    <a href="#features" class="text-2xl font-medium text-white hover:text-teal-300 transition-colors duration-200">{{ __('landing.header.features') }}</a>
    <a href="#portals" class="text-2xl font-medium text-white hover:text-teal-300 transition-colors duration-200">{{ __('landing.header.portals') }}</a>
    <a href="#about" class="text-2xl font-medium text-white hover:text-teal-300 transition-colors duration-200">{{ __('landing.header.about') }}</a>
    <a href="#contact" class="text-2xl font-medium text-white hover:text-teal-300 transition-colors duration-200">{{ __('landing.contact.nav') }}</a>

    <div class="flex flex-wrap items-center justify-center gap-2 w-full max-w-xs">
        @foreach($languages as $code => $label)
            <a href="{{ route('locale.switch', $code) }}"
               class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $currentLocale === $code ? 'bg-teal-400 text-ink' : 'border border-white/30 text-white/90 hover:border-teal-300 hover:text-teal-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="flex flex-col gap-4 w-full max-w-xs mt-2">
        <a href="/student/login" class="text-center py-3.5 border border-white/30 rounded-lg text-white font-medium text-lg hover:border-teal-300 hover:text-teal-300 transition-colors duration-200">{{ __('landing.header.sign_in') }}</a>
        <a href="#portals" class="text-center py-3.5 bg-teal-500 rounded-lg text-ink font-semibold text-lg hover:bg-teal-400 transition-colors duration-200">{{ __('landing.header.get_started') }}</a>
    </div>
</div>
