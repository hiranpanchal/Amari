/**
 * Amari Mega Menu — Admin JS (nav-menus.php)
 * Handles: show/hide mega options when checkbox toggled,
 *          re-init on AJAX-loaded new menu items.
 * @version 3.0.0
 */
(function ($) {
    'use strict';

    const init = () => {
        // Show/hide mega options panel when checkbox changes
        $(document).on('change', '.amari-mega-checkbox', function () {
            const $options = $(this).closest('.amari-mega-fields').find('.amari-mega-options');
            if ($(this).is(':checked')) {
                $options.slideDown(180);
            } else {
                $options.slideUp(180);
            }
        });
    };

    // Run on load and whenever WordPress re-renders menu items via AJAX
    $(document).ready(init);
    $(document).on('menu-item-added', init);

}(jQuery));
