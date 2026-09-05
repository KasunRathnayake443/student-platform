@php
    $floatingStats = [
        ['emoji' => '⭐', 'label' => 'Kid Dashboard', 'sub' => 'Stars & Missions', 'grad' => 'from-violet-500 to-purple-600'],
        ['emoji' => '🔥', 'label' => 'Junior Dashboard', 'sub' => 'Streaks & Progress', 'grad' => 'from-indigo-500 to-blue-600'],
        ['emoji' => '📈', 'label' => 'Senior Dashboard', 'sub' => 'Analytics & Trends', 'grad' => 'from-slate-600 to-purple-700'],
    ];
    $roleLinks = [
        ['label' => 'Student Portal', 'href' => '/student/login', 'icon' => 'academic-cap'],
        ['label' => 'Teacher Portal', 'href' => '/teacher/login', 'icon' => 'book-open'],
        ['label' => 'School Admin', 'href' => '/admin/login', 'icon' => 'building-office'],
    ];
    $heroKpis = [
        ['label' => 'AVG SCORE', 'value' => '60.1%', 'sub' => '3 quizzes passed', 'border' => 'border-t-2 border-purple-400'],
        ['label' => 'ASSIGNMENTS', 'value' => '6/6', 'sub' => '0 pending', 'border' => 'border-t-2 border-blue-400'],
        ['label' => 'ACTIVE CLASSES', 'value' => '2', 'sub' => 'Grade 10', 'border' => 'border-t-2 border-green-400'],
        ['label' => 'QUIZ ATTEMPTS', 'value' => '7', 'sub' => '3 days ago', 'border' => 'border-t-2 border-amber-400'],
    ];
@endphp

<section class="relative min-h-screen flex items-center overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 30%, #4c1d95 60%, #6d28d9 85%, #7c3aed 100%);" aria-label="Hero">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full bg-purple-400/20 blur-3xl animate-pulse" style="animation-duration:4s;"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 rounded-full bg-violet-300/15 blur-3xl animate-pulse" style="animation-duration:6s;animation-delay:1s;"></div>
        <div class="absolute top-1/2 right-1/3 w-64 h-64 rounded-full bg-pink-400/10 blur-3xl animate-pulse" style="animation-duration:5s;animation-delay:2s;"></div>
        <div class="absolute inset-0 opacity-10" style="background-image:linear-gradient(rgba(255,255,255,.1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.1) 1px,transparent 1px);background-size:60px 60px;"></div>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-6 py-24 md:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            <div id="hero-left">
                <div class="reveal hero-animate mb-6">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-purple-200 text-sm font-semibold tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-purple-300 animate-pulse"></span>
                        Complete Learning Management System
                    </span>
                </div>

                <h1 class="reveal hero-animate font-display text-hero-xl font-semibold text-white leading-[1.1] mb-6">
                    The LMS that <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-300 to-pink-300">grows with every student.</span>
                </h1>

                <p class="reveal hero-animate text-white/75 text-lg leading-relaxed mb-8 max-w-xl font-light">
                    Three adaptive dashboards for Kids, Juniors, and Seniors — automatically assigned by age. Schools create their own space, manage teachers, build grades and classes, and track every student's progress.
                </p>

                <div class="reveal hero-animate flex flex-wrap gap-2 mb-10">
                    @foreach($floatingStats as $s)
                        <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r {{ $s['grad'] }} bg-opacity-80 border border-white/10">
                            <span class="text-base">{{ $s['emoji'] }}</span>
                            <div>
                                <p class="text-white text-xs font-semibold leading-none">{{ $s['label'] }}</p>
                                <p class="text-white/60 text-xs leading-none mt-0.5">{{ $s['sub'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="reveal hero-animate flex flex-col sm:flex-row gap-4 mb-10">
                    <a href="#portals" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-purple-800 font-semibold text-base rounded-xl hover:bg-purple-50 transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                        Access Your Portal
                        @include('partials.landing.icon', ['name' => 'arrow-right', 'size' => 18])
                    </a>
                    <a href="#dashboards" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-medium text-base rounded-xl border border-white/20 hover:bg-white/20 transition-all duration-200">
                        See Dashboards
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

            <div class="reveal hero-animate hidden lg:block">
                <div class="relative">
                    <div class="absolute inset-0 bg-purple-400/20 blur-3xl rounded-3xl"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden border border-white/20">
                        <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-bold text-gray-800 text-sm">Liam</p>
                                <p class="text-xs text-gray-400">Saturday, September 5, 2026</p>
                            </div>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-purple-100 text-purple-700 font-medium">Horizon International Academy › Grade 10</span>
                        </div>
                        <div class="grid grid-cols-4 gap-0">
                            @foreach($heroKpis as $i => $k)
                                <div class="p-4 {{ $i < 3 ? 'border-r border-gray-100' : '' }} {{ $k['border'] }}">
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">{{ $k['label'] }}</p>
                                    <p class="font-bold text-gray-800 text-xl">{{ $k['value'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $k['sub'] }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="p-5">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">PERFORMANCE TREND · QUIZ SCORES</p>
                            <div class="h-14 flex items-end gap-1">
                                @foreach([1, 1, 1, 1, 1, 1, 1, 9] as $i => $h)
                                    <div class="flex-1 rounded-t" style="height:{{ $h * 6 }}px; background:{{ $i === 7 ? '#7c3aed' : '#ede9fe' }};"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="absolute -top-4 -right-4 bg-gradient-to-br from-violet-500 to-pink-500 rounded-2xl p-4 shadow-xl border border-white/20 w-44">
                        <p class="text-white font-bold text-sm">Hi Liam! 🌟</p>
                        <p class="text-white/70 text-xs mt-0.5">Ready for today's adventures?</p>
                        <div class="flex gap-0.5 mt-2">
                            @for($i = 0; $i < 6; $i++)<span class="text-yellow-300 text-sm">★</span>@endfor
                            @for($i = 0; $i < 4; $i++)<span class="text-white/30 text-sm">★</span>@endfor
                        </div>
                        <p class="text-white/60 text-xs mt-1">6 / 10 stars</p>
                    </div>

                    <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl p-4 shadow-xl border border-gray-100 w-44">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl">🔥</span>
                            <span class="font-bold text-gray-800 text-sm">2-day streak!</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1.5 rounded-full w-3/4"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Progress: 100% done</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-8 left-1/2 scroll-bounce z-10 hidden md:block">
        @include('partials.landing.icon', ['name' => 'chevron-down', 'size' => 28, 'iconClass' => 'text-white/50'])
    </div>
</section>