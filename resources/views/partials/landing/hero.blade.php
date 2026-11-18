@php
    $floatingStats = [
        ['emoji' => '⭐', 'label' => __('landing.hero.kid_title'), 'sub' => __('landing.hero.kid_sub'), 'grad' => 'from-violet-500 to-purple-600'],
        ['emoji' => '🔥', 'label' => __('landing.hero.junior_title'), 'sub' => __('landing.hero.junior_sub'), 'grad' => 'from-indigo-500 to-blue-600'],
        ['emoji' => '📈', 'label' => __('landing.hero.senior_title'), 'sub' => __('landing.hero.senior_sub'), 'grad' => 'from-slate-600 to-purple-700'],
    ];
    $roleLinks = [
        ['label' => __('landing.hero.role_student'), 'href' => '/student/login', 'icon' => 'academic-cap'],
        ['label' => __('landing.hero.role_teacher'), 'href' => '/teacher/login', 'icon' => 'book-open'],
        ['label' => __('landing.hero.role_admin'), 'href' => '/school-admin/login', 'icon' => 'building-office'],
    ];
    $heroKpis = collect(__('landing.hero.mockup.kpi'))
        ->map(fn ($kpi, $i) => $kpi + ['border' => [
            'border-t-2 border-purple-400',
            'border-t-2 border-blue-400',
            'border-t-2 border-green-400',
            'border-t-2 border-amber-400',
        ][$i]])
        ->all();
@endphp

