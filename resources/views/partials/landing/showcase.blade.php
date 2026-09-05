@php
    $dashboards = [
        [
            'id'        => 'kid',
            'label'     => 'Kid',
            'ageRange'  => 'Ages 5–10',
            'emoji'     => '🌟',
            'tagline'   => 'Gamified & Fun',
            'description' => 'Stars, missions, and colorful adventures make learning feel like play. Kids earn stars for completing lessons and unlock new challenges.',
            'grad'      => 'linear-gradient(135deg,#8b5cf6 0%,#a855f7 50%,#ec4899 100%)',
            'glow'      => 'from-violet-500 via-purple-500 to-pink-500',
            'bottomBar' => 'from-violet-500 via-purple-500 to-pink-400',
            'badge'     => 'bg-purple-100 text-purple-700',
            'accent'    => 'text-purple-600',
            'chipBg'    => 'bg-purple-50',
            'chipBorder'=> 'border-purple-300',
            'headerBg'  => 'bg-gradient-to-r from-violet-500 via-purple-500 to-pink-400',
            'headerTitleCls' => 'text-white',
            'headerSubCls'   => 'text-white/70',
            'stats'     => [
                ['icon' => '⭐', 'label' => 'My Stars', 'value' => '6 / 10', 'color' => 'text-yellow-500'],
                ['icon' => '🎯', 'label' => 'Missions Done', 'value' => '3 / 3', 'color' => 'text-green-500'],
                ['icon' => '📚', 'label' => 'Classes', 'value' => '4', 'color' => 'text-purple-500'],
            ],
            'highlights' => ['Star rating system', 'Daily missions', 'Colorful grade tabs', 'Fun achievement badges'],
        ],
        [
            'id'        => 'junior',
            'label'     => 'Junior',
            'ageRange'  => 'Ages 11–14',
            'emoji'     => '🔥',
            'tagline'   => 'Streaks & Progress',
            'description' => 'Daily streaks, quiz averages, and progress rings keep middle schoolers motivated. Track classes, upcoming deadlines, and recent quiz results.',
            'grad'      => 'linear-gradient(135deg,#3b82f6 0%,#6366f1 50%,#8b5cf6 100%)',
            'glow'      => 'from-blue-500 via-indigo-500 to-purple-500',
            'bottomBar' => 'from-blue-500 via-indigo-500 to-purple-500',
            'badge'     => 'bg-indigo-100 text-indigo-700',
            'accent'    => 'text-indigo-600',
            'chipBg'    => 'bg-indigo-50',
            'chipBorder'=> 'border-indigo-300',
            'headerBg'  => 'bg-gradient-to-r from-slate-800 to-slate-700',
            'headerTitleCls' => 'text-white',
            'headerSubCls'   => 'text-white/70',
            'stats'     => [
                ['icon' => '📋', 'label' => 'Assigned', 'value' => '0 pending', 'color' => 'text-orange-500'],
                ['icon' => '🧠', 'label' => 'Quiz Average', 'value' => '60.1%', 'color' => 'text-green-500'],
                ['icon' => '📊', 'label' => 'Progress', 'value' => '100%', 'color' => 'text-indigo-500'],
            ],
            'highlights' => ['Daily streak counter', 'Quiz result cards', 'Class progress bars', 'Upcoming deadlines'],
        ],
        [
            'id'        => 'senior',
            'label'     => 'Senior',
            'ageRange'  => 'Ages 15–18',
            'emoji'     => '📈',
            'tagline'   => 'Analytics & Performance',
            'description' => 'Performance trend charts, assignment tracking, and detailed quiz history give senior students full visibility into their academic progress.',
            'grad'      => 'linear-gradient(135deg,#334155 0%,#475569 50%,#7c3aed 100%)',
            'glow'      => 'from-slate-700 via-slate-600 to-purple-600',
            'bottomBar' => 'from-slate-600 via-slate-500 to-purple-600',
            'badge'     => 'bg-slate-100 text-slate-700',
            'accent'    => 'text-slate-700',
            'chipBg'    => 'bg-slate-50',
            'chipBorder'=> 'border-slate-300',
            'headerBg'  => 'bg-gradient-to-r from-slate-100 to-white',
            'headerTitleCls' => 'text-ink',
            'headerSubCls'   => 'text-ink-soft',
            'stats'     => [
                ['icon' => '📉', 'label' => 'Avg Score', 'value' => '60.1%', 'color' => 'text-purple-600'],
                ['icon' => '✅', 'label' => 'Assignments', 'value' => '6/6', 'color' => 'text-blue-500'],
                ['icon' => '🎯', 'label' => 'Quiz Attempts', 'value' => '7', 'color' => 'text-amber-500'],
            ],
            'highlights' => ['Performance trend chart', 'Quiz history table', 'Class & subject list', 'Pending assignments'],
        ],
    ];
