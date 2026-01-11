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
});
