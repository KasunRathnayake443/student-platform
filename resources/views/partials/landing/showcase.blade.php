@php
    $td = fn (string $group, string $key) => __("landing.showcase.dashboards.{$group}.{$key}");
    $dashboards = [
        [
            'id'        => 'kid',
            'label'     => $td('kid', 'label'),
            'ageRange'  => $td('kid', 'age'),
            'emoji'     => '🌟',
            'tagline'   => $td('kid', 'tagline'),
            'description' => $td('kid', 'description'),
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
                ['icon' => '⭐', 'label' => $td('kid', 'stats.0'), 'value' => '6 / 10', 'color' => 'text-yellow-500'],
                ['icon' => '🎯', 'label' => $td('kid', 'stats.1'), 'value' => '3 / 3', 'color' => 'text-green-500'],
                ['icon' => '📚', 'label' => $td('kid', 'stats.2'), 'value' => '4', 'color' => 'text-purple-500'],
            ],
            'highlights' => [
                $td('kid', 'highlights.0'),
                $td('kid', 'highlights.1'),
                $td('kid', 'highlights.2'),
                $td('kid', 'highlights.3'),
            ],
        ],
        [
            'id'        => 'junior',
            'label'     => $td('junior', 'label'),
            'ageRange'  => $td('junior', 'age'),
            'emoji'     => '🔥',
            'tagline'   => $td('junior', 'tagline'),
            'description' => $td('junior', 'description'),
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
                ['icon' => '📋', 'label' => $td('junior', 'stats.0'), 'value' => '0 pending', 'color' => 'text-orange-500'],
                ['icon' => '🧠', 'label' => $td('junior', 'stats.1'), 'value' => '60.1%', 'color' => 'text-green-500'],
                ['icon' => '📊', 'label' => $td('junior', 'stats.2'), 'value' => '100%', 'color' => 'text-indigo-500'],
            ],
            'highlights' => [
                $td('junior', 'highlights.0'),
                $td('junior', 'highlights.1'),
                $td('junior', 'highlights.2'),
                $td('junior', 'highlights.3'),
            ],
        ],
        [
            'id'        => 'senior',
            'label'     => $td('senior', 'label'),
            'ageRange'  => $td('senior', 'age'),
            'emoji'     => '📈',
            'tagline'   => $td('senior', 'tagline'),
            'description' => $td('senior', 'description'),
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
                ['icon' => '📉', 'label' => $td('senior', 'stats.0'), 'value' => '60.1%', 'color' => 'text-purple-600'],
                ['icon' => '✅', 'label' => $td('senior', 'stats.1'), 'value' => '6/6', 'color' => 'text-blue-500'],
                ['icon' => '🎯', 'label' => $td('senior', 'stats.2'), 'value' => '7', 'color' => 'text-amber-500'],
            ],
            'highlights' => [
                $td('senior', 'highlights.0'),
                $td('senior', 'highlights.1'),
                $td('senior', 'highlights.2'),
                $td('senior', 'highlights.3'),
            ],
        ],
    ];
@endphp

