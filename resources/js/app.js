import './bootstrap';
import '../scss/config/default/app.scss';
import '@vueform/slider/themes/default.css';
import '../scss/mermaid.min.css';
import 'animate.css';

try {
    if (!window.__ignoreExtensionMessageChannelClosed) {
        window.__ignoreExtensionMessageChannelClosed = true;
        window.addEventListener('unhandledrejection', (event) => {
            try {
                const r = event?.reason;
                const s = typeof r === 'string' ? r : String(r?.message || r || '');
                if (s && (s.includes('A listener indicated an asynchronous response by returning true') || s.includes('message channel closed'))) {
                    if (event && event.preventDefault) event.preventDefault();
                }
            } catch (e) {}
        });
    }
} catch (e) {}

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

function getChoicesConfig(el) {
    const isMultiple = !!el?.hasAttribute?.('multiple');
    const disableSearch = !!el?.hasAttribute?.('data-choices-search-false');
    return {
        searchEnabled: !disableSearch,
        searchChoices: !disableSearch,
        removeItemButton: isMultiple,
        shouldSort: false,
        searchFields: ['label', 'value'],
        noResultsText: 'Nem resultado encontrado',
        placeholder: true,
        placeholderValue: 'Selecione',
        position: 'bottom',
        fuseOptions: {
            threshold: 0.0,
            ignoreLocation: true,
            minMatchCharLength: 1
        }
    };
}

function ensureChoicesInstance(el) {
    try {
        if (!el) return null;
        if (el.dataset.choicesInitialized === 'true' || el._choicesInstance) return el._choicesInstance || el.choices || null;
        cleanupChoicesSiblings(el);
        const instance = new Choices(el, getChoicesConfig(el));
        el.dataset.choicesInitialized = 'true';
        el._choicesInstance = instance;
        setupInvalidClassObserver(el);
        mirrorInvalidToWrapper(el);
        try { el.style.display = 'none'; } catch (_) {}
        return instance;
    } catch (e) {
        console.error('Choices init error:', e);
        return null;
    }
}

function initChoices() {
    const selects = document.querySelectorAll('select[data-choices]');
    selects.forEach((el) => {
        ensureChoicesInstance(el);
    });
}
function initChoiceEl(el) {
    try {
        if (!el) return;
        ensureChoicesInstance(el);
        const v = el.value != null ? String(el.value) : '';
        syncChoiceValue(el, v);
    } catch (e) {
        console.error('Choices init error:', e);
    }
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
window.initChoiceEl = initChoiceEl;
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
        const isMultiple = el.hasAttribute('multiple');
        const inst = el._choicesInstance || el.choices;
        if (isMultiple) {
            let vals = [];
            if (Array.isArray(value)) {
                vals = value.map(v => String(v)).filter(Boolean);
            } else {
                const s = value != null ? String(value) : '';
                if (s.includes(',')) {
                    vals = s.split(',').map(x => String(x).trim()).filter(Boolean);
                } else {
                    vals = Array.from(el.selectedOptions || []).map(o => String(o.value)).filter(Boolean);
                }
            }
            if (inst && typeof inst.removeActiveItems === 'function') {
                try { inst.removeActiveItems(); } catch (_) {}
            }
            if (inst && typeof inst.setChoiceByValue === 'function') {
                inst.setChoiceByValue(vals);
            } else {
                Array.from(el.options || []).forEach(opt => { opt.selected = vals.includes(String(opt.value)); });
            }
        } else {
            const v = value != null ? String(value) : '';
            el.value = v;
            if (inst && typeof inst.setChoiceByValue === 'function') inst.setChoiceByValue(v);
        }
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
                        if (el.dataset.choicesInitialized === 'true' || el._choicesInstance) return;
                        try {
                            ensureChoicesInstance(el);
                            const v = el.value != null ? String(el.value) : '';
                            syncChoiceValue(el, v);
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
function pauseChoicesObserver() {
    try {
        if (window._choicesObserver && !window._choicesObserverPaused) {
            window._choicesObserver.disconnect();
            window._choicesObserverPaused = true;
        }
    } catch (e) {
        console.error('Choices observer pause error:', e);
    }
}
function resumeChoicesObserver() {
    try {
        if (window._choicesObserver && window._choicesObserverPaused) {
            window._choicesObserver.observe(document.body, { childList: true, subtree: true });
            window._choicesObserverPaused = false;
        }
    } catch (e) {
        console.error('Choices observer resume error:', e);
    }
}
window.pauseChoicesObserver = pauseChoicesObserver;
window.resumeChoicesObserver = resumeChoicesObserver;
function cleanupChoicesSiblings(el) {
    try {
        const p = el.parentElement;
        if (!p) return;
        const wraps = (p.matches('.choices') ? p.parentElement : p)?.querySelectorAll('.choices') || [];
        wraps.forEach((w) => {
            if (!w.contains(el)) w.remove();
        });
        if (p && p.classList && p.classList.contains('choices')) {
            const grand = p.parentElement;
            if (grand) {
                grand.insertBefore(el, p);
                p.remove();
            }
        }
    } catch (e) {
        console.error('Choices cleanup error:', e);
    }
}

function destroyChoiceEl(el) {
    try {
        if (!el) return;
        const inst = el._choicesInstance || el.choices;
        if (inst && typeof inst.destroy === 'function') {
            inst.destroy();
        }
        el._choicesInstance = null;
        el.dataset.choicesInitialized = '';
        const p = el.parentElement;
        if (p && p.classList && p.classList.contains('choices')) {
            try { p.remove(); } catch (_) {}
        }
        try { el.style.display = ''; } catch (_) {}
        cleanupChoicesSiblings(el);
    } catch (e) {
        console.error('Choices destroy error:', e);
    }
}
window.destroyChoiceEl = destroyChoiceEl;

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
        const handler = (ev) => {
            const wrapper = el.closest('.choices') || el.parentElement;
            if (!wrapper) return;
            const isValid = (() => {
                try {
                    if (typeof el.checkValidity !== 'function') return true;
                    if (ev && ev.type === 'invalid') {
                        return !!(el.validity ? el.validity.valid : true);
                    }
                    return !!el.checkValidity();
                } catch (e) {
                    return true;
                }
            })();
            if (!isValid) {
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