<section class="relative min-h-screen flex items-center overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 30%, #4c1d95 60%, #6d28d9 85%, #7c3aed 100%);" aria-label="Hero">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full bg-purple-400/20 blur-3xl animate-pulse" style="animation-duration:4s;"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 rounded-full bg-violet-300/15 blur-3xl animate-pulse" style="animation-duration:6s;animation-delay:1s;"></div>
        <div class="absolute top-1/2 right-1/3 w-64 h-64 rounded-full bg-pink-400/10 blur-3xl animate-pulse" style="animation-duration:5s;animation-delay:2s;"></div>
        <div class="absolute inset-0 opacity-10" style="background-image:linear-gradient(rgba(255,255,255,.1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.1) 1px,transparent 1px);background-size:60px 60px;"></div>
    </div>

    <div class="relative z-10 w-full max-w-[1440px] mx-auto px-4 lg:px-8 py-24 md:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            <div id="hero-left">
                <h1 class="reveal hero-animate font-display text-hero-xl font-semibold text-white leading-[1.1] mb-6">
                    {{ __('landing.hero.title_prefix') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-300 to-pink-300">{{ __('landing.hero.title_highlight') }}</span>
                </h1>

                <p class="reveal hero-animate text-white/75 text-lg leading-relaxed mb-10 max-w-xl font-light">
                    {{ __('landing.hero.description') }}
                </p>

                <div class="reveal hero-animate flex flex-nowrap gap-2 mt-2 mb-10">
                    @foreach($floatingStats as $s)
                        <div class="inline-flex items-center gap-2.5 px-3 py-2.5 rounded-2xl bg-gradient-to-r {{ $s['grad'] }} bg-opacity-80 border border-white/10 shadow-md">
                            <span class="text-2xl leading-none">{{ $s['emoji'] }}</span>
                            <div>
                                <p class="text-white text-sm font-semibold leading-tight whitespace-nowrap">{{ $s['label'] }}</p>
                                <p class="text-white/65 text-xs leading-tight mt-1 whitespace-nowrap">{{ $s['sub'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="reveal hero-animate flex flex-col sm:flex-row gap-4 mb-10">
                    <a href="#portals" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-purple-800 font-semibold text-base rounded-xl hover:bg-purple-50 transition-colors duration-200 shadow-lg">
                        {{ __('landing.hero.access_portal') }}
                        @include('partials.landing.icon', ['name' => 'arrow-right', 'size' => 18])
                    </a>
                    <a href="#dashboards" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-medium text-base rounded-xl border border-white/20 hover:bg-white/20 transition-all duration-200">
                        {{ __('landing.hero.see_dashboards') }}
                        @include('partials.landing.icon', ['name' => 'chevron-down', 'size' => 18])
                    </a>
                </div>

                <div class="reveal hero-animate flex flex-wrap gap-3">
                    @foreach($roleLinks as $role)
                        <a href="{{ $role['href'] }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white/10 backdrop-blur-sm border border-white/20 text-white/90 text-sm font-medium hover:bg-white/20 hover:border-purple-300/50 hover:text-purple-200 transition-all duration-200 min-h-[44px]">
                            @include('partials.landing.icon', ['name' => $role['icon'], 'size' => 16])
                            {{ $role['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <a href="#dashboards" aria-label="Explore the dashboards" class="group reveal hero-animate hidden lg:block lg:scale-110 origin-center cursor-pointer rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-300">
                <div class="relative transition-transform duration-300 ease-out group-hover:-translate-y-2">
                    <div class="absolute inset-0 bg-purple-400/20 blur-3xl rounded-3xl"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden border border-white/20 transition-shadow duration-300 group-hover:ring-2 group-hover:ring-purple-200">
                        <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-bold text-gray-800 text-sm">{{ __('landing.showcase.dashboards.senior.hello') }}</p>
                                <p class="text-xs text-gray-400">{{ __('landing.showcase.dashboards.senior.sub') }}</p>
                            </div>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-purple-100 text-purple-700 font-medium">{{ __('landing.showcase.dashboards.senior.badge') }}</span>
                        </div>
                        <div class="grid grid-cols-4 gap-0">
                            @foreach($heroKpis as $i => $k)
                                <div class="p-4 transition-colors duration-200 hover:bg-purple-50/60 {{ $i < 3 ? 'border-r border-gray-100' : '' }} {{ $k['border'] }}">
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">{{ $k['label'] }}</p>
                                    <p class="font-bold text-gray-800 text-xl">{{ $k['value'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $k['sub'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="p-6">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">{{ __('landing.hero.mockup.chart') }}</p>
                            <div class="h-20 flex items-end gap-1.5 border-b border-gray-100 pb-1">
                                @foreach([42, 58, 35, 70, 52, 78, 64, 92] as $i => $h)
                                    <div class="flex-1 rounded-t-md transition-all duration-300 hover:opacity-80 hover:-translate-y-0.5" style="height:{{ $h }}%; background:{{ $i === 7 ? '#7c3aed' : '#ddd6fe' }};" title="{{ __('landing.hero.mockup.quiz_label') }} {{ $i + 1 }}: {{ $h }}%"></div>
                                @endforeach
                            </div>
                            <div class="flex justify-between mt-2 text-[10px] text-gray-400">
                                <span>{{ __('landing.hero.mockup.quiz1') }}</span>
                                <span>{{ __('landing.hero.mockup.quiz8') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -top-4 -right-4 bg-gradient-to-br from-violet-500 to-pink-500 rounded-2xl p-4 shadow-xl border border-white/20 w-44 transition-transform duration-300 group-hover:-translate-y-1">
                        <p class="text-white font-bold text-sm">{{ __('landing.hero.mockup.hello') }}</p>
                        <p class="text-white/70 text-xs mt-0.5">{{ __('landing.hero.mockup.ready') }}</p>
                        <div class="flex gap-0.5 mt-2">
                            @for($i = 0; $i < 6; $i++)<span class="text-yellow-300 text-sm">★</span>@endfor
                            @for($i = 0; $i < 4; $i++)<span class="text-white/30 text-sm">★</span>@endfor
                        </div>
                        <p class="text-white/60 text-xs mt-1">{{ __('landing.hero.mockup.stars') }}</p>
                    </div>

                    <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl p-4 shadow-xl border border-gray-100 w-44 transition-transform duration-300 group-hover:translate-y-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl">🔥</span>
                            <span class="font-bold text-gray-800 text-sm">{{ __('landing.hero.mockup.streak') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1.5 rounded-full w-3/4"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('landing.hero.mockup.progress') }}</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="absolute bottom-8 left-1/2 scroll-bounce z-10 hidden md:block">
        @include('partials.landing.icon', ['name' => 'chevron-down', 'size' => 28, 'iconClass' => 'text-white/50'])
    </div>
</section>