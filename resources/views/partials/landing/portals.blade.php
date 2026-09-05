@php
    $portals = [
        [
            'role'        => 'Student',
            'tagline'     => 'Your learning, your pace.',
            'description' => 'Access your enrolled classes, complete assignments, take quizzes, and watch your progress grow — with a dashboard that adapts to your age group.',
            'href'        => '/student/login',
            'icon'        => 'academic-cap',
            'emoji'       => '🎓',
            'features'    => ['Adaptive Kid / Junior / Senior dashboard', 'My Classes & Lessons', 'Quiz & Assignment submissions', 'Progress tracking & grade reports'],
            'gradient'    => 'from-violet-500 to-purple-600',
            'btnClass'    => 'bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700',
        ],
        [
            'role'        => 'Teacher',
            'tagline'     => 'Create. Teach. Inspire.',
            'description' => 'Build lessons, create quizzes and assignments, manage your class roster, grade submissions, and track how each student is performing.',
            'href'        => '/teacher/login',
            'icon'        => 'book-open',
            'emoji'       => '👩‍🏫',
            'features'    => ['Add lessons, quizzes & assignments', 'Grade book & feedback tools', 'Class performance analytics', 'Student progress visibility'],
            'gradient'    => 'from-indigo-500 to-blue-600',
            'btnClass'    => 'bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700',
        ],
        [
            'role'        => 'School Admin',
            'tagline'     => 'Run your school smarter.',
            'description' => "Create your school space, add teachers and students, build grade levels and classes, and get full visibility into every student's progress across the institution.",
            'href'        => '/admin/login',
            'icon'        => 'building-office',
            'emoji'       => '🏫',
            'features'    => ['Create & manage school space', 'Add students & teachers', 'Create grades & classes', 'Track all student progress'],
            'gradient'    => 'from-slate-700 to-purple-800',
            'btnClass'    => 'bg-gradient-to-r from-slate-700 to-purple-800 hover:from-slate-700 hover:to-purple-900',
        ],
    ];
@endphp

<section id="portals" class="py-24 bg-slate-100 scroll-mt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="reveal text-center mb-14 max-w-2xl mx-auto">
            <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-purple-600">Choose Your Portal</span>
            <h2 class="font-display text-section-title font-semibold mb-4">One platform. Three doors.</h2>
            <p class="text-slate-500 text-lg leading-relaxed">
                Every user type gets a tailored experience — sign in to the portal built for your role.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($portals as $idx => $p)
                <div class="reveal reveal-delay-{{ $idx + 1 }} group relative bg-white rounded-2xl border border-slate-200 overflow-hidden card-hover-lift flex flex-col">
                    <div class="relative h-36 bg-gradient-to-br {{ $p['gradient'] }} flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 opacity-20" style="background-image:radial-gradient(circle at 30% 70%,rgba(255,255,255,.4) 0%,transparent 50%);"></div>
                        <span class="text-6xl relative z-10">{{ $p['emoji'] }}</span>
                        <div class="absolute top-4 left-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-sm border border-white/20">
                                @include('partials.landing.icon', ['name' => $p['icon'], 'size' => 13, 'solid' => true])
                                {{ $p['role'] }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col flex-1 p-7">
                        <p class="text-xs font-semibold tracking-widest uppercase mb-2 text-purple-600">{{ $p['tagline'] }}</p>
                        <h3 class="font-display text-xl font-semibold mb-3">{{ $p['role'] }} Portal</h3>
                        <p class="text-sm text-slate-500 leading-relaxed mb-5">{{ $p['description'] }}</p>

                        <ul class="space-y-2.5 mb-7 flex-1">
                            @foreach($p['features'] as $feat)
                                <li class="flex items-start gap-2.5 text-sm text-slate-800">
                                    <span class="w-4 h-4 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        @include('partials.landing.icon', ['name' => 'check', 'size' => 10, 'iconClass' => 'text-purple-600', 'solid' => true])
                                    </span>
                                    {{ $feat }}
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ $p['href'] }}" class="inline-flex items-center justify-center gap-2 w-full py-3.5 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5 shadow-md hover:shadow-lg text-white {{ $p['btnClass'] }}">
                            Go to {{ $p['role'] }} Portal
                            @include('partials.landing.icon', ['name' => 'arrow-right', 'size' => 16])
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>