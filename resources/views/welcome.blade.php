<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('landing.meta.title', ['name' => \App\Services\PlatformSettings::name()]) }}</title>
        <meta name="description" content="{{ __('landing.meta.description', ['name' => \App\Services\PlatformSettings::name()]) }}">

        <link rel="icon" href="{{ \App\Services\PlatformSettings::logoUrl() }}">
        <link rel="apple-touch-icon" href="{{ \App\Services\PlatformSettings::logoUrl() }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/landing.css'])
    </head>
    <body>
        @include('partials.landing.header')
        @include('partials.landing.hero')
        @include('partials.landing.showcase')
        @include('partials.landing.features')
        @include('partials.landing.portals')
        @include('partials.landing.about')
        @include('partials.landing.cta')
        @include('partials.landing.contact')
        @include('partials.landing.footer')

        <script>
            (function () {
                /* Fixed header background on scroll */
                const header = document.getElementById('landing-header');
                const onScroll = () => header && header.classList.toggle('scrolled', window.scrollY > 40);
                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();

                /* Mobile menu */
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

                /* Dashboard showcase: tabs + auto-cycle */
                const showcase = document.getElementById('dashboards');
                if (showcase) {
                    const tabs = Array.from(showcase.querySelectorAll('[data-tab]'));
                    const dots = Array.from(showcase.querySelectorAll('[data-dot]'));
                    const infos = Array.from(showcase.querySelectorAll('[data-info]'));
                    const his = Array.from(showcase.querySelectorAll('[data-highlights]'));
                    const panels = Array.from(showcase.querySelectorAll('[data-panel]'));
                    let idx = 0;
                    let timer = null;

                    const render = (i) => {
                        idx = i;
                        tabs.forEach((t, k) => {
                            const on = k === i;
                            if (on) {
                                t.style.background = t.dataset.grad;
                                t.style.color = '#fff';
                                t.style.boxShadow = '0 4px 14px rgba(0,0,0,0.18)';
                            } else {
                                t.style.background = '';
                                t.style.color = '';
                                t.style.boxShadow = '';
                            }
                            t.setAttribute('aria-pressed', on ? 'true' : 'false');
                        });
                        dots.forEach((d, k) => {
                            d.classList.toggle('dot-on', k === i);
                            d.classList.toggle('dot-off', k !== i);
                            d.style.background = k === i ? d.dataset.grad : '';
                        });
                        infos.forEach((el, k) => el.hidden = k !== i);
                        his.forEach((el, k) => el.hidden = k !== i);
                        panels.forEach((el, k) => el.hidden = k !== i);
                    };

                    const start = () => { timer = setInterval(() => render((idx + 1) % tabs.length), 3500); };
                    const go = (i) => { render(i); clearInterval(timer); start(); };
                    tabs.forEach((t, k) => t.addEventListener('click', () => go(k)));
                    dots.forEach((d, k) => d.addEventListener('click', () => go(k)));
                    render(0);
                    start();
                }

                /* Reveal on scroll */
                const reveals = document.querySelectorAll('.reveal');
                if ('IntersectionObserver' in window && reveals.length) {
                    const io = new IntersectionObserver((entries) => {
                        entries.forEach((e) => {
                            if (e.isIntersecting) {
                                e.target.classList.add('visible');
                                io.unobserve(e.target);
                            }
                        });
                    }, { threshold: 0.12 });
                    reveals.forEach((el) => io.observe(el));
                } else {
                    reveals.forEach((el) => el.classList.add('visible'));
                }

                /* Contact form: submit without reloading the page */
                const contactForm = document.getElementById('contact-form');
                if (contactForm) {
                    const feedback = document.getElementById('contact-form-feedback');
                    const submitButton = contactForm.querySelector('button[type="submit"]');
                    const fields = ['name', 'email', 'phone', 'message'];

                    const setFeedback = (message, variant) => {
                        if (!feedback || !message) return;
                        feedback.textContent = message;
                        feedback.classList.remove(
                            'hidden', 'border-teal-200', 'bg-teal-50', 'text-teal-700',
                            'border-rose-200', 'bg-rose-50', 'text-rose-700',
                        );
                        if (variant === 'success') {
                            feedback.classList.add('border-teal-200', 'bg-teal-50', 'text-teal-700');
                        } else {
                            feedback.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-700');
                        }
                    };

                    const clearErrors = () => {
                        fields.forEach((field) => {
                            const input = contactForm.elements.namedItem(field);
                            const error = contactForm.querySelector('[data-field-error="' + field + '"]');
                            if (input && input.classList) {
                                input.removeAttribute('aria-invalid');
                                input.classList.remove('border-rose-400');
                            }
                            if (error) error.remove();
                        });
                    };

                    const showFieldError = (field, message) => {
                        const input = contactForm.elements.namedItem(field);
                        if (!input) return;
                        let error = contactForm.querySelector('[data-field-error="' + field + '"]');
                        if (!error) {
                            error = document.createElement('p');
                            error.dataset.fieldError = field;
                            const container = input.closest('div') || contactForm;
                            container.appendChild(error);
                        }
                        error.textContent = message;
                        error.classList.add('mt-1.5', 'text-xs', 'text-rose-600');
                        if (input.classList) input.classList.add('border-rose-400');
                        input.setAttribute('aria-invalid', 'true');
                    };

                    contactForm.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        clearErrors();

                        const csrf = contactForm.querySelector('input[name="_token"]');
                        const headers = {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        };
                        if (csrf) headers['X-CSRF-TOKEN'] = csrf.value;

                        if (submitButton) submitButton.disabled = true;

                        try {
                            const response = await fetch(contactForm.action, {
                                method: 'POST',
                                headers,
                                body: new FormData(contactForm),
                            });
                            const payload = await response.json().catch(() => ({}));

                            if (response.ok) {
                                setFeedback(payload.message || contactForm.dataset.success, 'success');
                                contactForm.reset();
                                return;
                            }

                            const errors = payload.errors || {};
                            let hasFieldError = false;

                            Object.keys(errors).forEach((field) => {
                                if (!fields.includes(field)) return;
                                const message = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                                showFieldError(field, message);
                                hasFieldError = true;
                            });

                            const generalMessage = errors.contact
                                ? (Array.isArray(errors.contact) ? errors.contact[0] : errors.contact)
                                : payload.message;

                            setFeedback(
                                generalMessage || (hasFieldError ? contactForm.dataset.error : null),
                                'error',
                            );
                        } catch (error) {
                            setFeedback(contactForm.dataset.error, 'error');
                        } finally {
                            if (submitButton) submitButton.disabled = false;
                        }
                    });
                }

                /* Hero staggered entrance */
                document.querySelectorAll('.hero-animate').forEach((el, i) => {
                    el.style.transitionDelay = (i * 120) + 'ms';
                    requestAnimationFrame(() => el.classList.add('visible'));
                });
            })();
        </script>
    </body>
</html>