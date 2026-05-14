(function () {
    'use strict';

    // Sidebar toggle — páginas privadas
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar       = document.getElementById('sidebar');
    var overlay       = document.getElementById('sidebarOverlay');
    if (sidebarToggle && sidebar && overlay) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            });
        });
    }

    // Mobile nav — páginas públicas
    var navToggle = document.getElementById('navbarToggle');
    var navMenu   = document.getElementById('navbarMobileMenu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (navMenu.classList.contains('open') &&
                !navToggle.contains(e.target) &&
                !navMenu.contains(e.target)) {
                navMenu.classList.remove('open');
            }
        });
    }
})();
