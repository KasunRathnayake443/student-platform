<section class="py-20 relative overflow-hidden scroll-mt-24">
    <div class="absolute top-0 right-0 w-96 h-96 blob-teal opacity-50 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 blob-navy opacity-40 pointer-events-none"></div>
    <div class="absolute inset-0 dot-pattern opacity-40 pointer-events-none"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
        <div class="reveal mb-3">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-teal-500/30 bg-teal-500/10 text-teal-600 text-xs font-semibold tracking-widest uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                Ready to Launch
            </span>
        </div>

        <h2 class="reveal font-display text-section-title font-semibold mb-5">Bring your school online in days, not months.</h2>

        <p class="reveal text-slate-500 text-lg leading-relaxed mb-10 max-w-2xl mx-auto">
            StudentPlatform is built on Laravel — battle-tested, fast, and ready to scale with your institution. Request a live demo and see it running with your own data.
        </p>

        <div class="reveal flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#portals" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-ink text-white font-semibold text-base rounded-xl hover:bg-ink-soft transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5 min-h-[52px]">
                @include('partials.landing.icon', ['name' => 'calendar-days', 'size' => 18])
                Request a Demo
            </a>
            <a href="#portals" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-ink font-medium text-base rounded-xl border border-slate-200 hover:border-teal-500 hover:text-teal-600 transition-all duration-200 min-h-[52px]">
                View All Portals
                @include('partials.landing.icon', ['name' => 'arrow-right', 'size' => 18])
            </a>
        </div>

        <p class="reveal mt-8 text-sm text-slate-500 flex items-center justify-center gap-2">
            @include('partials.landing.icon', ['name' => 'shield-check', 'size' => 15, 'iconClass' => 'text-teal-500'])
            No setup fees. GDPR-compliant. Full data ownership for your school.
        </p>
    </div>
</section>