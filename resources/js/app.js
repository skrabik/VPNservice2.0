import './bootstrap';
import Alpine from 'alpinejs';
import { createApp } from 'vue';
import AdminNotificationsTable from './components/AdminNotificationsTable.vue';

window.Alpine = Alpine;
Alpine.start();

function initializeVue() {
    if (document.querySelector('#admin-notifications-table')) {
        const app = createApp({});
        app.component('admin-notification', AdminNotificationsTable);
        app.mount('#admin-notifications-table');
    }
}

initializeVue();
document.addEventListener('turbo:load', () => {
    initializeVue();
});
