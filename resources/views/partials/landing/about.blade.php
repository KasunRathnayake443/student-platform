@php
    $at = fn (string $key) => __('landing.about.' . $key);
    $offerings = collect(range(0, 4))->map(fn ($i) => [
        'title' => __("landing.about.offerings.{$i}.title"),
        'text'  => __("landing.about.offerings.{$i}.text"),
    ])->all();
    $executive = collect(range(0, 3))->map(fn ($i) => [
        'label' => __("landing.about.executive.{$i}.label"),
        'text'  => __("landing.about.executive.{$i}.text"),
    ])->all();
@endphp

<section id="about" class="py-24 bg-ink scroll-mt-24" aria-labelledby="about-title">
    <div class="max-w-7xl mx-auto px-6">
        <div class="reveal text-center mb-14 max-w-3xl mx-auto">
            <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-teal-400">{{ $at('kicker') }}</span>
            <h2 id="about-title" class="font-display text-section-title font-semibold text-white mb-4">
                {{ $at('title') }}
            </h2>
            <p class="text-white/60 text-lg leading-relaxed">
                {{ $at('intro') }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            {{-- Company profile --}}
            <article class="reveal bg-white/5 border border-white/10 rounded-2xl p-8">
                <div class="flex items-center gap-4 mb-6">
                    <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center flex-shrink-0">
                        @include('partials.landing.icon', ['name' => 'building-office', 'size' => 22, 'iconClass' => 'text-white'])
                    </span>
                    <div>
                        <h3 class="font-display text-2xl font-semibold text-white">BeeSmart (Pvt) Ltd.</h3>
                        <a href="https://beesmart.ltd/" target="_blank" rel="noopener noreferrer" class="text-teal-300 text-sm font-medium hover:text-teal-200 transition-colors duration-200">beesmart.ltd ↗</a>
                    </div>
                </div>

                <p class="text-white/65 leading-relaxed mb-7">
                    {{ $at('company_intro') }}
                </p>

                <h4 class="text-xs font-semibold tracking-[0.16em] uppercase text-teal-300 mb-4">{{ $at('core_offerings') }}</h4>
                <ul class="space-y-4 mb-7">
                    @foreach($offerings as $offering)
                        <li class="flex gap-3">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-teal-500/20 flex items-center justify-center flex-shrink-0">
                                @include('partials.landing.icon', ['name' => 'check', 'size' => 10, 'iconClass' => 'text-teal-300', 'solid' => true])
                            </span>
                            <div>
                                <p class="text-white font-medium text-sm">{{ $offering['title'] }}</p>
                                <p class="text-white/55 text-sm leading-relaxed mt-0.5">{{ $offering['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="rounded-xl border border-teal-400/20 bg-teal-400/5 p-5">
                    <p class="text-teal-300 font-semibold text-sm mb-1">{{ $at('model_title') }}</p>
                    <p class="text-white/60 text-sm leading-relaxed">
                        {{ $at('model_text') }}
                    </p>
                </div>
            </article>

            <div class="space-y-8">
                {{-- Specialized arm --}}
                <article class="reveal reveal-delay-1 bg-white/5 border border-white/10 rounded-2xl p-8">
                    <div class="flex items-center gap-4 mb-5">
                        <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center flex-shrink-0">
                            @include('partials.landing.icon', ['name' => 'shield-check', 'size' => 22, 'iconClass' => 'text-white'])
                        </span>
                        <div>
                            <h3 class="font-display text-xl font-semibold text-white">MedTech BeeSmart</h3>
                            <a href="https://medtech.beesmart.ltd/" target="_blank" rel="noopener noreferrer" class="text-teal-300 text-sm font-medium hover:text-teal-200 transition-colors duration-200">medtech.beesmart.ltd ↗</a>
                        </div>
                    </div>
                    <p class="text-white/65 leading-relaxed text-sm">
                        {{ $at('medtech_text') }}
                    </p>
                </article>

                {{-- Executive profile --}}
                <article class="reveal reveal-delay-2 bg-ink-soft border border-white/10 rounded-2xl p-8">
                    <div class="flex items-center gap-4 mb-5">
                        <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center flex-shrink-0">
                            @include('partials.landing.icon', ['name' => 'user-group', 'size' => 22, 'iconClass' => 'text-white'])
                        </span>
                        <div>
                            <h3 class="font-display text-xl font-semibold text-white">Dr. Praveen De Silva</h3>
                            <p class="text-teal-300 text-sm font-semibold">{{ $at('ceo_role') }}</p>
                        </div>
                    </div>

                    <p class="text-white/65 leading-relaxed text-sm mb-5">
                        {{ $at('ceo_intro') }}
                    </p>

                    <ul class="space-y-3">
                        @foreach($executive as $item)
                            <li class="flex gap-3">
                                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                                <p class="text-white/60 text-sm leading-relaxed"><span class="text-white font-medium">{{ $item['label'] }}:</span> {{ $item['text'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </article>
            </div>
        </div>
    </div>
</section>
