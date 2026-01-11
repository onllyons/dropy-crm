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
});
