/**
 * Amari Theme — Frontend JavaScript
 *
 * Handles:
 *  - Mobile navigation toggle
 *  - Scroll animations (Intersection Observer)
 *  - Portfolio filter
 *  - Contact form AJAX submission
 *  - Scroll-to-top behaviour
 */

(function() {
    'use strict';

    /* ── Mobile Nav ─────────────────────────────────────────── */
    const toggle = document.querySelector('.amari-nav-toggle');
    const nav    = document.querySelector('.amari-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open);
        });
    }

    /* ── Scroll Animations ──────────────────────────────────── */
    const animatedEls = document.querySelectorAll('[data-amari-animate]');
    if (animatedEls.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        animatedEls.forEach(el => observer.observe(el));
    } else {
        animatedEls.forEach(el => el.classList.add('is-visible'));
    }

    /* ── Portfolio Filter ───────────────────────────────────── */
    document.querySelectorAll('.amari-portfolio-filter').forEach(filterBar => {
        const grid  = filterBar.nextElementSibling;
        if (!grid || !grid.classList.contains('amari-portfolio-grid')) return;
        const items = grid.querySelectorAll('.amari-portfolio-item');

        filterBar.querySelectorAll('.amari-filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                filterBar.querySelectorAll('.amari-filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;
                items.forEach(item => {
                    if (filter === '*') {
                        item.classList.remove('is-hidden');
                    } else {
                        const cats = (item.dataset.categories || '').split(' ');
                        if (cats.includes(filter)) {
                            item.classList.remove('is-hidden');
                        } else {
                            item.classList.add('is-hidden');
                        }
                    }
                });
            });
        });
    });

    /* ── Contact Form AJAX ──────────────────────────────────── */
    document.querySelectorAll('.amari-contact-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn      = form.querySelector('[type=submit]');
            const response = form.querySelector('.amari-form-response');
            const data     = new FormData(form);

            if (btn) { btn.disabled = true; btn.textContent = 'Sending...'; }
            if (response) response.style.display = 'none';

            fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: data,
            })
            .then(r => r.json())
            .then(res => {
                if (btn) { btn.disabled = false; btn.textContent = btn.dataset.originalText || 'Send Message'; }
                if (response) {
                    response.className = 'amari-form-response ' + (res.success ? 'success' : 'error');
                    response.textContent = res.data ? res.data.message : (res.success ? 'Message sent!' : 'Error occurred.');
                    response.style.display = 'block';
                }
                if (res.success) form.reset();
            })
            .catch(() => {
                if (btn) { btn.disabled = false; btn.textContent = btn.dataset.originalText || 'Send Message'; }
                if (response) {
                    response.className = 'amari-form-response error';
                    response.textContent = 'A network error occurred. Please try again.';
                    response.style.display = 'block';
                }
            });
        });

        // Store original button text
        const btn = form.querySelector('[type=submit]');
        if (btn) btn.dataset.originalText = btn.textContent;
    });

    /* ── Header scroll shadow ───────────────────────────────── */
    const header = document.querySelector('.amari-header');
    if (header) {
        const updateHeader = () => {
            header.style.boxShadow = window.scrollY > 10
                ? '0 2px 20px rgba(0,0,0,0.12)'
                : '0 1px 12px rgba(0,0,0,0.06)';
        };
        window.addEventListener('scroll', updateHeader, { passive: true });
    }

    /* ── Accordion ──────────────────────────────────────────── */
    document.querySelectorAll('.amari-accordion').forEach(accordion => {
        const allowMultiple = accordion.dataset.allowMultiple === '1';

        accordion.querySelectorAll('.amari-accordion-trigger').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const panel = document.getElementById(trigger.getAttribute('aria-controls'));
                const isOpen = trigger.getAttribute('aria-expanded') === 'true';

                // Close others if not allow-multiple
                if (!allowMultiple) {
                    accordion.querySelectorAll('.amari-accordion-trigger').forEach(t => {
                        if (t !== trigger) {
                            t.setAttribute('aria-expanded', 'false');
                            const p = document.getElementById(t.getAttribute('aria-controls'));
                            if (p) p.hidden = true;
                            // Rotate icon back
                            const icon = t.querySelector('.amari-accordion-icon');
                            if (icon) icon.style.transform = '';
                        }
                    });
                }

                // Toggle this one
                if (isOpen) {
                    trigger.setAttribute('aria-expanded', 'false');
                    if (panel) panel.hidden = true;
                    const icon = trigger.querySelector('.amari-accordion-icon');
                    if (icon) icon.style.transform = '';
                } else {
                    trigger.setAttribute('aria-expanded', 'true');
                    if (panel) panel.hidden = false;
                    const icon = trigger.querySelector('.amari-accordion-icon');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
            });
        });
    });

    /* ── Tabs ───────────────────────────────────────────────── */
    document.querySelectorAll('.amari-tabs').forEach(tabsWidget => {
        const tabList  = tabsWidget.querySelector('[role="tablist"]');
        const tabs     = tabsWidget.querySelectorAll('[role="tab"]');
        const panels   = tabsWidget.querySelectorAll('[role="tabpanel"]');

        if (!tabList) return;

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetId = tab.getAttribute('aria-controls');

                // Deactivate all
                tabs.forEach(t => {
                    t.setAttribute('aria-selected', 'false');
                    t.classList.remove('active');
                });
                panels.forEach(p => p.hidden = true);

                // Activate clicked
                tab.setAttribute('aria-selected', 'true');
                tab.classList.add('active');
                const panel = document.getElementById(targetId);
                if (panel) panel.hidden = false;
            });

            // Keyboard navigation
            tab.addEventListener('keydown', e => {
                const tabArr = [...tabs];
                const idx    = tabArr.indexOf(tab);
                if (e.key === 'ArrowRight') { e.preventDefault(); tabArr[(idx + 1) % tabArr.length].click(); }
                if (e.key === 'ArrowLeft')  { e.preventDefault(); tabArr[(idx - 1 + tabArr.length) % tabArr.length].click(); }
                if (e.key === 'Home')       { e.preventDefault(); tabArr[0].click(); }
                if (e.key === 'End')        { e.preventDefault(); tabArr[tabArr.length - 1].click(); }
            });
        });
    });

    /* ── Counter Animation ──────────────────────────────────── */
    const counterEls = document.querySelectorAll('.amari-counter-number[data-target]');
    if (counterEls.length && 'IntersectionObserver' in window) {
        const counterObs = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                counterObs.unobserve(entry.target);

                const el       = entry.target;
                const target   = parseFloat(el.dataset.target) || 0;
                const duration = parseInt(el.dataset.duration, 10) || 2000;
                const suffix   = el.dataset.suffix || '';
                const isFloat  = target !== Math.floor(target);
                const start    = performance.now();

                const tick = (now) => {
                    const elapsed  = Math.min(now - start, duration);
                    const progress = elapsed / duration;
                    // Ease out cubic
                    const ease     = 1 - Math.pow(1 - progress, 3);
                    const current  = target * ease;
                    el.textContent = (isFloat ? current.toFixed(1) : Math.round(current)) + suffix;
                    if (elapsed < duration) requestAnimationFrame(tick);
                    else el.textContent = target + suffix;
                };
                requestAnimationFrame(tick);
            });
        }, { threshold: 0.4 });

        counterEls.forEach(el => counterObs.observe(el));
    } else {
        // Fallback: show final values immediately
        counterEls.forEach(el => {
            el.textContent = (el.dataset.target || '0') + (el.dataset.suffix || '');
        });
    }

    /* ── Slider ─────────────────────────────────────────────── */
    document.querySelectorAll('.amari-slider').forEach(slider => {
        const track      = slider.querySelector('.amari-slider-track');
        const slides     = slider.querySelectorAll('.amari-slide');
        const prevBtn    = slider.querySelector('.amari-slider-prev');
        const nextBtn    = slider.querySelector('.amari-slider-next');
        const dotsWrap   = slider.querySelector('.amari-slider-dots');
        const dots       = dotsWrap ? dotsWrap.querySelectorAll('.amari-dot') : [];
        const total      = slides.length;
        const autoplay   = slider.dataset.autoplay === '1';
        const interval   = parseInt(slider.dataset.interval, 10) || 5000;
        const transition = slider.dataset.transition || 'slide';

        if (!track || total === 0) return;

        let current = 0;
        let timer   = null;

        const goTo = (index) => {
            const prev = current;
            current = (index + total) % total;

            if (transition === 'fade') {
                slides[prev].classList.remove('active');
                slides[current].classList.add('active');
            } else {
                track.style.transform = `translateX(-${current * 100}%)`;
            }

            // Dots
            dots.forEach((d, i) => d.classList.toggle('active', i === current));
        };

        // Init
        if (transition === 'fade') {
            slides.forEach((s, i) => {
                s.style.position = 'absolute';
                s.style.top = '0'; s.style.left = '0'; s.style.width = '100%'; s.style.height = '100%';
                s.style.opacity = i === 0 ? '1' : '0';
                s.style.transition = 'opacity 0.6s ease';
                s.classList.toggle('active', i === 0);
            });
            // Override goTo for fade
            goTo._fade = true;
            const fadeGoTo = (index) => {
                slides[current].style.opacity = '0'; slides[current].classList.remove('active');
                current = (index + total) % total;
                slides[current].style.opacity = '1'; slides[current].classList.add('active');
                dots.forEach((d, i) => d.classList.toggle('active', i === current));
            };
            slider._goTo = fadeGoTo;
        } else {
            track.style.display        = 'flex';
            track.style.transition     = 'transform 0.5s cubic-bezier(.4,0,.2,1)';
            slides.forEach(s => { s.style.minWidth = '100%'; s.style.flex = '0 0 100%'; });
            slider._goTo = goTo;
        }

        const navigate = (index) => {
            if (slider._goTo) slider._goTo(index); else goTo(index);
            if (autoplay) restartTimer();
        };

        if (prevBtn) prevBtn.addEventListener('click', () => navigate(current - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => navigate(current + 1));

        dots.forEach((dot, i) => dot.addEventListener('click', () => navigate(i)));

        // Keyboard
        slider.setAttribute('tabindex', '0');
        slider.addEventListener('keydown', e => {
            if (e.key === 'ArrowLeft')  navigate(current - 1);
            if (e.key === 'ArrowRight') navigate(current + 1);
        });

        // Touch / swipe
        let touchStartX = 0;
        slider.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
        slider.addEventListener('touchend',   e => {
            const diff = touchStartX - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 40) navigate(diff > 0 ? current + 1 : current - 1);
        });

        const startTimer = () => { timer = setInterval(() => navigate(current + 1), interval); };
        const restartTimer = () => { clearInterval(timer); startTimer(); };
        if (autoplay) startTimer();

        // Pause on hover
        slider.addEventListener('mouseenter', () => clearInterval(timer));
        slider.addEventListener('mouseleave', () => { if (autoplay) startTimer(); });
    });

})();
