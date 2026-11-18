@php
    $offerings = [
        [
            'title' => 'Cloud Architecture & DevOps',
            'text'  => 'Design and deployment of resilient multi-cloud infrastructures (AWS, GCP, Azure, Kubernetes) with zero-downtime migration paths and intelligent auto-scaling.',
        ],
        [
            'title' => 'AI & Automation',
            'text'  => 'Machine learning pipelines, intelligent workflow automation and custom predictive intelligence models that streamline enterprise operations.',
        ],
        [
            'title' => 'Full-Stack Software Development',
            'text'  => 'End-to-end product engineering from MVPs to large-scale enterprise platforms using modern frameworks, agile delivery and DevOps-first principles.',
        ],
        [
            'title' => 'Cybersecurity',
            'text'  => 'Proactive threat detection, zero-trust system architecture and regulatory compliance frameworks designed for 24/7 protection.',
        ],
        [
            'title' => 'Data & Analytics',
            'text'  => 'Transformation of raw data into operational insights through real-time dashboards and business intelligence (BI) pipelines.',
        ],
    ];

    $executive = [
        'Engineering & leadership' => 'Over 13 years of software engineering and enterprise leadership, having overseen more than 300 global software initiatives.',
        'Domain specialization'   => 'Complex system architecture, blockchain integration, AI applications and scalable health-tech / med-tech infrastructure.',
        'Accolades & recognition' => 'NBQSA (National Best Quality Software Awards) category winner, with AWS Solutions Architect Professional certification and executive foundations tied to MIT and Stanford GSB.',
        'Industry presence'       => 'Past speaking engagements at events including AWS re:Invent.',
    ];
@endphp

<section id="about" class="py-24 bg-ink scroll-mt-24" aria-labelledby="about-title">
    <div class="max-w-7xl mx-auto px-6">
        <div class="reveal text-center mb-14 max-w-3xl mx-auto">
            <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-teal-400">About the creators</span>
            <h2 id="about-title" class="font-display text-section-title font-semibold text-white mb-4">
                Built by BeeSmart. Led by Dr. Praveen De Silva.
            </h2>
            <p class="text-white/60 text-lg leading-relaxed">
                This platform is created and maintained by BeeSmart (Pvt) Ltd. — a technology partner for enterprises that need secure, scalable and intelligently engineered software.
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
                    BeeSmart is a next-generation technology solutions and software engineering company specializing in enterprise-grade digital infrastructure, cloud architecture, AI automation and bespoke software development.
                </p>

                <h4 class="text-xs font-semibold tracking-[0.16em] uppercase text-teal-300 mb-4">Core offerings</h4>
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
                    <p class="text-teal-300 font-semibold text-sm mb-1">The “Embed, Design, Build, Hand Over” model</p>
                    <p class="text-white/60 text-sm leading-relaxed">
                        Rather than creating long-term dependencies, BeeSmart embeds with client teams to understand local constraints, builds in low-risk iterations and transfers full knowledge so internal teams can independently own and operate the systems. Long-term partnerships and enterprise-ready scalability support growth without re-engineering core architecture.
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
                        A specialized vertical under the BeeSmart ecosystem, focused on the intersection of healthcare and modern engineering. It builds secure, compliant and AI-ready medical technology — from EMR integrations and health informatics dashboards to patient-data frameworks designed for compliance-heavy clinical environments.
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
                            <p class="text-teal-300 text-sm font-semibold">Co-Founder &amp; CEO, BeeSmart</p>
                        </div>
                    </div>

                    <p class="text-white/65 leading-relaxed text-sm mb-5">
                        Dr. Praveen De Silva steers BeeSmart’s strategic vision across global enterprise systems, blockchain initiatives and health-tech innovation.
                    </p>

                    <ul class="space-y-3">
                        @foreach($executive as $label => $text)
                            <li class="flex gap-3">
                                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                                <p class="text-white/60 text-sm leading-relaxed"><span class="text-white font-medium">{{ $label }}:</span> {{ $text }}</p>
                            </li>
                        @endforeach
                    </ul>
                </article>
            </div>
        </div>
    </div>
</section>
