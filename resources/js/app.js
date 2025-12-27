import './bootstrap';
import '../scss/config/default/app.scss';
import '@vueform/slider/themes/default.css';
import '../scss/mermaid.min.css';
import 'animate.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import BootstrapVueNext from 'bootstrap-vue-next';
import vClickOutside from "click-outside-vue3";
import VueApexCharts from "vue3-apexcharts";
import VueFeather from 'vue-feather';
import VueTheMask from 'vue-the-mask';

import AOS from 'aos';
import 'aos/dist/aos.css';
import Choices from 'choices.js';

import store from "./state/store";
import i18n from './i18n'

AOS.init({
    easing: 'ease-out-back',
    duration: 1000
});

createInertiaApp({
    title: title => title ? `${title} | WCode` : 'WCode',
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(store)
            .use(i18n)
            .use(ZiggyVue)
            .use(BootstrapVueNext)
            .use(VueApexCharts)
            .use(VueTheMask)
            .use(vClickOutside)
            .component(VueFeather.type, VueFeather)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

function initChoices() {
    const selects = document.querySelectorAll('select[data-choices]');
    selects.forEach((el) => {
        if (el.dataset.choicesInitialized === 'true') return;
        try {
            const isMultiple = el.hasAttribute('multiple');
            const instance = new Choices(el, {
                searchEnabled: true,
                searchChoices: true,
                removeItemButton: isMultiple,
                shouldSort: false,
                searchFields: ['label', 'value'],
                noResultsText: 'Nem resultado encontrado',
                fuseOptions: {
                    threshold: 0.0,
                    ignoreLocation: true,
                    minMatchCharLength: 1
                }
            });
            el.dataset.choicesInitialized = 'true';
            el._choicesInstance = instance;
        } catch (e) {
            console.error('Choices init error:', e);
        }
    });
}

document.addEventListener('inertia:finish', () => {
    setTimeout(initChoices, 0);
});
window.addEventListener('load', () => {
    setTimeout(initChoices, 0);
});
window.initChoices = initChoices;
