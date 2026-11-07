/*!
 * Start Bootstrap - SB Admin Pro v2.0.5
 * Copyright 2013-2023 Start Bootstrap
 * Licensed under SEE_LICENSE (https://github.com/StartBootstrap/sb-admin-pro/blob/master/LICENSE)
 */
(function ($, window, document) {
    'use strict';

    $(function () {
        if (window.feather && typeof window.feather.replace === 'function') {
            window.feather.replace();
        }

        if (window.bootstrap) {
            $('[data-bs-toggle="tooltip"]').each(function () {
                if (!window.bootstrap.Tooltip.getInstance(this)) {
                    window.bootstrap.Tooltip.getOrCreateInstance(this);
                }
            });
            $('[data-bs-toggle="popover"]').each(function () {
                if (!window.bootstrap.Popover.getInstance(this)) {
                    window.bootstrap.Popover.getOrCreateInstance(this);
                }
            });
        }

        $(document).off('click.adminLayout', '#sidebarToggle')
            .on('click.adminLayout', '#sidebarToggle', function (event) {
                event.preventDefault();
                $('body').toggleClass('sidenav-toggled');
                try {
                    window.localStorage.setItem(
                        'sb|sidebar-toggle',
                        $('body').hasClass('sidenav-toggled') ? 'true' : 'false'
                    );
                } catch (error) {
                    // The sidebar remains usable when storage is unavailable.
                }
            });

        $('#layoutSidenav_content').off('click.adminLayout').on('click.adminLayout', function () {
            if (window.innerWidth < 992 && $('body').hasClass('sidenav-toggled')) {
                $('body').removeClass('sidenav-toggled');
            }
        });

        var match = window.location.pathname.match(/([\w-]+\.html)/);
        var activatedPath = match ? match[0] : 'index.html';
        $('.nav-link').filter(function () {
            return $(this).attr('href') === activatedPath;
        }).each(function () {
            var $link = $(this).addClass('active');
            $link.parents('.collapse').each(function () {
                var $collapse = $(this).addClass('show');
                $('[data-bs-target="#' + $collapse.attr('id') + '"]')
                    .removeClass('collapsed').addClass('active');
            });
        });
    });
})(jQuery, window, document);