<section id="dashboards" class="py-24 bg-slate-100 relative overflow-hidden scroll-mt-24">
    <div class="absolute top-0 left-0 w-[500px] h-[500px] rounded-full bg-purple-100/60 blur-3xl pointer-events-none -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-[400px] h-[400px] rounded-full bg-indigo-100/50 blur-3xl pointer-events-none translate-x-1/3 translate-y-1/3"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6">
        <div class="reveal mb-14 max-w-2xl">
            <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-purple-600">{{ __('landing.showcase.kicker') }}</span>
            <h2 class="font-display text-section-title font-semibold mb-4">{{ __('landing.showcase.title') }}</h2>
            <p class="text-slate-500 text-lg leading-relaxed">
                {{ __('landing.showcase.description') }}
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
                            <h3 class="font-display text-2xl font-semibold mb-3">{{ __('landing.showcase.dashboard_title', ['name' => $d['label']]) }}</h3>
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
                                        aria-label="{{ __('landing.showcase.show_dashboard', ['name' => $d['label']]) }}"></button>
                            @endforeach
                        </div>
                        <span class="text-xs text-slate-500">{{ __('landing.showcase.auto_cycle') }}</span>
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
                                            <h4 class="font-bold text-xl {{ $d['headerTitleCls'] }}">{{ $td($d['id'], 'hello') }}</h4>
                                            <p class="text-sm mt-0.5 {{ $d['headerSubCls'] }}">{{ $td($d['id'], 'sub') }}</p>
                                        </div>
                                        @if($d['id'] === 'kid')
                                            <span class="text-4xl">⭐</span>
                                        @elseif($d['id'] === 'junior')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-orange-500 text-white text-xs font-bold">{{ $td('junior', 'badge') }}</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold">{{ $td('senior', 'badge') }}</span>
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
                                            <p class="text-sm font-semibold text-purple-700 mb-3">{{ $td('kid', 'stars_title') }}</p>
                                            <div class="flex gap-1.5 mb-4">
                                                @for($s = 0; $s < 10; $s++)<span class="text-xl {{ $s < 6 ? 'text-yellow-400' : 'text-gray-200' }}">★</span>@endfor
                                            </div>
                                            <div class="bg-purple-50 rounded-xl p-3 text-center">
                                                <p class="text-sm font-medium text-purple-700">{{ $td('kid', 'missions') }}</p>
                                                <p class="text-xs text-purple-500 mt-1">{{ __('landing.showcase.mock.all_done') }}</p>
                                            </div>
                                        </div>
                                    @elseif($d['id'] === 'junior')
                                        <div class="space-y-3">
                                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('landing.showcase.mock.my_classes') }}</p>
                                            @foreach([__('landing.showcase.mock.math'), __('landing.showcase.mock.science')] as $c => $cls)
                                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                                    <span class="text-sm font-medium">{{ $cls }}</span>
                                                    <div class="w-20 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                        <div class="h-full rounded-full {{ $c === 0 ? 'bg-purple-500 w-3/4' : 'bg-green-500 w-full' }}"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="grid grid-cols-3 gap-2 mt-2">
                                                @foreach([['40%', 'bg-red-50', 'text-red-500', 'text-red-400', 'failed'], ['90%', 'bg-green-50', 'text-green-600', 'text-green-500', 'passed'], ['100%', 'bg-green-50', 'text-green-600', 'text-green-500', 'passed']] as $q)
                                                    <div class="p-2 rounded-lg text-center {{ $q[1] }}">
                                                        <span class="font-bold text-sm {{ $q[2] }}">{{ $q[0] }}</span>
                                                        <p class="text-xs mt-0.5 {{ $q[3] }}">{{ __('landing.showcase.mock.' . $q[4]) }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="space-y-3">
                                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('landing.showcase.mock.performance') }}</p>
                                            <div class="h-16 flex items-end gap-1 px-1">
                                                @foreach([2, 2, 2, 2, 2, 2, 2, 8] as $b => $h)
                                                    <div class="flex-1 rounded-t" style="height:{{ $h * 8 }}px; background:{{ $b === 7 ? '#7c3aed' : '#e9d5ff' }};"></div>
                                                @endforeach
                                            </div>
                                            <div class="space-y-2">
                                                @foreach([['math', 'teacher_sarah'], ['science', 'teacher_robert']] as $row)
                                                    <div class="flex items-center justify-between text-xs py-2 border-b border-slate-200 last:border-0">
                                                        <span class="font-medium">{{ __('landing.showcase.mock.' . $row[0]) }}</span>
                                                        <span class="text-slate-500">{{ __('landing.showcase.mock.' . $row[1]) }}</span>
                                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">{{ __('landing.showcase.mock.up_to_date') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="px-5 py-3 bg-gradient-to-r {{ $d['bottomBar'] }} bg-opacity-10">
                                    <p class="text-xs text-white font-medium text-center">{{ $d['emoji'] }} {{ __('landing.showcase.dashboard_title', ['name' => $d['label']]) }} · {{ __('landing.showcase.mock.auto_assigned') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>