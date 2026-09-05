@php
    $features = [
        [
            'id'          => 'school-space',
            'icon'        => 'building-office',
            'emoji'       => '🏫',
            'title'       => 'Create Your School Space',
            'description' => 'Schools get their own dedicated space in the platform. Set up your institution, customize branding, and manage everything from one place.',
            'items'       => ['Custom school profile', 'Multi-school support', 'School-level settings', 'Branded experience'],
            'colSpan'     => 'md:col-span-2',
            'accent'      => 'from-violet-600 to-purple-700',
            'dark'        => true,
        ],
        [
            'id'          => 'grades-classes',
            'icon'        => 'academic-cap',
            'emoji'       => '📚',
            'title'       => 'Grades & Classes',
            'description' => 'Create grade levels and build classes inside each grade. Assign teachers and enroll students with ease.',
            'items'       => ['Grade creation', 'Classes inside grades', 'Teacher assignment', 'Student enrollment'],
            'colSpan'     => 'md:col-span-1',
            'accent'      => 'from-indigo-500 to-blue-600',
            'dark'        => false,
        ],
        [
            'id'          => 'content',
            'icon'        => 'pencil-square',
            'emoji'       => '✏️',
            'title'       => 'Quizzes, Assignments & Lessons',
            'description' => 'Teachers add rich learning content — video lessons, interactive quizzes, and graded assignments — all within their classes.',
            'items'       => ['Video lessons', 'Interactive quizzes', 'Graded assignments', 'Content library'],
            'colSpan'     => 'md:col-span-1',
            'accent'      => 'from-purple-500 to-violet-600',
            'dark'        => false,
        ],
        [
            'id'          => 'progress',
            'icon'        => 'chart-bar',
            'emoji'       => '📊',
            'title'       => "Track Every Student's Progress",
            'description' => 'Real-time dashboards show quiz scores, assignment completion, class performance, and learning trends for every student across all grades.',
            'items'       => ['Per-student analytics', 'Quiz score history', 'Assignment tracking', 'Performance trends'],
            'colSpan'     => 'md:col-span-2',
            'accent'      => 'from-slate-700 to-purple-800',
            'dark'        => true,
        ],
        [
            'id'          => 'people',
            'icon'        => 'user-group',
            'emoji'       => '👥',
            'title'       => 'Manage Students & Teachers',
            'description' => 'Add students and teachers to your school space. Assign roles, manage class memberships, and keep your roster up to date.',
            'items'       => ['Add students', 'Add teachers', 'Role management', 'Class rosters'],
            'colSpan'     => 'md:col-span-1',
            'accent'      => 'from-emerald-500 to-teal-600',
            'dark'        => false,
        ],
    ];
@endphp

<section id="features" class="py-24 scroll-mt-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="reveal mb-14 max-w-2xl">
            <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-purple-600">School Management</span>
            <h2 class="font-display text-section-title font-semibold mb-4">Everything your school needs, built in.</h2>
            <p class="text-slate-500 text-lg leading-relaxed">
                From creating your school space to tracking every student's progress — the full LMS lifecycle in one platform.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($features as $idx => $f)
                <div class="reveal reveal-delay-{{ ($idx % 4) + 1 }} {{ $f['colSpan'] }} group relative overflow-hidden rounded-2xl border border-slate-200 card-hover-lift {{ $f['dark'] ? 'min-h-[260px]' : 'min-h-[220px]' }}"
                     @if($f['dark']) style="background:linear-gradient(135deg,#1e1b4b,#4c1d95);" @else style="background:#ffffff;" @endif>
                    @if($f['dark'])
                        <div class="absolute inset-0 opacity-30" style="background-image:radial-gradient(circle at 70% 30%,rgba(167,139,250,.3) 0%,transparent 60%);"></div>
                    @endif

                    <div class="relative z-10 p-8 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center gap-3 mb-5">
                                <span class="w-11 h-11 rounded-xl flex items-center justify-center bg-gradient-to-br {{ $f['accent'] }}">
                                    @include('partials.landing.icon', ['name' => $f['icon'], 'size' => 20, 'iconClass' => 'text-white'])
                                </span>
                                <span class="text-2xl">{{ $f['emoji'] }}</span>
                            </div>
                            <h3 class="font-display text-xl font-semibold mb-3 {{ $f['dark'] ? 'text-white' : '' }}">{{ $f['title'] }}</h3>
                            <p class="text-sm leading-relaxed mb-5 {{ $f['dark'] ? 'text-white/65' : 'text-slate-500' }}">{{ $f['description'] }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach($f['items'] as $item)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium {{ $f['dark'] ? 'bg-white/10 text-white/80 border border-white/10' : 'bg-purple-50 text-purple-700 border border-purple-100' }}">
                                    @include('partials.landing.icon', ['name' => 'check', 'size' => 10, 'iconClass' => $f['dark'] ? 'text-purple-300' : 'text-purple-500', 'solid' => true])
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>