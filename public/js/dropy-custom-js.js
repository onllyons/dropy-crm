// Dropy custom scripts
document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.getElementById('menuButton');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (!menuButton || !sidebar || !sidebarOverlay) {
        return;
    }

    const toggleSidebar = () => {
        const isHidden = sidebar.classList.contains('-translate-x-full');
        sidebar.classList.toggle('-translate-x-full', !isHidden);
        sidebarOverlay.classList.toggle('hidden', !isHidden);
    };

    menuButton.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);

    const offcanvasOpen = document.getElementById('offcanvasOpen');
    const offcanvasRight = document.getElementById('offcanvasRight');
    const offcanvasOverlay = document.getElementById('offcanvasOverlay');
    const offcanvasClose = document.getElementById('offcanvasClose');

    if (offcanvasOpen && offcanvasRight && offcanvasOverlay && offcanvasClose) {
        const openOffcanvas = () => {
            offcanvasRight.classList.remove('translate-x-full');
            offcanvasOverlay.classList.remove('hidden');
        };
        const closeOffcanvas = () => {
            offcanvasRight.classList.add('translate-x-full');
            offcanvasOverlay.classList.add('hidden');
        };

        offcanvasOpen.addEventListener('click', openOffcanvas);
        offcanvasOverlay.addEventListener('click', closeOffcanvas);
        offcanvasClose.addEventListener('click', closeOffcanvas);
    }

    const topLinksButton = document.getElementById('topLinksButton');
    const topLinksMenu = document.getElementById('topLinksMenu');

    if (topLinksButton && topLinksMenu) {
        const closeTopLinks = () => {
            topLinksMenu.classList.add('hidden');
            topLinksButton.setAttribute('aria-expanded', 'false');
        };

        const toggleTopLinks = (event) => {
            event.stopPropagation();
            const isHidden = topLinksMenu.classList.contains('hidden');
            topLinksMenu.classList.toggle('hidden', !isHidden);
            topLinksButton.setAttribute('aria-expanded', String(isHidden));
        };

        topLinksButton.addEventListener('click', toggleTopLinks);
        document.addEventListener('click', (event) => {
            if (topLinksMenu.contains(event.target) || topLinksButton.contains(event.target)) {
                return;
            }
            closeTopLinks();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeTopLinks();
            }
        });
    }

    const navToggles = document.querySelectorAll('[data-nav-toggle]');
    const navGroups = document.querySelectorAll('[data-nav-group]');
    let activeGroup = document.querySelector('[data-nav-group="home"]');

    const showGroup = (target) => {
        if (!target || target === activeGroup) {
            return;
        }

        const hideGroup = activeGroup;
        hideGroup.classList.add('nav-group--hidden');

        const onHideEnd = () => {
            hideGroup.classList.add('hidden');
            hideGroup.removeEventListener('transitionend', onHideEnd);
        };
        hideGroup.addEventListener('transitionend', onHideEnd);

        target.classList.remove('hidden');
        requestAnimationFrame(() => {
            target.classList.remove('nav-group--hidden');
        });

        activeGroup = target;
    };

    navToggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const targetName = toggle.getAttribute('data-nav-toggle');
            const targetGroup = document.querySelector(`[data-nav-group="${targetName}"]`);
            showGroup(targetGroup);
            navToggles.forEach((btn) => btn.classList.remove('is-active'));
            toggle.classList.add('is-active');
        });
    });
});
