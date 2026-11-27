<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('registration.title') }} — {{ \App\Services\PlatformSettings::name() }}</title>

    <link rel="icon" href="{{ \App\Services\PlatformSettings::logoUrl() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/landing.css'])
</head>
<body>
    @php
        $paymentLabels = [
            'bank_transfer' => __('registration.form.method_bank'),
            'ezcash' => __('registration.form.method_ezcash'),
            'mcash' => __('registration.form.method_mcash'),
        ];
        $socialLabels = [
            'facebook' => 'Facebook',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'instagram' => 'Instagram',
        ];
        $pending = fn (?string $value): string => filled($value) ? (string) $value : __('registration.payment.pending');
    @endphp

    @include('partials.landing.header')

    <main>
        <section class="relative overflow-hidden pt-28 sm:pt-32 md:pt-40 pb-24 sm:pb-28" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 30%, #4c1d95 60%, #6d28d9 85%, #7c3aed 100%);" aria-labelledby="registration-title">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full bg-teal-300/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-80 h-80 rounded-full bg-pink-400/20 blur-3xl"></div>
                <div class="absolute inset-0 opacity-10" style="background-image:linear-gradient(rgba(255,255,255,.1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.1) 1px,transparent 1px);background-size:60px 60px;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-teal-300/40 bg-teal-300/10 text-teal-200 text-xs sm:text-sm font-semibold tracking-widest uppercase mb-6">
                        <span class="w-2 h-2 rounded-full bg-teal-300"></span>
                        {{ __('registration.hero.badge') }}
                    </span>

                    <h1 id="registration-title" class="reveal hero-animate font-display text-hero-xl font-semibold text-white leading-[1.08] mb-6">
                        {{ __('registration.hero.title_prefix') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-200 to-pink-200">{{ __('registration.hero.title_highlight') }}</span>
                    </h1>

                    <p class="reveal hero-animate text-white/80 text-lg sm:text-xl leading-relaxed max-w-2xl mb-8 font-light">
                        {{ __('registration.hero.description') }}
                    </p>

                    <div class="reveal hero-animate flex flex-col sm:flex-row gap-3 sm:gap-4 mb-10">
                        <a href="#registration-form" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 sm:px-8 sm:py-4 bg-white text-purple-800 font-semibold text-base rounded-xl hover:bg-purple-50 transition-colors duration-200 shadow-lg">
                            {{ __('registration.hero.start') }} <span aria-hidden="true">→</span>
                        </a>
                        <a href="#payment-options" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 sm:px-8 sm:py-4 bg-white/10 backdrop-blur-sm text-white font-medium text-base rounded-xl border border-white/20 hover:bg-white/20 transition-all duration-200">
                            {{ __('registration.hero.payment_options') }}
                        </a>
                    </div>

                    <div class="reveal hero-animate inline-flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl border border-white/15 bg-white/10 backdrop-blur-sm px-6 py-4">
                        <span class="text-white/70 text-sm uppercase tracking-widest font-semibold">{{ __('registration.hero.fee') }}</span>
                        <strong class="text-white text-2xl sm:text-3xl font-bold">{{ $feeLabel }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative z-20 -mt-10 sm:-mt-14" aria-label="Registration guidance">
            <div class="max-w-7xl mx-auto px-5 sm:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="reveal lg:col-span-2 rounded-3xl border border-slate-200 bg-white p-7 sm:p-9 shadow-xl">
                        <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-teal-600">{{ __('registration.audience.kicker') }}</span>
                        <h2 class="font-display text-2xl sm:text-3xl font-semibold text-ink mb-3">{{ __('registration.audience.title') }}</h2>
                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed mb-6">
                            {{ __('registration.audience.notice') }}
                        </p>
                        <a href="{{ route('home').'#contact' }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-teal-500 text-ink font-semibold text-base hover:bg-teal-400 transition-colors">
                            {{ __('registration.audience.contact_form') }} <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    <div class="reveal reveal-delay-1 rounded-3xl bg-ink text-white p-7 sm:p-9 shadow-xl">
                        <h2 class="font-display text-2xl font-semibold mb-4">{{ __('registration.audience.contact_title') }}</h2>
                        <ul class="space-y-3 text-base text-white/80">
                            @if(filled($contactAddress))
                                <li><span class="block text-xs uppercase tracking-widest text-teal-300 font-semibold">{{ __('registration.audience.address') }}</span>{{ $contactAddress }}</li>
                            @endif
                            @if(filled($contactEmail))
                                <li><span class="block text-xs uppercase tracking-widest text-teal-300 font-semibold">{{ __('registration.audience.email') }}</span><a class="hover:text-teal-300 transition-colors" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></li>
                            @endif
                            @if(filled($contactPhone))
                                <li><span class="block text-xs uppercase tracking-widest text-teal-300 font-semibold">{{ __('registration.audience.phone') }}</span><a class="hover:text-teal-300 transition-colors" href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}">{{ $contactPhone }}</a></li>
                            @endif
                            @if($socialLinks === [] && blank($contactAddress) && blank($contactEmail) && blank($contactPhone))
                                <li>{{ __('registration.audience.fallback') }}</li>
                            @endif
                        </ul>
                        @if($socialLinks !== [])
                            <div class="flex flex-wrap gap-4 mt-6">
                                @foreach($socialLinks as $network => $url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-teal-300 hover:text-white transition-colors">{{ $socialLabels[$network] ?? ucfirst($network) }} ↗</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section id="payment-options" class="py-20 sm:py-24 relative overflow-hidden scroll-mt-24" aria-labelledby="payment-title">
            <div class="absolute inset-0 dot-pattern opacity-40 pointer-events-none"></div>
            <div class="absolute top-10 left-0 w-80 h-80 blob-teal opacity-40 pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-80 h-80 blob-navy opacity-40 pointer-events-none"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8">
                <div class="reveal text-center max-w-3xl mx-auto mb-12">
                    <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-teal-600">{{ __('registration.payment.kicker') }}</span>
                    <h2 id="payment-title" class="font-display text-section-title font-semibold mb-4">{{ __('registration.payment.title') }}</h2>
                    <p class="text-slate-500 text-lg sm:text-xl leading-relaxed">{{ __('registration.payment.description') }}</p>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <article class="reveal card-hover-lift rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                        <div class="h-1.5" style="background: linear-gradient(90deg, #14b8a6, #0f766e);"></div>
                        <div class="p-7 sm:p-8">
                            <div class="flex items-center gap-4 mb-5">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-teal-50 text-2xl" aria-hidden="true">🏦</span>
                                <span class="text-xs font-bold tracking-widest text-teal-600 uppercase">{{ __('registration.payment.option_a') }}</span>
                            </div>
                            <h3 class="font-display text-xl sm:text-2xl font-semibold text-ink mb-4">{{ __('registration.payment.bank_title') }}</h3>
                            <dl class="space-y-3 text-base text-slate-600">
                                <div><dt class="text-xs uppercase tracking-widest font-semibold text-slate-400">{{ __('registration.payment.bank_name') }}</dt><dd>{{ $pending($paymentDetails['bank_name'] ?? null) }}</dd></div>
                                <div><dt class="text-xs uppercase tracking-widest font-semibold text-slate-400">{{ __('registration.payment.account_name') }}</dt><dd>{{ $pending($paymentDetails['bank_account_name'] ?? null) }}</dd></div>
                                <div><dt class="text-xs uppercase tracking-widest font-semibold text-slate-400">{{ __('registration.payment.account_number') }}</dt><dd>{{ $pending($paymentDetails['bank_account_number'] ?? null) }}</dd></div>
                                <div><dt class="text-xs uppercase tracking-widest font-semibold text-slate-400">{{ __('registration.payment.branch') }}</dt><dd>{{ $pending($paymentDetails['bank_branch'] ?? null) }}</dd></div>
                            </dl>
                            <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm sm:text-base text-slate-600 space-y-2">
                                <p><span class="font-semibold text-ink">{{ __('registration.payment.reference_prefix') }}</span> {{ __('registration.payment.reference_note') }}</p>
                                <p>{{ __('registration.payment.receipt_note') }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="reveal reveal-delay-1 card-hover-lift rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                        <div class="h-1.5" style="background: linear-gradient(90deg, #6366f1, #8b5cf6);"></div>
                        <div class="p-7 sm:p-8">
                            <div class="flex items-center gap-4 mb-5">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-50 text-2xl" aria-hidden="true">📱</span>
                                <span class="text-xs font-bold tracking-widest text-indigo-600 uppercase">{{ __('registration.payment.option_b') }}</span>
                            </div>
                            <h3 class="font-display text-xl sm:text-2xl font-semibold text-ink mb-4">{{ __('registration.payment.ezcash_title') }}</h3>
                            <ol class="list-decimal list-inside space-y-3 text-base text-slate-600">
                                <li>{{ __('registration.payment.dial_ezcash') }}</li>
                                <li>{{ __('registration.payment.select_send') }}</li>
                                <li>{{ __('registration.payment.enter_number', ['label' => __('registration.payment.ezcash_label'), 'number' => $pending($paymentDetails['ezcash_number'] ?? null)]) }}@if(filled($paymentDetails['ezcash_merchant_alias'] ?? null)) {{ __('registration.payment.alias', ['label' => __('registration.payment.merchant_alias'), 'value' => $paymentDetails['ezcash_merchant_alias']]) }}@endif</li>
                                <li>{{ __('registration.payment.enter_amount') }}</li>
                                <li>{{ __('registration.payment.note_reference') }}</li>
                            </ol>
                        </div>
                    </article>

                    <article class="reveal reveal-delay-2 card-hover-lift rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                        <div class="h-1.5" style="background: linear-gradient(90deg, #ec4899, #be185d);"></div>
                        <div class="p-7 sm:p-8">
                            <div class="flex items-center gap-4 mb-5">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-pink-50 text-2xl" aria-hidden="true">💬</span>
                                <span class="text-xs font-bold tracking-widest text-pink-600 uppercase">{{ __('registration.payment.option_c') }}</span>
                            </div>
                            <h3 class="font-display text-xl sm:text-2xl font-semibold text-ink mb-4">{{ __('registration.payment.mcash_title') }}</h3>
                            <ol class="list-decimal list-inside space-y-3 text-base text-slate-600">
                                <li>{{ __('registration.payment.dial_mcash') }}</li>
                                <li>{{ __('registration.payment.select_send') }}</li>
                                <li>{{ __('registration.payment.enter_number', ['label' => __('registration.payment.mcash_label'), 'number' => $pending($paymentDetails['mcash_number'] ?? null)]) }}@if(filled($paymentDetails['mcash_merchant_id'] ?? null)) {{ __('registration.payment.alias', ['label' => __('registration.payment.merchant_id'), 'value' => $paymentDetails['mcash_merchant_id']]) }}@endif</li>
                                <li>{{ __('registration.payment.enter_amount') }}</li>
                                <li>{{ __('registration.payment.note_reference') }}</li>
                            </ol>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="registration-form" class="py-20 sm:py-24 bg-slate-50 scroll-mt-24" aria-labelledby="form-title">
            <div class="max-w-5xl mx-auto px-5 sm:px-8">
                <div class="reveal text-center max-w-2xl mx-auto mb-12">
                    <span class="inline-block mb-3 text-xs font-semibold tracking-[0.18em] uppercase text-teal-600">{{ __('registration.form.kicker') }}</span>
                    <h2 id="form-title" class="font-display text-section-title font-semibold mb-4">{{ __('registration.form.title') }}</h2>
                    <p class="text-slate-500 text-lg sm:text-xl leading-relaxed">{{ __('registration.form.description') }}</p>
                </div>

                <div class="reveal rounded-3xl border border-slate-200 bg-white p-7 sm:p-10 shadow-xl">
                    @if(session('registration_submitted'))
                        <div role="status" class="mb-8 rounded-xl border border-teal-200 bg-teal-50 px-5 py-4 text-base font-medium text-teal-800">
                            {{ __('registration.messages.success') }}
                        </div>
                    @endif

                    @error('registration')
                        <div role="alert" class="mb-8 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-base font-medium text-rose-700">{{ $message }}</div>
                    @enderror

                    @if($errors->any())
                        <div role="alert" class="mb-8 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-base text-rose-700">
                            <p class="font-semibold mb-2">{{ __('registration.messages.check_fields') }}</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('student.registration.store') }}" method="POST" enctype="multipart/form-data" class="space-y-9">
                        @csrf

                        <fieldset>
                            <legend class="font-display text-xl font-semibold text-ink mb-1">{{ __('registration.form.about') }}</legend>
                            <p class="text-sm text-slate-500 mb-5">{{ __('registration.form.about_hint') }}</p>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="full_name" class="block text-base font-semibold text-ink mb-2">{{ __('registration.form.full_name') }} <span class="text-rose-500">*</span></label>
                                    <input id="full_name" name="full_name" type="text" required maxlength="255" placeholder="{{ __('registration.form.placeholders.full_name') }}" value="{{ old('full_name') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-ink placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none @error('full_name') border-rose-400 @enderror">
                                    @error('full_name') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="institution" class="block text-base font-semibold text-ink mb-2">{{ __('registration.form.institution') }} <span class="text-rose-500">*</span></label>
                                    <input id="institution" name="institution" type="text" required maxlength="255" placeholder="{{ __('registration.form.placeholders.institution') }}" value="{{ old('institution') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-ink placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none @error('institution') border-rose-400 @enderror">
                                    @error('institution') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="grade" class="block text-base font-semibold text-ink mb-2">{{ __('registration.form.grade') }} <span class="text-rose-500">*</span></label>
                                    <select id="grade" name="grade" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base text-ink focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none @error('grade') border-rose-400 @enderror">
                                        <option value="">{{ __('registration.form.select_grade') }}</option>
                                        @foreach($grades as $grade)
                                            <option value="{{ $grade }}" @selected(old('grade') === $grade)>{{ $grade }}</option>
                                        @endforeach
                                    </select>
                                    @error('grade') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-base font-semibold text-ink mb-2">{{ __('registration.form.email') }} <span class="text-rose-500">*</span></label>
                                    <input id="email" name="email" type="email" required maxlength="255" placeholder="{{ __('registration.form.placeholders.email') }}" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-ink placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none @error('email') border-rose-400 @enderror">
                                    @error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="phone" class="block text-base font-semibold text-ink mb-2">{{ __('registration.form.phone') }} <span class="text-rose-500">*</span></label>
                                    <input id="phone" name="phone" type="tel" required maxlength="20" placeholder="{{ __('registration.form.placeholders.phone') }}" value="{{ old('phone') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-ink placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none @error('phone') border-rose-400 @enderror">
                                    <p class="mt-2 text-sm text-slate-500">{{ __('registration.form.phone_hint') }}</p>
                                    @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="referred_by" class="block text-base font-semibold text-ink mb-2">{{ __('registration.form.referred_by') }} <span class="font-normal text-slate-400">{{ __('registration.form.referred_by_optional') }}</span></label>
                                    <input id="referred_by" name="referred_by" type="text" maxlength="255" placeholder="{{ __('registration.form.placeholders.referred_by') }}" value="{{ old('referred_by') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-ink placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none @error('referred_by') border-rose-400 @enderror">
                                    @error('referred_by') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="mt-7">
                                <p class="text-base font-semibold text-ink mb-3">{{ __('registration.form.language') }} <span class="text-rose-500">*</span></p>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($languages as $language)
                                        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-base text-ink cursor-pointer has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50">
                                            <input type="radio" name="language" value="{{ $language }}" required @checked(old('language') === $language) class="accent-teal-600">
                                            {{ $language }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('language') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </fieldset>

                        <div class="border-t border-slate-200 pt-8">
                            <h3 class="font-display text-xl font-semibold text-ink mb-1">{{ __('registration.form.payment') }}</h3>
                            <p class="text-sm text-slate-500 mb-5">{{ __('registration.form.payment_hint') }}</p>

                            <fieldset>
                                <legend class="text-base font-semibold text-ink mb-3">{{ __('registration.form.payment_method') }} <span class="text-rose-500">*</span></legend>
                                <div class="grid gap-3 sm:grid-cols-3">
                                    @foreach($paymentLabels as $value => $label)
                                        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-3 text-base text-ink cursor-pointer has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50">
                                            <input type="radio" name="payment_method" value="{{ $value }}" required @checked(old('payment_method', 'bank_transfer') === $value) class="accent-teal-600">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('payment_method') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </fieldset>

                            <div class="grid gap-6 sm:grid-cols-2 mt-6">
                                <div>
                                    <label for="payment_date" class="block text-base font-semibold text-ink mb-2">{{ __('registration.form.payment_date') }} <span class="text-rose-500">*</span></label>
                                    <input id="payment_date" name="payment_date" type="date" required value="{{ old('payment_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-ink focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none @error('payment_date') border-rose-400 @enderror">
                                    @error('payment_date') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="transaction_reference" class="block text-base font-semibold text-ink mb-2">{{ __('registration.form.reference') }} <span class="text-rose-500">*</span></label>
                                    <input id="transaction_reference" name="transaction_reference" type="text" required maxlength="255" placeholder="{{ __('registration.form.placeholders.reference') }}" value="{{ old('transaction_reference') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-ink placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none @error('transaction_reference') border-rose-400 @enderror">
                                    @error('transaction_reference') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="mt-6">
                                <label for="receipt" class="block text-base font-semibold text-ink mb-2">{{ __('registration.form.receipt') }} <span id="receipt-required" class="text-rose-500">*</span></label>
                                <input id="receipt" name="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-base text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-teal-700 hover:file:bg-teal-100 @error('receipt') border border-rose-400 @enderror" data-bank-hint="{{ __('registration.form.receipt_hint_bank') }}" data-optional-hint="{{ __('registration.form.receipt_hint') }}" data-image-label="{{ __('registration.js.image') }}" data-pdf-label="{{ __('registration.js.pdf') }}" data-preview-alt="{{ __('registration.alt.receipt_preview') }}" data-pdf-title="{{ __('registration.alt.pdf') }}">
                                <p id="receipt-hint" class="mt-2 text-sm text-slate-500">{{ __('registration.form.receipt_hint_bank') }}</p>

                                <div id="receipt-preview" class="mt-4 hidden rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-start gap-4">
                                        <div id="receipt-preview-media" class="shrink-0"></div>
                                        <div class="min-w-0 flex-1">
                                            <p id="receipt-preview-name" class="text-sm font-semibold text-ink break-all"></p>
                                            <p id="receipt-preview-size" class="text-sm text-slate-500"></p>
                                            <button type="button" id="receipt-preview-remove" class="mt-2 text-sm font-semibold text-rose-600 hover:text-rose-700 transition-colors">{{ __('registration.form.remove_file') }}</button>
                                        </div>
                                    </div>
                                </div>

                                @error('receipt') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-ink text-white font-semibold text-base hover:bg-ink-soft transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                {{ __('registration.form.submit') }} <span aria-hidden="true">→</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-ink text-white/70 py-9">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm sm:text-base">
            <span>{{ \App\Services\PlatformSettings::name() }} — {{ __('registration.footer.tagline') }}</span>
            <a href="{{ route('home') }}" class="font-semibold text-teal-300 hover:text-white transition-colors">{{ __('registration.footer.back_home') }} →</a>
        </div>
    </footer>

    <script>
        (function () {
            const header = document.getElementById('landing-header');
            const onScroll = () => header && header.classList.toggle('scrolled', window.scrollY > 40);
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            const menuBtn = document.getElementById('menu-btn');
            const menu = document.getElementById('mobile-menu');
            if (menuBtn && menu) {
                const close = () => {
                    menu.classList.remove('open');
                    menuBtn.classList.remove('active');
                    menuBtn.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                };
                menuBtn.addEventListener('click', () => {
                    const open = menu.classList.toggle('open');
                    menuBtn.classList.toggle('active', open);
                    menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                    document.body.style.overflow = open ? 'hidden' : '';
                });
                menu.querySelectorAll('a').forEach((a) => a.addEventListener('click', close));
            }

            const reveals = document.querySelectorAll('.reveal');
            if ('IntersectionObserver' in window && reveals.length) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12 });
                reveals.forEach((el) => observer.observe(el));
            } else {
                reveals.forEach((el) => el.classList.add('visible'));
            }

            document.querySelectorAll('.hero-animate').forEach((el, i) => {
                el.style.transitionDelay = (i * 120) + 'ms';
                requestAnimationFrame(() => el.classList.add('visible'));
            });
        })();
    </script>

    <script>
        (function () {
            const receipt = document.getElementById('receipt');
            const marker = document.getElementById('receipt-required');
            const hint = document.getElementById('receipt-hint');
            const preview = document.getElementById('receipt-preview');
            const previewMedia = document.getElementById('receipt-preview-media');
            const previewName = document.getElementById('receipt-preview-name');
            const previewSize = document.getElementById('receipt-preview-size');
            const removeButton = document.getElementById('receipt-preview-remove');
            if (!receipt || !marker || !hint || !preview || !previewMedia || !previewName || !previewSize || !removeButton) return;

            let objectUrl = null;

            const update = () => {
                const selected = document.querySelector('input[name="payment_method"]:checked');
                const isBank = selected && selected.value === 'bank_transfer';
                receipt.required = isBank;
                marker.hidden = !isBank;
                hint.textContent = isBank ? receipt.dataset.bankHint : receipt.dataset.optionalHint;
            };

            const clearPreview = () => {
                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }
                previewMedia.replaceChildren();
                previewName.textContent = '';
                previewSize.textContent = '';
                preview.classList.add('hidden');
            };

            const formatSize = (bytes) => bytes >= 1024 * 1024
                ? (bytes / (1024 * 1024)).toFixed(1) + ' MB'
                : Math.max(1, Math.round(bytes / 1024)) + ' KB';

            const renderPreview = (file) => {
                clearPreview();

                objectUrl = URL.createObjectURL(file);

                const isImage = file.type.startsWith('image/') || /\.(jpe?g|png)$/i.test(file.name);
                const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);

                if (isImage) {
                    const image = document.createElement('img');
                    image.src = objectUrl;
                    image.alt = receipt.dataset.previewAlt;
                    image.className = 'w-20 h-20 sm:w-24 sm:h-24 rounded-xl object-cover border border-slate-200';
                    previewMedia.appendChild(image);
                } else if (isPdf) {
                    const link = document.createElement('a');
                    link.href = objectUrl;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.title = receipt.dataset.pdfTitle;
                    link.className = 'inline-flex w-20 h-20 sm:w-24 sm:h-24 items-center justify-center rounded-xl border border-slate-200 bg-rose-50 text-3xl';
                    link.textContent = '📄';
                    previewMedia.appendChild(link);
                }

                previewName.textContent = file.name;
                previewSize.textContent = formatSize(file.size) + (isPdf ? ' · ' + receipt.dataset.pdfLabel : ' · ' + receipt.dataset.imageLabel);
                preview.classList.remove('hidden');
            };

            receipt.addEventListener('change', () => {
                const file = receipt.files && receipt.files[0];
                if (file) {
                    renderPreview(file);
                } else {
                    clearPreview();
                }
            });

            removeButton.addEventListener('click', () => {
                receipt.value = '';
                clearPreview();
            });

            document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
                input.addEventListener('change', update);
            });
            update();
            clearPreview();
        })();
    </script>
</body>
</html>