@endphp

<section id="dashboards" class="py-24 bg-slate-100 relative overflow-hidden scroll-mt-24">
    <div class="absolute top-0 left-0 w-[500px] h-[500px] rounded-full bg-purple-100/60 blur-3xl pointer-events-none -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-[400px] h-[400px] rounded-full bg-indigo-100/50 blur-3xl pointer-events-none translate-x-1/3 translate-y-1/3"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6">
        <div class="reveal mb-14 max-w-2xl">
            <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-purple-600">Adaptive Student Dashboards</span>
            <h2 class="font-display text-section-title font-semibold mb-4">One platform, three learning experiences.</h2>
            <p class="text-slate-500 text-lg leading-relaxed">
                The dashboard automatically adapts to each student's age group — gamified for kids, streak-driven for juniors, and analytics-focused for seniors.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">

            <div class="reveal">
                <div class="flex gap-2 mb-8 p-1.5 bg-white rounded-2xl border border-slate-200 w-fit shadow-sm">
                    @foreach($dashboards as $i => $d)
                        <button type="button" data-tab tabindex="0" data-grad="{{ $d['grad'] }}"
                                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 text-slate-500 hover:text-slate-900"
                                aria-pressed="false">
                            <span>{{ $d['emoji'] }}</span>{{ $d['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="space-y-6">
                    @foreach($dashboards as $i => $d)
                        <div data-info {{ $i > 0 ? 'hidden' : '' }}>
                            <div class="flex items-center gap-3 mb-3">
                                <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $d['badge'] }}">{{ $d['ageRange'] }}</span>
                                <span class="text-xs font-semibold tracking-widest uppercase {{ $d['accent'] }}">{{ $d['tagline'] }}</span>
                            </div>
                            <h3 class="font-display text-2xl font-semibold mb-3">{{ $d['label'] }} Dashboard</h3>
                            <p class="text-slate-500 leading-relaxed">{{ $d['description'] }}</p>
                        </div>
                    @endforeach

                    <div class="grid grid-cols-2 gap-3">
                        @foreach($dashboards as $i => $d)
                            <div data-highlights {{ $i > 0 ? 'hidden' : '' }} class="grid grid-cols-2 gap-3 col-span-2">
                                @foreach($d['highlights'] as $h)
                                    <div class="flex items-center gap-2.5 p-3 rounded-xl {{ $d['chipBg'] }} border {{ $d['chipBorder'] }}">
                                        <span class="w-5 h-5 rounded-full bg-gradient-to-br {{ $d['glow'] }} flex items-center justify-center flex-shrink-0">
                                            @include('partials.landing.icon', ['name' => 'check', 'size' => 10, 'iconClass' => 'text-white', 'solid' => true])
                                        </span>
                                        <span class="text-sm font-medium">{{ $h }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex gap-1.5" data-dots>
                            @foreach($dashboards as $i => $d)
                                <button type="button" data-dot data-grad="{{ $d['grad'] }}"
                                        class="h-1.5 rounded-full transition-all duration-500 {{ $i === 0 ? 'dot-on' : 'dot-off' }}"
                                        aria-label="Show {{ $d['label'] }} dashboard"></button>
                            @endforeach
                        </div>
                        <span class="text-xs text-slate-500">Auto-cycles every 3.5s</span>
                    </div>
                </div>
            </div>

            <div class="reveal reveal-delay-2">
                <div class="relative">
                    @foreach($dashboards as $i => $d)
                        <div data-panel {{ $i > 0 ? 'hidden' : '' }} class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br {{ $d['glow'] }} opacity-20 blur-2xl rounded-3xl scale-95"></div>

                            <div class="relative bg-white rounded-2xl border border-slate-200 shadow-2xl overflow-hidden">
                                <div class="{{ $d['headerBg'] }} px-6 py-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h4 class="font-bold text-xl {{ $d['headerTitleCls'] }}">{{ $d['id'] === 'kid' ? 'Hi Liam! 🌟' : ($d['id'] === 'junior' ? 'Hey Liam! 🔥' : 'Liam') }}</h4>
                                            <p class="text-sm mt-0.5 {{ $d['headerSubCls'] }}">{{ $d['id'] === 'kid' ? "Ready for today's adventures?" : ($d['id'] === 'junior' ? "Saturday, Sep 5 · Here's how your week is going" : 'Saturday, September 5, 2026') }}</p>
                                        </div>
                                        @if($d['id'] === 'kid')
                                            <span class="text-4xl">⭐</span>
                                        @elseif($d['id'] === 'junior')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-orange-500 text-white text-xs font-bold">🔥 2-day streak</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold">Horizon International Academy › Grade 10</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-0 border-b border-slate-200">
                                    @foreach($d['stats'] as $k => $stat)
                                        <div class="p-4 {{ $k < 2 ? 'border-r border-slate-200' : '' }}">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <span class="text-base">{{ $stat['icon'] }}</span>
                                                <span class="text-xs text-slate-500 font-medium uppercase tracking-wide">{{ $stat['label'] }}</span>
                                            </div>
                                            <span class="font-bold text-lg {{ $stat['color'] }}">{{ $stat['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="p-5">
                                    @if($d['id'] === 'kid')
                                        <div>
                                            <p class="text-sm font-semibold text-purple-700 mb-3">⭐ My Stars</p>
                                            <div class="flex gap-1.5 mb-4">
                                                @for($s = 0; $s < 10; $s++)<span class="text-xl {{ $s < 6 ? 'text-yellow-400' : 'text-gray-200' }}">★</span>@endfor
                                            </div>
                                            <div class="bg-purple-50 rounded-xl p-3 text-center">
                                                <p class="text-sm font-medium text-purple-700">🎯 Today's Missions</p>
                                                <p class="text-xs text-purple-500 mt-1">🎉 All done! You finished all your missions today! 🏆</p>
                                            </div>
                                        </div>
                                    @elseif($d['id'] === 'junior')
                                        <div class="space-y-3">
                                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">My Classes</p>
                                            @foreach(['10-A Mathematics', '10-B Science & Physics'] as $c => $cls)
                                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                                    <span class="text-sm font-medium">{{ $cls }}</span>
                                                    <div class="w-20 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                        <div class="h-full rounded-full {{ $c === 0 ? 'bg-purple-500 w-3/4' : 'bg-green-500 w-full' }}"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="grid grid-cols-3 gap-2 mt-2">
                                                @foreach([['40%', 'bg-red-50', 'text-red-500', 'text-red-400', 'FAILED'], ['90%', 'bg-green-50', 'text-green-600', 'text-green-500', 'PASSED'], ['100%', 'bg-green-50', 'text-green-600', 'text-green-500', 'PASSED']] as $q)
                                                    <div class="p-2 rounded-lg text-center {{ $q[1] }}">
                                                        <span class="font-bold text-sm {{ $q[2] }}">{{ $q[0] }}</span>
                                                        <p class="text-xs mt-0.5 {{ $q[3] }}">{{ $q[4] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="space-y-3">
                                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Performance Trend · Quiz Scores</p>
                                            <div class="h-16 flex items-end gap-1 px-1">
                                                @foreach([2, 2, 2, 2, 2, 2, 2, 8] as $b => $h)
                                                    <div class="flex-1 rounded-t" style="height:{{ $h * 8 }}px; background:{{ $b === 7 ? '#7c3aed' : '#e9d5ff' }};"></div>
                                                @endforeach
                                            </div>
                                            <div class="space-y-2">
                                                @foreach([['10-A Mathematics', 'Sarah Connor'], ['10-B Science & Physics', 'Dr. Robert Langdon']] as $row)
                                                    <div class="flex items-center justify-between text-xs py-2 border-b border-slate-200 last:border-0">
                                                        <span class="font-medium">{{ $row[0] }}</span>
                                                        <span class="text-slate-500">{{ $row[1] }}</span>
                                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">Up to date</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="px-5 py-3 bg-gradient-to-r {{ $d['bottomBar'] }} bg-opacity-10">
                                    <p class="text-xs text-white font-medium text-center">{{ $d['emoji'] }} {{ $d['label'] }} Dashboard · Automatically assigned by age group</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>