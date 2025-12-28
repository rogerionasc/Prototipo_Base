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
    setTimeout(() => {
        initChoices();
        autoSyncChoices();
        setupChoicesObserver();
        syncInvalidStateForChoices();
    }, 0);
});
window.addEventListener('load', () => {
    setTimeout(() => {
        initChoices();
        autoSyncChoices();
        setupChoicesObserver();
        syncInvalidStateForChoices();
    }, 0);
});
window.initChoices = initChoices;
function autoSyncChoices() {
    try {
        const selects = document.querySelectorAll('select[data-choices]');
        selects.forEach((el) => {
            const v = el.value != null ? String(el.value) : '';
            syncChoiceValue(el, v);
        });
    } catch (e) {
        console.error('Choices auto-sync error:', e);
    }
}
window.autoSyncChoices = autoSyncChoices;
function syncChoiceValue(el, value) {
    try {
        if (!el) return;
        const v = value != null ? String(value) : '';
        const inst = el._choicesInstance || el.choices;
        el.value = v;
        if (inst && typeof inst.setChoiceByValue === 'function') inst.setChoiceByValue(v);
        el.dispatchEvent(new Event('change', { bubbles: true }));
    } catch (e) {
        console.error('Choices sync error:', e);
    }
}
window.syncChoiceValue = syncChoiceValue;

function setupChoicesObserver() {
    try {
        if (window._choicesObserver) return;
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((m) => {
                m.addedNodes.forEach((node) => {
                    if (!(node instanceof Element)) return;
                    const targets = [];
                    if (node.matches && node.matches('select[data-choices]')) {
                        targets.push(node);
                    }
                    const nested = node.querySelectorAll ? node.querySelectorAll('select[data-choices]') : [];
                    nested.forEach((el) => targets.push(el));
                    targets.forEach((el) => {
                        if (el.dataset.choicesInitialized === 'true') return;
                        try {
                            const isMultiple = el.hasAttribute('multiple');
                            const inst = new Choices(el, {
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
                            el._choicesInstance = inst;
                            const v = el.value != null ? String(el.value) : '';
                            syncChoiceValue(el, v);
                            setupInvalidClassObserver(el);
                            mirrorInvalidToWrapper(el);
                        } catch (e) {
                            console.error('Choices init error:', e);
                        }
                    });
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
        window._choicesObserver = observer;
    } catch (e) {
        console.error('Choices observer error:', e);
    }
}
window.setupChoicesObserver = setupChoicesObserver;

function mirrorInvalidToWrapper(el) {
    try {
        const wrapper = el.closest('.choices') || el.parentElement;
        if (!wrapper) return;
        if (el.classList.contains('is-invalid')) {
            wrapper.classList.add('is-invalid');
        } else {
            wrapper.classList.remove('is-invalid');
        }
    } catch (e) {
        console.error('Choices invalid mirror error:', e);
    }
}

function setupInvalidClassObserver(el) {
    try {
        if (el._invalidObserver) return;
        const obs = new MutationObserver(() => {
            mirrorInvalidToWrapper(el);
        });
        obs.observe(el, { attributes: true, attributeFilter: ['class'] });
        el._invalidObserver = obs;
        setupValidityEvents(el);
    } catch (e) {
        console.error('Choices invalid observer error:', e);
    }
}

function syncInvalidStateForChoices() {
    try {
        const selects = document.querySelectorAll('select[data-choices]');
        selects.forEach((el) => {
            mirrorInvalidToWrapper(el);
            setupInvalidClassObserver(el);
        });
    } catch (e) {
        console.error('Choices invalid sync error:', e);
    }
}
window.syncInvalidStateForChoices = syncInvalidStateForChoices;

function setupValidityEvents(el) {
    try {
        if (el._validityEventsBound) return;
        const handler = () => {
            const wrapper = el.closest('.choices') || el.parentElement;
            if (!wrapper) return;
            if (typeof el.checkValidity === 'function' && !el.checkValidity()) {
                wrapper.classList.add('is-invalid');
            } else {
                wrapper.classList.remove('is-invalid');
            }
        };
        el.addEventListener('invalid', handler, true);
        el.addEventListener('change', handler, true);
        el.addEventListener('input', handler, true);
        el._validityEventsBound = true;
    } catch (e) {
        console.error('Choices validity events error:', e);
    }
}
