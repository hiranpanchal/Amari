/**
 * Amari Mega Menu — Frontend JS
 * Handles: keyboard navigation, touch/click toggle, escape close, focus trap.
 * @version 3.0.0
 */
(function () {
    'use strict';

    /* ----------------------------------------------------------------
       Constants & helpers
    ---------------------------------------------------------------- */

    const BREAKPOINT   = 1024;
    const OPEN_CLASS   = 'amari-mega-open';
    const SUB_OPEN     = 'amari-sub-open';

    const isMobile = () => window.innerWidth <= BREAKPOINT;

    /** Return the closest ancestor matching selector */
    const closest = (el, sel) => el && el.closest ? el.closest(sel) : null;

    /* ----------------------------------------------------------------
       Grab all top-level mega items
    ---------------------------------------------------------------- */

    const init = () => {
        const navMenus = document.querySelectorAll('.amari-has-mega');
        if (!navMenus.length) return;

        navMenus.forEach(li => setupMegaItem(li));
        setupKeyboardNav();
        setupOutsideClick();
        setupEscape();
    };

    /* ----------------------------------------------------------------
       Setup a single mega-menu parent <li>
    ---------------------------------------------------------------- */

    const setupMegaItem = (li) => {
        const link     = li.querySelector(':scope > a');
        const dropdown = li.querySelector(':scope > .amari-mega-dropdown');
        if (!link || !dropdown) return;

        // Desktop: hover is handled by CSS alone.
        // Mobile: toggle on click
        link.addEventListener('click', (e) => {
            if (!isMobile()) return; // let CSS hover handle it on desktop

            e.preventDefault();
            const isOpen = li.classList.contains(OPEN_CLASS);
            closeAll();
            if (!isOpen) {
                li.classList.add(OPEN_CLASS);
                link.setAttribute('aria-expanded', 'true');
            }
        });
    };

    /* ----------------------------------------------------------------
       Standard sub-menu toggle on mobile
    ---------------------------------------------------------------- */

    document.querySelectorAll('.amari-nav-menu li:not(.amari-has-mega)').forEach(li => {
        const sub  = li.querySelector(':scope > .sub-menu');
        const link = li.querySelector(':scope > a');
        if (!sub || !link) return;

        link.addEventListener('click', (e) => {
            if (!isMobile()) return;
            e.preventDefault();
            const isOpen = li.classList.contains(SUB_OPEN);
            // Close siblings
            li.parentElement.querySelectorAll('.' + SUB_OPEN).forEach(el => el.classList.remove(SUB_OPEN));
            if (!isOpen) li.classList.add(SUB_OPEN);
        });
    });

    /* ----------------------------------------------------------------
       Keyboard navigation (WCAG 2.1 menu pattern)
    ---------------------------------------------------------------- */

    const setupKeyboardNav = () => {
        const navMenuEl = document.querySelector('.amari-nav-menu');
        if (!navMenuEl) return;

        navMenuEl.addEventListener('keydown', (e) => {
            const focused = document.activeElement;
            if (!navMenuEl.contains(focused)) return;

            const parentLi   = closest(focused, '.amari-has-mega');
            const dropdown   = parentLi?.querySelector(':scope > .amari-mega-dropdown');
            const isTopLink  = focused.parentElement?.classList.contains('amari-has-mega');

            switch (e.key) {
                case 'Enter':
                case ' ':
                    if (isTopLink && dropdown) {
                        e.preventDefault();
                        const isOpen = parentLi.classList.contains(OPEN_CLASS);
                        closeAll();
                        if (!isOpen) {
                            parentLi.classList.add(OPEN_CLASS);
                            focused.setAttribute('aria-expanded', 'true');
                            // Focus first focusable item in dropdown
                            const firstLink = dropdown.querySelector('a');
                            if (firstLink) firstLink.focus();
                        }
                    }
                    break;

                case 'Escape':
                    if (parentLi && parentLi.classList.contains(OPEN_CLASS)) {
                        e.preventDefault();
                        parentLi.classList.remove(OPEN_CLASS);
                        const topLink = parentLi.querySelector(':scope > a');
                        if (topLink) topLink.focus();
                        topLink?.setAttribute('aria-expanded', 'false');
                    }
                    break;

                case 'Tab':
                    // Let the browser handle Tab — just close when focus leaves the nav
                    requestAnimationFrame(() => {
                        if (!navMenuEl.contains(document.activeElement)) closeAll();
                    });
                    break;

                case 'ArrowDown':
                    if (isTopLink && dropdown) {
                        e.preventDefault();
                        parentLi.classList.add(OPEN_CLASS);
                        focused.setAttribute('aria-expanded', 'true');
                        const firstLink = dropdown.querySelector('a');
                        if (firstLink) firstLink.focus();
                    }
                    break;

                case 'ArrowUp':
                    if (parentLi && dropdown?.contains(focused)) {
                        e.preventDefault();
                        const links = [...dropdown.querySelectorAll('a')];
                        const idx   = links.indexOf(focused);
                        if (idx === 0) {
                            // Move back to top link
                            parentLi.querySelector(':scope > a')?.focus();
                        } else if (idx > 0) {
                            links[idx - 1].focus();
                        }
                    }
                    break;

                case 'ArrowRight':
                    if (parentLi && dropdown?.contains(focused)) {
                        e.preventDefault();
                        // Move to first link of next column
                        const cols     = [...dropdown.querySelectorAll('.amari-mega-col')];
                        const thisCol  = closest(focused, '.amari-mega-col');
                        const colIdx   = cols.indexOf(thisCol);
                        const nextCol  = cols[colIdx + 1];
                        if (nextCol) nextCol.querySelector('a')?.focus();
                    }
                    break;

                case 'ArrowLeft':
                    if (parentLi && dropdown?.contains(focused)) {
                        e.preventDefault();
                        const cols    = [...dropdown.querySelectorAll('.amari-mega-col')];
                        const thisCol = closest(focused, '.amari-mega-col');
                        const colIdx  = cols.indexOf(thisCol);
                        const prevCol = cols[colIdx - 1];
                        if (prevCol) {
                            const prevLinks = [...prevCol.querySelectorAll('a')];
                            prevLinks[prevLinks.length - 1]?.focus();
                        }
                    }
                    break;
            }
        });
    };

    /* ----------------------------------------------------------------
       Close all open mega menus
    ---------------------------------------------------------------- */

    const closeAll = () => {
        document.querySelectorAll('.' + OPEN_CLASS).forEach(li => {
            li.classList.remove(OPEN_CLASS);
            const link = li.querySelector(':scope > a');
            link?.setAttribute('aria-expanded', 'false');
        });
    };

    /* ----------------------------------------------------------------
       Click outside to close
    ---------------------------------------------------------------- */

    const setupOutsideClick = () => {
        document.addEventListener('click', (e) => {
            const inNav = closest(e.target, '.amari-nav-menu');
            if (!inNav) closeAll();
        });
    };

    /* ----------------------------------------------------------------
       Global Escape key
    ---------------------------------------------------------------- */

    const setupEscape = () => {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeAll();
        });
    };

    /* ----------------------------------------------------------------
       Touch / pointer: prevent hover-stuck menus on touchscreen desktops
    ---------------------------------------------------------------- */

    let lastPointerType = 'mouse';
    document.addEventListener('pointerdown', (e) => {
        lastPointerType = e.pointerType;
    });

    /* ----------------------------------------------------------------
       Boot
    ---------------------------------------------------------------- */

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
