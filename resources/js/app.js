import Alpine from 'alpinejs';
window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    // Force sidebar to read localStorage before Alpine renders
    const sidebar = document.querySelector('[x-data]');
    if (sidebar && sidebar._x_dataStack) {
        sidebar._x_dataStack[0].collapsed =
            localStorage.getItem('sidebar_collapsed') === 'true';
    }
});

Alpine.start();
