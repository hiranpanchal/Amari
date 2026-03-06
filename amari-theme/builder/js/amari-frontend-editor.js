/**
 * Amari Frontend Editor — JS bootstrap
 *
 * Wires the floating edit bar to the builder overlay on the live frontend.
 * The main builder engine (amari-builder.js) is loaded as a dependency,
 * so AmariBuilderController is already available.
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const openBtn    = document.getElementById('amari-fe-open');
        const dismissBtn = document.getElementById('amari-fe-dismiss');
        const bar        = document.getElementById('amari-fe-bar');

        if (!openBtn) return;

        // Init the builder controller (same one as admin)
        if (typeof AmariBuilderController === 'function') {
            window.AmariBuilder = new AmariBuilderController();
            window.AmariBuilder.init();
        }

        // Open builder
        openBtn.addEventListener('click', function() {
            if (window.AmariBuilder) {
                window.AmariBuilder.open();
                bar && bar.classList.add('is-hidden');
            }
        });

        // After builder closes, reload the page to show fresh content
        const overlay = document.getElementById('amari-builder-overlay');
        if (overlay) {
            const origClose = window.AmariBuilder?.close?.bind(window.AmariBuilder);
            if (origClose) {
                window.AmariBuilder.close = function(skipConfirm) {
                    origClose(skipConfirm);
                    bar && bar.classList.remove('is-hidden');
                    // Reload if saved
                    if (!window.AmariBuilder.isDirty) {
                        window.location.reload();
                    }
                };
            }
        }

        // Dismiss bar
        dismissBtn && dismissBtn.addEventListener('click', function() {
            bar && bar.classList.add('is-hidden');
            // Remember for this session
            try { sessionStorage.setItem('amari_fe_bar_dismissed', '1'); } catch(e) {}
        });

        // Restore dismissed state
        try {
            if (sessionStorage.getItem('amari_fe_bar_dismissed') === '1') {
                bar && bar.classList.add('is-hidden');
            }
        } catch(e) {}

        // Keyboard shortcut: Alt+E to open editor
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.key === 'e') {
                e.preventDefault();
                openBtn.click();
            }
        });
    });
})();
