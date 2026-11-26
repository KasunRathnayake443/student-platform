@php
    $contactAddress = \App\Services\PlatformSettings::contactAddress();
    $contactEmail = \App\Services\PlatformSettings::contactEmail();
    $contactPhone = \App\Services\PlatformSettings::contactPhone();
    $socialLinks = \App\Services\PlatformSettings::socialLinks();
    $socialLabels = [
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'instagram' => 'Instagram',
    ];
    $t = fn (string $key) => __('landing.contact.' . $key);
@endphp

<section id="contact" class="py-28 md:py-36 relative overflow-hidden scroll-mt-24" aria-labelledby="contact-title">
    <div class="absolute inset-0 dot-pattern opacity-30 pointer-events-none"></div>
    <div class="absolute top-10 left-0 w-80 h-80 blob-teal opacity-40 pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6">
        <div class="reveal text-center mb-14 max-w-3xl mx-auto">
            <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-teal-600">{{ $t('kicker') }}</span>
            <h2 id="contact-title" class="font-display text-section-title font-semibold mb-4">{{ $t('title') }}</h2>
            <p class="text-slate-500 text-lg leading-relaxed">{{ $t('description') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">
            <div class="reveal lg:col-span-3 bg-white rounded-2xl border border-slate-200 p-8 shadow-sm">
                <h3 class="font-display text-2xl font-semibold text-ink mb-2">{{ $t('form_title') }}</h3>
                <p class="text-slate-500 text-sm mb-7">{{ $t('form_description') }}</p>

                <div id="contact-form-feedback" role="status" aria-live="polite" class="mb-6 hidden rounded-xl px-4 py-3 text-sm font-medium"></div>

                @if(session('contact_submitted'))
                    <div role="status" class="mb-6 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-medium text-teal-700">
                        {{ $t('success') }}
                    </div>
                @endif

                @error('contact')
                    <div role="alert" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                        {{ $message }}
                    </div>
                @enderror

                <form id="contact-form" action="{{ route('contact.store') }}" method="POST" data-success="{{ $t('success') }}" data-error="{{ $t('error') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="contact-name" class="block text-sm font-semibold text-ink mb-2">{{ $t('name') }}</label>
                            <input type="text" id="contact-name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" placeholder="{{ $t('name_placeholder') }}" aria-invalid="@error('name') true @else false @enderror" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-ink placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 min-h-[48px] @error('name') border-rose-400 @enderror">
                            @error('name')
                                <p data-field-error="name" class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact-email" class="block text-sm font-semibold text-ink mb-2">{{ $t('email') }}</label>
                            <input type="email" id="contact-email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="{{ $t('email_placeholder') }}" aria-invalid="@error('email') true @else false @enderror" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-ink placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 min-h-[48px] @error('email') border-rose-400 @enderror">
                            @error('email')
                                <p data-field-error="email" class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="contact-phone" class="block text-sm font-semibold text-ink mb-2">{{ $t('phone') }}</label>
                        <input type="tel" id="contact-phone" name="phone" value="{{ old('phone') }}" maxlength="50" autocomplete="tel" placeholder="{{ $t('phone_placeholder') }}" aria-invalid="@error('phone') true @else false @enderror" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-ink placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 min-h-[48px] @error('phone') border-rose-400 @enderror">
                        @error('phone')
                            <p data-field-error="phone" class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact-message" class="block text-sm font-semibold text-ink mb-2">{{ $t('message') }}</label>
                        <textarea id="contact-message" name="message" rows="5" required maxlength="5000" placeholder="{{ $t('message_placeholder') }}" aria-invalid="@error('message') true @else false @enderror" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-ink placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 @error('message') border-rose-400 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <p data-field-error="message" class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 bg-ink text-white font-semibold text-sm rounded-xl hover:bg-ink-soft transition-all duration-200 shadow-lg hover:-translate-y-0.5 min-h-[52px]">
                            {{ $t('send') }}
                            @include('partials.landing.icon', ['name' => 'arrow-right', 'size' => 18])
                        </button>
                        <p class="text-xs text-slate-500">{{ $t('response_note') }}</p>
                    </div>
                </form>
            </div>

            <div class="reveal-delay-1 reveal lg:col-span-2 bg-ink rounded-2xl p-8 text-white h-full">
                <h3 class="font-display text-2xl font-semibold mb-2">{{ $t('details_title') }}</h3>
                <p class="text-white/60 text-sm leading-relaxed mb-8">{{ $t('details_description') }}</p>

                <div class="space-y-7">
                    @if($contactAddress)
                        <div class="flex gap-4">
                            <span class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                @include('partials.landing.icon', ['name' => 'map-pin', 'size' => 20, 'iconClass' => 'text-teal-300'])
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-teal-300 mb-1">{{ $t('address') }}</p>
                                <p class="text-white/85 text-sm leading-relaxed whitespace-pre-line">{{ $contactAddress }}</p>
                            </div>
                        </div>
                    @endif

                    @if($contactEmail)
                        <div class="flex gap-4">
                            <span class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                @include('partials.landing.icon', ['name' => 'envelope', 'size' => 20, 'iconClass' => 'text-teal-300'])
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-teal-300 mb-1">{{ $t('email') }}</p>
                                <a href="mailto:{{ $contactEmail }}" class="text-white/85 hover:text-teal-300 text-sm break-all transition-colors duration-200">{{ $contactEmail }}</a>
                            </div>
                        </div>
                    @endif

                    @if($contactPhone)
                        <div class="flex gap-4">
                            <span class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                @include('partials.landing.icon', ['name' => 'phone', 'size' => 20, 'iconClass' => 'text-teal-300'])
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-teal-300 mb-1">{{ $t('phone') }}</p>
                                <a href="tel:{{ preg_replace('/[^+\d]/', '', $contactPhone) }}" class="text-white/85 hover:text-teal-300 text-sm transition-colors duration-200">{{ $contactPhone }}</a>
                            </div>
                        </div>
                    @endif
                </div>

                @if($socialLinks)
                    <div class="mt-9 pt-7 border-t border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-teal-300 mb-4">{{ $t('social_title') }}</p>
                        <div class="flex flex-wrap gap-3">
                            @foreach($socialLinks as $platform => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $socialLabels[$platform] ?? ucfirst($platform) }}" title="{{ $socialLabels[$platform] ?? ucfirst($platform) }}" class="w-11 h-11 rounded-xl bg-white/10 hover:bg-teal-500 flex items-center justify-center transition-colors duration-200">
                                    @include('partials.landing.icon', ['name' => $platform, 'size' => 18, 'iconClass' => 'text-white', 'solid' => true])
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
