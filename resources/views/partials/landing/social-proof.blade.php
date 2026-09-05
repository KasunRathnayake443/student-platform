@php
    $stats = [
        ['value' => '12,400+', 'label' => 'Active Students', 'icon' => 'user-group'],
        ['value' => '860+', 'label' => 'Courses Available', 'icon' => 'book-open'],
        ['value' => '74', 'label' => 'Schools Enrolled', 'icon' => 'building-office'],
        ['value' => '97%', 'label' => 'Satisfaction Rate', 'icon' => 'star'],
    ];
@endphp

<section id="social-proof" class="py-20 bg-ink scroll-mt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
            @foreach($stats as $idx => $stat)
                <div class="reveal reveal-delay-{{ $idx + 1 }} flex flex-col items-center text-center p-6 rounded-2xl bg-white/5 border border-white/10">
                    <div class="w-11 h-11 rounded-xl bg-teal-500/15 flex items-center justify-center mb-4">
                        @include('partials.landing.icon', ['name' => $stat['icon'], 'size' => 20, 'iconClass' => 'text-teal-500'])
                    </div>
                    <span class="font-display text-3xl font-bold text-white mb-1">{{ $stat['value'] }}</span>
                    <span class="text-xs font-medium tracking-wide text-white/60 uppercase">{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="reveal max-w-3xl mx-auto text-center">
            <div class="flex justify-center gap-1 mb-6">
                @for($i = 0; $i < 5; $i++)
                    @include('partials.landing.icon', ['name' => 'star', 'size' => 18, 'iconClass' => 'text-teal-500', 'solid' => true])
                @endfor
            </div>

            <blockquote class="font-display text-2xl md:text-3xl font-medium text-white leading-relaxed italic mb-8">
                "StudentPlatform transformed how our 600-student school manages learning. Teachers spend 40% less time on admin, and students actually log in every day."
            </blockquote>

            <div>
                <p class="font-semibold text-white text-base">Dr. Priya Venkataraman</p>
                <p class="text-sm text-white/60 mt-1">Principal, Greenfield International School, Bengaluru</p>
            </div>
        </div>
    </div>
</section>